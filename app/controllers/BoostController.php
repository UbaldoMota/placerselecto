<?php
/**
 * BoostController.php
 * Ventanas de destacado/resaltado por perfil (consumen tokens).
 */

require_once APP_PATH . '/Controller.php';
require_once APP_PATH . '/Security.php';
require_once APP_PATH . '/models/PerfilModel.php';
require_once APP_PATH . '/models/BoostModel.php';
require_once APP_PATH . '/models/TokenTarifaModel.php';
require_once APP_PATH . '/models/TokenMovimientoModel.php';
require_once APP_PATH . '/models/NotificacionModel.php';

class BoostController extends Controller
{
    private PerfilModel           $perfiles;
    private BoostModel            $boosts;
    private TokenTarifaModel      $tarifas;
    private TokenMovimientoModel  $movimientos;

    public const MIN_HORAS = 1;
    public const MAX_HORAS = 168;   // 7 días

    public function __construct()
    {
        $this->perfiles    = new PerfilModel();
        $this->boosts      = new BoostModel();
        $this->tarifas     = new TokenTarifaModel();
        $this->movimientos = new TokenMovimientoModel();
    }

    /**
     * GET /perfil/{id}/destacar
     */
    public function show(array $params = []): void
    {
        $this->requireAuth();

        $idPerfil = (int)($params['id'] ?? 0);
        $user     = $this->currentUser();
        $perfil   = $this->perfiles->find($idPerfil);

        if (!$perfil || !$this->perfiles->perteneceA($idPerfil, (int)$user['id'])) {
            SessionManager::flash('error', 'Perfil no encontrado.');
            $this->redirect('/mis-perfiles');
        }

        if ($perfil['estado'] !== 'publicado') {
            SessionManager::flash('warning', 'El perfil debe estar publicado para destacarlo.');
            $this->redirect('/mis-perfiles');
        }

        $saldo     = $this->movimientos->saldo((int)$user['id']);
        $tarifas   = $this->tarifas->mapa();
        $boosts    = $this->boosts->porPerfil($idPerfil, 20);

        $this->render('boost/create', [
            'pageTitle' => 'Destacar perfil',
            'perfil'    => $perfil,
            'saldo'     => $saldo,
            'tarifas'   => $tarifas,
            'boosts'    => $boosts,
            'minHoras'  => self::MIN_HORAS,
            'maxHoras'  => self::MAX_HORAS,
        ]);
    }

