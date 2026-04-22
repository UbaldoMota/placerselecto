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
}
