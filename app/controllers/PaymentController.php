<?php
/**
 * PaymentController.php
 * Gestiona la selección de planes de destacado y el flujo de pago simulado.
 * Preparado para integrar CCBill o Segpay reemplazando el método processPlan().
 */

require_once APP_PATH . '/Controller.php';
require_once APP_PATH . '/Security.php';
require_once APP_PATH . '/Validator.php';
require_once APP_PATH . '/models/AnuncioModel.php';
require_once APP_PATH . '/models/PagoModel.php';
require_once APP_PATH . '/models/NotificacionModel.php';
require_once APP_PATH . '/models/TokenPaqueteModel.php';
require_once APP_PATH . '/models/TokenMovimientoModel.php';

class PaymentController extends Controller
{
    private AnuncioModel          $anuncios;
    private PagoModel             $pagos;
    private TokenPaqueteModel     $paquetes;
    private TokenMovimientoModel  $movimientos;

    public function __construct()
    {
        $this->anuncios    = new AnuncioModel();
        $this->pagos       = new PagoModel();
        $this->paquetes    = new TokenPaqueteModel();
        $this->movimientos = new TokenMovimientoModel();
    }

    // =========================================================
    // TOKENS — compra de paquetes
    // =========================================================

    /**
     * GET /tokens/comprar — lista paquetes activos.
     */
    public function showPackages(array $params = []): void
    {
        $this->requireAuth();

        $user    = $this->currentUser();
        $saldo   = $this->movimientos->saldo((int)$user['id']);
        $activos = $this->paquetes->activos();

        $this->render('payment/token-packages', [
            'pageTitle' => 'Comprar tokens',
            'paquetes'  => $activos,
            'saldo'     => $saldo,
        ]);
    }

    /**
     * POST /tokens/comprar/{id_paquete} — procesa compra simulada.
     */
    public function buyPackage(array $params = []): void
    {
        $this->requireAuth();

        $idPaquete = (int)($params['id_paquete'] ?? 0);
        $user      = $this->currentUser();
        $ip        = Security::getClientIp();

        $paquete = $this->paquetes->find($idPaquete);
        if (!$paquete || !(int)$paquete['activo']) {
            SessionManager::flash('error', 'Paquete no disponible.');
            $this->redirect('/tokens/comprar');
        }

        // Rate limit
        if (!Security::checkRateLimit('compra_tokens_' . $user['id'], 5, 3600)) {
            SessionManager::flash('error', 'Demasiados intentos de compra. Espera un momento.');
            $this->redirect('/tokens/comprar');
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // 1. Registrar pago
            $idPago = $this->pagos->iniciarPago([
                'id_usuario'       => (int)$user['id'],
                'id_anuncio'       => null,
                'id_paquete'       => $idPaquete,
                'tokens_otorgados' => (int)$paquete['tokens'],
                'monto'            => (float)$paquete['monto_mxn'],
                'tipo_destacado'   => null,
                'metodo_pago'      => 'simulado',
                'ip_pago'          => $ip,
            ]);

            // 2. Completar pago
            $referencia = 'TKN-' . strtoupper(bin2hex(random_bytes(6)));
            $this->pagos->completar($idPago, $referencia);

            // 3. Acreditar tokens
            $tokens = (int)$paquete['tokens'];
            $res = $this->movimientos->aplicar(
                (int)$user['id'],
                'recarga',
                $tokens,
                $idPago,
                null,
                "Compra paquete \"{$paquete['nombre']}\" (+{$tokens} tokens)"
            );

            if (!$res['ok']) {
                throw new RuntimeException($res['error'] ?? 'Error acreditando tokens.');
            }

            $db->commit();
            Security::resetRateLimit('compra_tokens_' . $user['id']);

            SessionManager::flash('success',
                "¡Recarga exitosa! +{$tokens} tokens. Saldo: {$res['saldo_despues']}.");
            $this->redirect("/tokens/confirmacion/{$idPago}");

        } catch (\Throwable $e) {
            $db->rollBack();
            error_log('[PaymentController::buyPackage] ' . $e->getMessage());

            if (isset($idPago)) {
                $this->pagos->fallar($idPago);
            }

            SessionManager::flash('error', 'Error al procesar la compra. Intenta de nuevo.');
            $this->redirect('/tokens/comprar');
        }
    }