    /**
     * POST /perfil/{id}/destacar
     */
    public function create(array $params = []): void
    {
        $this->requireAuth();

        $idPerfil = (int)($params['id'] ?? 0);
        $user     = $this->currentUser();
        $perfil   = $this->perfiles->find($idPerfil);

        if (!$perfil || !$this->perfiles->perteneceA($idPerfil, (int)$user['id'])) {
            SessionManager::flash('error', 'Perfil no encontrado.');
            $this->redirect('/mis-perfiles');
        }

        $tipo      = Security::sanitizeString($_POST['tipo'] ?? '');
        $horas     = (int)($_POST['horas'] ?? 0);
        $modoInicio = Security::sanitizeString($_POST['modo_inicio'] ?? 'ahora'); // 'ahora' | 'programado'
        $inicioStr = trim($_POST['inicio'] ?? '');

        // ---- Validaciones ----
        if (!in_array($tipo, BoostModel::TIPOS, true)) {
            SessionManager::flash('error', 'Tipo de boost inválido.');
            $this->redirect("/perfil/{$idPerfil}/destacar");
        }

        if ($horas < self::MIN_HORAS || $horas > self::MAX_HORAS) {
            SessionManager::flash('error', 'Duración inválida. Rango ' . self::MIN_HORAS . '-' . self::MAX_HORAS . ' horas.');
            $this->redirect("/perfil/{$idPerfil}/destacar");
        }

        // Calcular inicio
        if ($modoInicio === 'programado' && $inicioStr !== '') {
            $ts = strtotime($inicioStr);
            if (!$ts || $ts < time() - 60) {
                SessionManager::flash('error', 'La fecha de inicio debe ser futura.');
                $this->redirect("/perfil/{$idPerfil}/destacar");
            }
            $inicio = date('Y-m-d H:i:s', $ts);
        } else {
            $inicio = date('Y-m-d H:i:s');
        }
        $fin = date('Y-m-d H:i:s', strtotime($inicio) + $horas * 3600);

        // Solapamiento con otro boost del mismo tipo
        if ($this->boosts->haySolapamiento($idPerfil, $tipo, $inicio, $fin)) {
            SessionManager::flash('error', 'Este perfil ya tiene un boost de tipo "' . $tipo . '" en ese rango.');
            $this->redirect("/perfil/{$idPerfil}/destacar");
        }

        // Calcular costo
        $tph   = $this->tarifas->tokensPorHora($tipo);
        if ($tph <= 0) {
            SessionManager::flash('error', 'Tarifa no configurada para este tipo.');
            $this->redirect("/perfil/{$idPerfil}/destacar");
        }
        $costo = $tph * $horas;

        $saldo = $this->movimientos->saldo((int)$user['id']);
        if ($saldo < $costo) {
            SessionManager::flash('error',
                "Saldo insuficiente. Necesitas {$costo} tokens y tienes {$saldo}. Compra más tokens.");
            $this->redirect('/tokens/comprar');
        }

        // ---- Transacción: crear boost + debitar tokens ----
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $estadoInicial = strtotime($inicio) <= time() ? 'activo' : 'programado';

            $idBoost = $this->boosts->crear([
                'id_perfil'       => $idPerfil,
                'tipo'            => $tipo,
                'inicio'          => $inicio,
                'fin'             => $fin,
                'tokens_gastados' => $costo,
                'estado'          => $estadoInicial,
            ]);

            $desc = "Boost {$tipo} · perfil \"" . mb_substr($perfil['nombre'], 0, 60) . "\" · {$horas}h";
            $res  = $this->movimientos->aplicar(
                (int)$user['id'],
                'consumo',
                -$costo,
                null,
                $idBoost,
                $desc
            );

            if (!$res['ok']) {
                throw new RuntimeException($res['error'] ?? 'Error debitando tokens.');
            }

            $db->commit();

            $msgInicio = $estadoInicial === 'activo' ? 'activo ahora' : 'programado para ' . substr($inicio, 0, 16);
            SessionManager::flash('success',
                "Boost {$tipo} creado ({$horas}h, {$msgInicio}). Saldo restante: {$res['saldo_despues']} tokens.");
            $this->redirect("/perfil/{$idPerfil}/destacar");

        } catch (\Throwable $e) {
            $db->rollBack();
            error_log('[BoostController::create] ' . $e->getMessage());
            SessionManager::flash('error', 'No se pudo crear el boost. Intenta de nuevo.');
            $this->redirect("/perfil/{$idPerfil}/destacar");
        }
    }

    /**
     * POST /perfil/boost/{id}/cancelar
     * Solo boosts programados (no iniciados). Reembolso 100%.
     */
    public function cancel(array $params = []): void
    {
        $this->requireAuth();

        $idBoost = (int)($params['id'] ?? 0);
        $user    = $this->currentUser();
        $idUser  = (int)$user['id'];

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $result = $this->boosts->cancelar($idBoost, $idUser);

            if (!$result['ok']) {
                $db->rollBack();
                SessionManager::flash('error', $result['error'] ?? 'No se pudo cancelar.');
                $this->redirect('/mis-tokens');
            }

            $reembolso = (int)$result['reembolso_tokens'];
            $boost     = $result['boost'];

            if ($reembolso > 0) {
                $res = $this->movimientos->aplicar(
                    $idUser,
                    'reembolso',
                    $reembolso,
                    null,
                    $idBoost,
                    "Cancelación de boost programado #{$idBoost} (+{$reembolso} tokens)"
                );

                if (!$res['ok']) {
                    throw new RuntimeException($res['error'] ?? 'Error reembolsando tokens.');
                }
            }

            $db->commit();

            SessionManager::flash('success',
                $reembolso > 0
                    ? "Boost cancelado. Se reembolsaron {$reembolso} tokens."
                    : "Boost cancelado.");

            $redirectTo = !empty($boost['id_perfil'])
                ? "/perfil/{$boost['id_perfil']}/destacar"
                : '/mis-tokens';
            $this->redirect($redirectTo);

        } catch (\Throwable $e) {
            $db->rollBack();
            error_log('[BoostController::cancel] ' . $e->getMessage());
            SessionManager::flash('error', 'Error al cancelar el boost.');
            $this->redirect('/mis-tokens');
        }
    }
}