    /**
     * GET /tokens/confirmacion/{id} — confirmación de compra.
     */
    public function tokenPurchaseConfirmation(array $params = []): void
    {
        $this->requireAuth();

        $idPago = (int)($params['id'] ?? 0);
        $user   = $this->currentUser();
        $pago   = $this->pagos->find($idPago);

        if (!$pago || (int)$pago['id_usuario'] !== (int)$user['id'] || $pago['estado'] !== 'completado') {
            SessionManager::flash('error', 'Confirmación no encontrada.');
            $this->redirect('/mis-tokens');
        }

        $paquete = !empty($pago['id_paquete']) ? $this->paquetes->find((int)$pago['id_paquete']) : null;
        $saldo   = $this->movimientos->saldo((int)$user['id']);

        $this->render('payment/token-confirmation', [
            'pageTitle' => 'Compra confirmada',
            'pago'      => $pago,
            'paquete'   => $paquete,
            'saldo'     => $saldo,
        ]);
    }

    // ---------------------------------------------------------
    // GET /destacar/{id_anuncio}
    // Muestra los planes de destacado disponibles
    // ---------------------------------------------------------
    public function showPlans(array $params = []): void
    {
        $this->requireAuth();

        $idAnuncio = (int) ($params['id_anuncio'] ?? 0);
        $user      = $this->currentUser();

        // Verificar que el anuncio existe, está publicado y pertenece al usuario
        $anuncio = $this->anuncios->find($idAnuncio);

        if (!$anuncio) {
            SessionManager::flash('error', 'Anuncio no encontrado.');
            $this->redirect('/mis-anuncios');
        }

        if (!$this->anuncios->perteneceA($idAnuncio, (int) $user['id'])) {
            SessionManager::flash('error', 'No tienes permiso sobre este anuncio.');
            $this->redirect('/mis-anuncios');
        }

        if ($anuncio['estado'] !== 'publicado') {
            SessionManager::flash('warning', 'Solo puedes destacar anuncios publicados.');
            $this->redirect('/mis-anuncios');
        }

        // Ya tiene destacado activo?
        $tieneDestacado = (bool) $anuncio['destacado'];
        $expira         = $anuncio['fecha_expiracion_destacado'];

        $this->render('payment/plans', [
            'pageTitle'      => 'Planes de destacado',
            'anuncio'        => $anuncio,
            'planes'         => PLANES_DESTACADO,
            'tieneDestacado' => $tieneDestacado,
            'expira'         => $expira,
        ]);
    }

    // ---------------------------------------------------------
    // POST /destacar/{id_anuncio}
    // Procesa la selección de plan y ejecuta el pago simulado
    // ---------------------------------------------------------
    public function processPlan(array $params = []): void
    {
        $this->requireAuth();

        $idAnuncio = (int) ($params['id_anuncio'] ?? 0);
        $user      = $this->currentUser();
        $ip        = Security::getClientIp();

        // Re-validar propiedad del anuncio
        $anuncio = $this->anuncios->find($idAnuncio);

        if (!$anuncio || !$this->anuncios->perteneceA($idAnuncio, (int) $user['id'])) {
            SessionManager::flash('error', 'Anuncio no encontrado o sin permiso.');
            $this->redirect('/mis-anuncios');
        }

        if ($anuncio['estado'] !== 'publicado') {
            SessionManager::flash('warning', 'El anuncio debe estar publicado para destacarlo.');
            $this->redirect('/mis-anuncios');
        }

        // Validar plan seleccionado
        $diasPlan = (int) ($_POST['plan'] ?? 0);
        $planes   = PLANES_DESTACADO;

        if (!array_key_exists($diasPlan, $planes)) {
            SessionManager::flash('error', 'Plan de destacado no válido.');
            $this->redirect("/destacar/{$idAnuncio}");
        }

        $monto = $planes[$diasPlan]['precio'];

        // Rate limiting: máx 3 pagos por usuario en 1 hora
        if (!Security::checkRateLimit('pago_' . $user['id'], 3, 3600)) {
            SessionManager::flash('error', 'Demasiadas solicitudes de pago. Espera un momento.');
            $this->redirect("/destacar/{$idAnuncio}");
        }

        // ---- FLUJO DE PAGO ----
        // En producción: aquí se redirige a CCBill/Segpay y se espera el callback.
        // En esta simulación: el pago es inmediato y siempre exitoso.

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // 1. Registrar pago
            $idPago = $this->pagos->iniciarPago([
                'id_usuario'     => (int) $user['id'],
                'id_anuncio'     => $idAnuncio,
                'monto'          => $monto,
                'tipo_destacado' => $diasPlan,
                'metodo_pago'    => 'simulado',
                'ip_pago'        => $ip,
            ]);

            // 2. Completar pago (simulado — referencia ficticia)
            $referencia = 'SIM-' . strtoupper(bin2hex(random_bytes(6)));
            $this->pagos->completar($idPago, $referencia);

            // 3. Activar destacado en el anuncio
            $this->anuncios->activarDestacado($idAnuncio, $diasPlan);

            $db->commit();

            Security::resetRateLimit('pago_' . $user['id']);

            (new NotificacionModel())->crearParaAdmins([
                'tipo'    => 'pago_nuevo',
                'titulo'  => 'Nuevo pago registrado',
                'mensaje' => '$' . number_format((float)$monto, 2) . ' MXN — plan de ' . $diasPlan . ' días (' . ($user['nombre'] ?? '') . ').',
                'url'     => '/admin/pagos',
                'icono'   => 'cash-coin',
                'color'   => 'success',
            ]);

            SessionManager::flash('success', "¡Destacado activado! Tu anuncio aparecerá primero durante {$diasPlan} días.");
            $this->redirect("/pago/confirmacion/{$idPago}");

        } catch (\Exception $e) {
            $db->rollBack();
            error_log('[PaymentController] Error en pago: ' . $e->getMessage());

            if (isset($idPago)) {
                $this->pagos->fallar($idPago);
            }

            SessionManager::flash('error', 'Ocurrió un error al procesar el pago. Intenta de nuevo.');
            $this->redirect("/destacar/{$idAnuncio}");
        }
    }

    // ---------------------------------------------------------
    // GET /pago/confirmacion/{id}
    // Página de confirmación post-pago
    // ---------------------------------------------------------
    public function confirmation(array $params = []): void
    {
        $this->requireAuth();

        $idPago = (int) ($params['id'] ?? 0);
        $user   = $this->currentUser();

        $pago = $this->pagos->find($idPago);

        // Verificar que el pago pertenece al usuario y está completado
        if (!$pago || (int) $pago['id_usuario'] !== (int) $user['id'] || $pago['estado'] !== 'completado') {
            SessionManager::flash('error', 'Confirmación de pago no encontrada.');
            $this->redirect('/mis-anuncios');
        }

        $anuncio = $this->anuncios->find((int) $pago['id_anuncio']);

        $this->render('payment/confirmation', [
            'pageTitle' => 'Pago confirmado',
            'pago'      => $pago,
            'anuncio'   => $anuncio,
            'planes'    => PLANES_DESTACADO,
        ]);
    }

    // =========================================================
    // PASARELAS DE PAGO REALES (Fase A: estructura sin credenciales)
    // =========================================================

    /**
     * GET /tokens/comprar/{id_paquete}/metodo
     * Selector de método de pago para un paquete específico.
     */
    public function selectMethod(array $params = []): void
    {
        $this->requireAuth();
        $idPaquete = (int)($params['id_paquete'] ?? 0);
        $paquete = $this->paquetes->find($idPaquete);

        if (!$paquete || !(int)$paquete['activo']) {
            SessionManager::flash('error', 'Paquete no disponible.');
            $this->redirect('/tokens/comprar');
        }

        $user  = $this->currentUser();
        $tienePerfilAprobado = $this->usuarioTienePerfilPublicado((int)$user['id']);

        $this->render('payment/select-method', [
            'pageTitle'           => 'Elegir método de pago',
            'paquete'             => $paquete,
            'devMode'             => self::isDevMode(),
            'truevoEnabled'       => defined('TRUEVO_ENABLED')  && TRUEVO_ENABLED,
            'paycashEnabled'      => defined('PAYCASH_ENABLED') && PAYCASH_ENABLED,
            'whatsappPagos'       => function_exists('setting') ? setting('whatsapp_pagos') : null,
            'tienePerfilAprobado' => $tienePerfilAprobado,
        ]);
    }

    /** ¿El usuario tiene al menos un perfil con estado='publicado'? */
    private function usuarioTienePerfilPublicado(int $idUsuario): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT 1 FROM perfiles WHERE id_usuario = ? AND estado = 'publicado' LIMIT 1");
        $stmt->execute([$idUsuario]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * POST /tokens/comprar/{id_paquete}/whatsapp
     * Crea un pago en estado 'pendiente' y redirige a WhatsApp con
     * mensaje pre-armado para que el cliente coordine el pago externo
     * (link de pago, transferencia, OXXO, etc.). El admin activa los
     * tokens manualmente desde /admin/pagos.
     */
    public function payWithWhatsapp(array $params = []): void
    {
        $this->requireAuth();

        $idPaquete = (int)($params['id_paquete'] ?? 0);
        $user      = $this->currentUser();
        $paquete   = $this->paquetes->find($idPaquete);

        if (!$paquete || !(int)$paquete['activo']) {
            SessionManager::flash('error', 'Paquete no disponible.');
            $this->redirect('/tokens/comprar');
        }

        // Bloqueo: solo se puede pagar si tiene un perfil ya publicado.
        if (!$this->usuarioTienePerfilPublicado((int)$user['id'])) {
            SessionManager::flash('error',
                'Para comprar tokens primero necesitas tener al menos un perfil publicado. '
                . 'Crea tu perfil y espera la aprobación del equipo.');
            $this->redirect('/tokens/comprar');
        }

        $whatsapp = function_exists('setting') ? setting('whatsapp_pagos') : null;
        if (!$whatsapp) {
            SessionManager::flash('error', 'El método de pago no está configurado todavía. Intenta más tarde.');
            $this->redirect('/tokens/comprar');
        }

        if (!Security::checkRateLimit('compra_tokens_' . $user['id'], 5, 3600)) {
            SessionManager::flash('error', 'Demasiados intentos. Espera un momento.');
            $this->redirect('/tokens/comprar');
        }

        $idPago = $this->pagos->iniciarPago([
            'id_usuario'       => (int)$user['id'],
            'id_paquete'       => $idPaquete,
            'tokens_otorgados' => (int)$paquete['tokens'],
            'monto'            => (float)$paquete['monto_mxn'],
            'tipo_destacado'   => 7, // legacy NOT NULL en prod
            'metodo_pago'      => 'externo_wa',
            'ip_pago'          => Security::getClientIp(),
        ]);

        // Referencia para auditoria/seguimiento del admin
        $referencia = 'PS-' . str_pad((string)$idPago, 5, '0', STR_PAD_LEFT) . '-' . strtoupper(bin2hex(random_bytes(2)));
        $this->pagos->update($idPago, ['referencia_pasarela' => $referencia]);

        // Mensaje pre-armado para WhatsApp
        $mensaje =
            "Hola, quiero comprar el paquete \"" . $paquete['nombre'] . "\" "
            . "(" . number_format((int)$paquete['tokens']) . " tokens) por $" . number_format((float)$paquete['monto_mxn'], 2) . " MXN.\n\n"
            . "Mi correo: " . ($user['email'] ?? '') . "\n"
            . "Referencia: " . $referencia;

        // Notificacion al admin (campanita)
        try {
            require_once APP_PATH . '/models/NotificacionModel.php';
            (new NotificacionModel())->crearParaAdmins([
                'tipo'    => 'pago_pendiente',
                'titulo'  => 'Solicitud de pago — ' . $referencia,
                'mensaje' => ($user['nombre'] ?? 'Usuario') . ' solicitó pagar el paquete "' . $paquete['nombre'] . '" ($' . number_format((float)$paquete['monto_mxn'], 0) . ' MXN). Envíale el link de pago por WhatsApp.',
                'url'     => '/admin/pagos?estado=pendiente',
                'icono'   => 'cash-coin',
                'color'   => 'warning',
            ]);
        } catch (\Throwable $e) {
            error_log('[payWithWhatsapp][notif] ' . $e->getMessage());
        }

        // Email al admin (best-effort, no bloquea el redirect si falla).
        // Usa template HTML estructurado y subject sin brackets para mejor deliverability.
        try {
            $adminEmail = null;
            $envProd = APP_PATH . '/../config/env.production.php';
            $envDev  = APP_PATH . '/../config/env.development.php';
            $envFile = file_exists($envProd) ? $envProd : (file_exists($envDev) ? $envDev : null);
            if ($envFile) {
                $cfg = require $envFile;
                $adminEmail = $cfg['admin_notify_email'] ?? null;
            }
            if ($adminEmail) {
                require_once APP_PATH . '/Mailer.php';
                $html = Mailer::render('pago-solicitud-admin', [
                    'usuario_nombre'  => $user['nombre'] ?? '',
                    'usuario_email'   => $user['email']  ?? '',
                    'paquete_nombre'  => $paquete['nombre'] ?? '',
                    'tokens'          => (int)$paquete['tokens'],
                    'monto'           => (float)$paquete['monto_mxn'],
                    'referencia'      => $referencia,
                    'url_admin_pagos' => APP_URL . '/admin/pagos?estado=pendiente',
                ]);
                $alt = "Nueva solicitud de pago.\n"
                    . "Usuaria: " . ($user['nombre'] ?? '—') . " (" . ($user['email'] ?? '—') . ")\n"
                    . "Paquete: " . ($paquete['nombre'] ?? '—') . " (" . number_format((int)$paquete['tokens']) . " tokens)\n"
                    . "Monto: $" . number_format((float)$paquete['monto_mxn'], 2) . " MXN\n"
                    . "Referencia: $referencia\n\n"
                    . "Pendientes: " . APP_URL . '/admin/pagos?estado=pendiente';
                Mailer::send(
                    $adminEmail,
                    'Nueva solicitud de pago ' . $referencia . ' — ' . APP_NAME,
                    $html,
                    $alt
                );
            }
        } catch (\Throwable $e) {
            error_log('[payWithWhatsapp][email] ' . $e->getMessage());
        }

        // Redirigir a la pantalla de espera. La URL de WhatsApp se construye alli
        // y abre en pestaña nueva, manteniendo al usuario en placerselecto.com.
        SessionManager::flash('success', 'Solicitud de pago registrada. Abrimos WhatsApp en otra pestaña.');
        $this->redirect('/pago/' . $idPago . '/pendiente');
    }

    /**
     * POST /tokens/comprar/{id_paquete}/truevo
     * Inicia un pago con tarjeta vía Truevo y redirige al checkout.
     */
    public function payWithTruevo(array $params = []): void
    {
        $this->requireAuth();
        if (!self::isDevMode() && !(defined('TRUEVO_ENABLED') && TRUEVO_ENABLED)) {
            SessionManager::flash('error', 'El pago con tarjeta no está disponible aún.');
            $this->redirect('/tokens/comprar');
        }
        $idPaquete = (int)($params['id_paquete'] ?? 0);
        $user      = $this->currentUser();
        $paquete   = $this->paquetes->find($idPaquete);

        if (!$paquete || !(int)$paquete['activo']) {
            SessionManager::flash('error', 'Paquete no disponible.');
            $this->redirect('/tokens/comprar');
        }

        if (!Security::checkRateLimit('compra_tokens_' . $user['id'], 5, 3600)) {
            SessionManager::flash('error', 'Demasiados intentos. Espera un momento.');
            $this->redirect('/tokens/comprar');
        }

        $idPago = $this->pagos->iniciarPago([
            'id_usuario'       => (int)$user['id'],
            'id_paquete'       => $idPaquete,
            'tokens_otorgados' => (int)$paquete['tokens'],
            'monto'            => (float)$paquete['monto_mxn'],
            'tipo_destacado'   => 7, // legacy NOT NULL en prod, valor dummy para compras de paquete
            'metodo_pago'      => 'truevo',
            'ip_pago'          => Security::getClientIp(),
        ]);

        $res = TruevoClient::createPayment(
            $idPago,
            (float)$paquete['monto_mxn'],
            'MXN',
            "Recarga {$paquete['nombre']} ({$paquete['tokens']} tokens)",
            $user['email'] ?? ''
        );

        if (empty($res['url']) || empty($res['reference'])) {
            $this->pagos->fallar($idPago);
            SessionManager::flash('error', 'No se pudo iniciar el pago con Truevo. Intenta de nuevo.');
            $this->redirect('/tokens/comprar');
        }

        $this->pagos->update($idPago, [
            'referencia_pasarela' => $res['reference'],
        ]);

        header('Location: ' . $res['url']);
        exit;
    }

    /**
     * POST /tokens/comprar/{id_paquete}/paycash
     * Inicia un pago en efectivo vía PayCash y muestra la pantalla de pendiente.
     */
    public function payWithPayCash(array $params = []): void
    {
        $this->requireAuth();
        if (!self::isDevMode() && !(defined('PAYCASH_ENABLED') && PAYCASH_ENABLED)) {
            SessionManager::flash('error', 'El pago en efectivo no está disponible aún.');
            $this->redirect('/tokens/comprar');
        }
        $idPaquete = (int)($params['id_paquete'] ?? 0);
        $user      = $this->currentUser();
        $paquete   = $this->paquetes->find($idPaquete);

        if (!$paquete || !(int)$paquete['activo']) {
            SessionManager::flash('error', 'Paquete no disponible.');
            $this->redirect('/tokens/comprar');
        }

        if (!Security::checkRateLimit('compra_tokens_' . $user['id'], 5, 3600)) {
            SessionManager::flash('error', 'Demasiados intentos. Espera un momento.');
            $this->redirect('/tokens/comprar');
        }

        $idPago = $this->pagos->iniciarPago([
            'id_usuario'       => (int)$user['id'],
            'id_paquete'       => $idPaquete,
            'tokens_otorgados' => (int)$paquete['tokens'],
            'monto'            => (float)$paquete['monto_mxn'],
            'tipo_destacado'   => 7,
            'metodo_pago'      => 'paycash',
            'ip_pago'          => Security::getClientIp(),
        ]);

        $res = PayCashClient::createPayment(
            $idPago,
            (float)$paquete['monto_mxn'],
            $user['email'] ?? ''
        );

        if (empty($res['reference'])) {
            $this->pagos->fallar($idPago);
            SessionManager::flash('error', 'No se pudo generar la referencia. Intenta de nuevo.');
            $this->redirect('/tokens/comprar');
        }

        $this->pagos->update($idPago, [
            'referencia_pasarela' => $res['reference'],
            'expira_en'           => $res['expira_en'],
        ]);

        $this->redirect("/pago/{$idPago}/pendiente");
    }

    /**
     * GET /pago/{id}/pendiente
     * Pantalla de espera (PayCash con código de barras, o "esperando webhook" en Truevo simulado).
     */
    public function paymentPending(array $params = []): void
    {
        $this->requireAuth();
        $idPago = (int)($params['id'] ?? 0);
        $user   = $this->currentUser();
        $pago   = $this->pagos->find($idPago);

        if (!$pago || (int)$pago['id_usuario'] !== (int)$user['id']) {
            SessionManager::flash('error', 'Pago no encontrado.');
            $this->redirect('/tokens/comprar');
        }

        if ($pago['estado'] === 'completado') {
            $this->redirect("/tokens/confirmacion/{$idPago}");
        }

        $paquete = !empty($pago['id_paquete']) ? $this->paquetes->find((int)$pago['id_paquete']) : null;

        // Si el metodo es externo_wa, construir el link de WhatsApp con
        // el mismo mensaje pre-armado para que el usuario pueda reabrirlo
        // desde la pantalla de espera (target=_blank).
        $whatsappUrl = null;
        if (($pago['metodo_pago'] ?? '') === 'externo_wa') {
            $waNumero = function_exists('setting') ? setting('whatsapp_pagos') : null;
            if ($waNumero) {
                $mensaje =
                    'Hola, quiero comprar el paquete "' . ($paquete['nombre'] ?? '?') . '" '
                    . '(' . number_format((int)($pago['tokens_otorgados'] ?? 0)) . ' tokens) por $' . number_format((float)$pago['monto'], 2) . " MXN.\n\n"
                    . 'Mi correo: ' . ($user['email'] ?? '') . "\n"
                    . 'Referencia: ' . ($pago['referencia_pasarela'] ?? '');
                $whatsappUrl = 'https://wa.me/' . preg_replace('/\D/', '', $waNumero) . '?text=' . rawurlencode($mensaje);
            }
        }

        $this->render('payment/payment-pending', [
            'pageTitle'   => 'Pago pendiente',
            'pago'        => $pago,
            'paquete'     => $paquete,
            'devMode'     => self::isDevMode(),
            'whatsappUrl' => $whatsappUrl,
        ]);
    }

    /**
     * POST /pago/{id}/simular-completar
     * Solo en modo dev — completa el pago como si el webhook hubiera llegado.
     */
    public function simulateComplete(array $params = []): void
    {
        $this->requireAuth();
        if (!self::isDevMode()) {
            SessionManager::flash('error', 'No disponible en producción.');
            $this->redirect('/tokens/comprar');
        }

        $idPago = (int)($params['id'] ?? 0);
        $user   = $this->currentUser();
        $pago   = $this->pagos->find($idPago);

        if (!$pago || (int)$pago['id_usuario'] !== (int)$user['id']) {
            SessionManager::flash('error', 'Pago no encontrado.');
            $this->redirect('/tokens/comprar');
        }

        $ok = $this->acreditarTokens($idPago);
        if (!$ok) {
            SessionManager::flash('error', 'No se pudo completar la simulación.');
            $this->redirect("/pago/{$idPago}/pendiente");
        }

        $this->redirect("/tokens/confirmacion/{$idPago}");
    }

    /**
     * POST /webhook/truevo  — IPN de Truevo cuando una transacción cambia de estado.
     * Body JSON con la referencia y el nuevo estado.
     */
    public function truevoWebhook(array $params = []): void
    {
        $body    = file_get_contents('php://input') ?: '';
        $headers = self::collectHeaders();

        if (!TruevoClient::verifyWebhook($body, $headers)) {
            error_log('[truevoWebhook] firma inválida');
            http_response_code(401);
            echo 'invalid signature';
            return;
        }

        $data = json_decode($body, true) ?: [];
        $ref   = $data['reference'] ?? '';
        $state = $data['state']     ?? '';

        if ($ref === '') {
            http_response_code(400); echo 'missing reference'; return;
        }

        $pago = $this->buscarPagoPorReferenciaPasarela($ref);
        if (!$pago) {
            http_response_code(404); echo 'pago no encontrado'; return;
        }

        if ($state === 'completed' || $state === 'success') {
            if ($pago['estado'] === 'pendiente') {
                $this->acreditarTokens((int)$pago['id']);
            }
        } elseif ($state === 'failed' || $state === 'declined') {
            if ($pago['estado'] === 'pendiente') {
                $this->pagos->fallar((int)$pago['id']);
            }
        }

        http_response_code(200);
        echo 'ok';
    }

    /**
     * POST /webhook/paycash — IPN de PayCash cuando el cliente paga en tienda.
     */
    public function paycashWebhook(array $params = []): void
    {
        $body    = file_get_contents('php://input') ?: '';
        $headers = self::collectHeaders();

        if (!PayCashClient::verifyWebhook($body, $headers)) {
            error_log('[paycashWebhook] firma inválida');
            http_response_code(401); echo 'invalid signature'; return;
        }

        $data = json_decode($body, true) ?: [];
        $ref   = $data['reference'] ?? '';
        $state = $data['state']     ?? 'paid';

        if ($ref === '') {
            http_response_code(400); echo 'missing reference'; return;
        }

        $pago = $this->buscarPagoPorReferenciaPasarela($ref);
        if (!$pago) {
            http_response_code(404); echo 'pago no encontrado'; return;
        }

        if ($state === 'paid' && $pago['estado'] === 'pendiente') {
            $this->acreditarTokens((int)$pago['id']);
        }

        http_response_code(200);
        echo 'ok';
    }

    // ---------------------------------------------------------
    // Helpers privados
    // ---------------------------------------------------------

    /**
     * Completa el pago y acredita los tokens correspondientes al usuario.
     * Idempotente: si ya está completado, no hace nada.
     */
    public function acreditarTokens(int $idPago): bool
    {
        $pago = $this->pagos->find($idPago);
        if (!$pago) return false;
        if ($pago['estado'] === 'completado') return true;
        if (empty($pago['id_paquete']) || empty($pago['tokens_otorgados'])) return false;

        $paquete = $this->paquetes->find((int)$pago['id_paquete']);
        $tokens  = (int)$pago['tokens_otorgados'];

        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        try {
            $referencia = $pago['referencia_pasarela'] ?: ('TKN-' . strtoupper(bin2hex(random_bytes(6))));
            $this->pagos->completar($idPago, $referencia);

            $res = $this->movimientos->aplicar(
                (int)$pago['id_usuario'],
                'recarga',
                $tokens,
                $idPago,
                null,
                "Compra paquete \"" . ($paquete['nombre'] ?? '#' . $pago['id_paquete']) . "\" (+{$tokens} tokens)"
            );
            if (!$res['ok']) throw new RuntimeException($res['error'] ?? 'Error acreditando tokens.');

            $db->commit();

            // Notificacion al usuario (campanita) — best-effort
            try {
                require_once APP_PATH . '/models/NotificacionModel.php';
                (new NotificacionModel())->crear((int)$pago['id_usuario'], [
                    'tipo'    => 'pago_completado',
                    'titulo'  => '¡Pago confirmado! +' . number_format($tokens) . ' tokens',
                    'mensaje' => 'Tu pago de "' . ($paquete['nombre'] ?? 'paquete') . '" fue acreditado. Saldo actual: ' . number_format((int)$res['saldo_despues']) . ' tokens.',
                    'url'     => '/mis-tokens',
                    'icono'   => 'check-circle-fill',
                    'color'   => 'success',
                ]);
            } catch (\Throwable $e) {
                error_log('[acreditarTokens][notif] ' . $e->getMessage());
            }

            // Email al usuario — best-effort. Usa template estructurado +
            // subject sin brackets para mejor deliverability (no spam).
            try {
                require_once APP_PATH . '/models/UsuarioModel.php';
                $usuario = (new UsuarioModel())->find((int)$pago['id_usuario']);
                if (!empty($usuario['email'])) {
                    require_once APP_PATH . '/Mailer.php';
                    $html = Mailer::render('pago-confirmado', [
                        'nombre'         => $usuario['nombre'] ?? '',
                        'tokens'         => $tokens,
                        'paquete_nombre' => $paquete['nombre'] ?? '',
                        'monto'          => (float)$pago['monto'],
                        'referencia'     => $referencia,
                        'saldo_actual'   => (int)$res['saldo_despues'],
                        'url_dashboard'  => APP_URL . '/dashboard',
                    ]);
                    $alt = "Hola " . ($usuario['nombre'] ?? '') . ",\n"
                        . "Tu pago fue confirmado y acreditamos " . number_format($tokens) . " tokens a tu cuenta.\n\n"
                        . "Paquete: " . ($paquete['nombre'] ?? '—') . "\n"
                        . "Monto: $" . number_format((float)$pago['monto'], 2) . " MXN\n"
                        . "Referencia: $referencia\n"
                        . "Saldo actual: " . number_format((int)$res['saldo_despues']) . " tokens\n\n"
                        . "Ir al dashboard: " . APP_URL . '/dashboard';
                    Mailer::send(
                        $usuario['email'],
                        'Pago confirmado — ' . number_format($tokens) . ' tokens acreditados — ' . APP_NAME,
                        $html,
                        $alt
                    );
                }
            } catch (\Throwable $e) {
                error_log('[acreditarTokens][email] ' . $e->getMessage());
            }

            return true;
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log('[PaymentController::acreditarTokens] ' . $e->getMessage());
            return false;
        }
    }

    private function buscarPagoPorReferenciaPasarela(string $ref): ?array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM pagos WHERE referencia_pasarela = ? LIMIT 1");
        $stmt->execute([$ref]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private static function isDevMode(): bool
    {
        return (defined('APP_ENV') && APP_ENV === 'development')
            || (defined('APP_DEBUG') && APP_DEBUG === true);
    }

    private static function collectHeaders(): array
    {
        if (function_exists('getallheaders')) return getallheaders() ?: [];
        $h = [];
        foreach ($_SERVER as $k => $v) {
            if (strpos($k, 'HTTP_') === 0) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($k, 5)))));
                $h[$name] = $v;
            }
        }
        return $h;
    }
}
