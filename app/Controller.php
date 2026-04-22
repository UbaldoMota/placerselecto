<?php
/**
 * Controller.php
 * Clase base de la que extienden todos los controladores.
 * Provee helpers comunes: render de vistas, redirecciones, JSON responses,
 * validación, sanitización y verificación de permisos.
 */

abstract class Controller
{
    /**
     * Renderiza una vista PHP pasándole datos.
     *
     * @param string $view   Ruta relativa a VIEWS_PATH sin extensión  (ej. 'ads/index')
     * @param array  $data   Variables que estarán disponibles en la vista
     * @param int    $status Código HTTP de respuesta
     */
    protected function render(string $view, array $data = [], int $status = 200): void
    {
        http_response_code($status);

        // Extraer $data como variables locales en la vista
        extract($data, EXTR_SKIP);

        // Datos globales disponibles en TODAS las vistas
        $currentUser  = $this->currentUser();
        $csrfField    = Middleware::csrfField();
        $flashMessages = SessionManager::getAllFlash();
        $appName      = APP_NAME;
        $appUrl       = APP_URL;

        $viewFile = VIEWS_PATH . '/' . ltrim($view, '/') . '.php';

        if (!file_exists($viewFile)) {
            throw new RuntimeException("Vista no encontrada: [{$view}]");
        }

        // Las vistas en auth/ y la vista age-gate son standalone (tienen su propio HTML completo)
        $standaloneViews = [
            'auth/login', 'auth/register', 'auth/recover', 'auth/age-gate',
            'auth/registro-tipo', 'auth/registro-contacto',
            'auth/registro-verificar-sms', 'auth/registro-verificar-email',
            'auth/registro-completar', 'auth/registro-comentarista',
        ];
        $isStandalone    = in_array($view, $standaloneViews, true);

        $layoutFile = VIEWS_PATH . '/partials/layout.php';
        $content    = $viewFile; // La vista principal se incluye dentro del layout

        if (!$isStandalone && file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            require $viewFile;
        }
    }

    /**
     * Renderiza una vista SIN layout (para fragmentos AJAX, emails, etc.).
     */
    protected function renderPartial(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = VIEWS_PATH . '/' . ltrim($view, '/') . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        }
    }

    /**
     * Responde con JSON. Termina la ejecución.
     *
     * @param mixed $data
     * @param int   $status
     */
    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Redirecciona a una URL. Termina la ejecución.
     *
     * @param string $path  Path relativo a APP_URL o URL absoluta
     * @param int    $code  Código de redirección (301 | 302)
     */
    protected function redirect(string $path, int $code = 302): void
    {
        $url = str_starts_with($path, 'http') ? $path : APP_URL . '/' . ltrim($path, '/');
        header('Location: ' . $url, true, $code);
        exit;
    }

    /**
     * Retorna el usuario actualmente autenticado desde la sesión.
     *
     * @return array|null
     */
    protected function currentUser(): ?array
    {
        if (!SessionManager::has('user_id')) {
            return null;
        }

        return [
            'id'                  => SessionManager::get('user_id'),
            'nombre'              => SessionManager::get('user_nombre'),
            'email'               => SessionManager::get('user_email'),
            'rol'                 => SessionManager::get('user_rol'),
            'verificado'          => SessionManager::get('user_verificado'),
            'estado_verificacion' => SessionManager::get('user_estado_verificacion'),
        ];
    }

    /**
     * Verifica que el usuario esté autenticado o redirige.
     */
    protected function requireAuth(): void
    {
        if (!SessionManager::has('user_id')) {
            SessionManager::flash('error', 'Debes iniciar sesión.');
            $this->redirect('/login');
        }
    }

    /**
     * Verifica que el usuario sea admin o aborta con 403.
     */
    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (SessionManager::get('user_rol') !== 'admin') {
            http_response_code(403);
            require VIEWS_PATH . '/partials/403.php';
            exit;
        }
    }

    /**
     * Verifica que el usuario esté verificado o muestra aviso.
     */
    protected function requireVerified(): void
    {
        $this->requireAuth();
        if (!SessionManager::get('user_verificado')) {
            SessionManager::flash('warning', 'Tu cuenta está pendiente de verificación. No puedes publicar aún.');
            $this->redirect('/dashboard');
        }
    }

    /**
     * Sanitiza un string contra XSS.
     */
    protected function sanitize(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitiza un array de inputs.
     *
     * @param array<string, string> $inputs
     * @return array<string, string>
     */
    protected function sanitizeArray(array $inputs): array
    {
        return array_map([$this, 'sanitize'], $inputs);
    }

    /**
     * Valida campos requeridos.
     * Retorna array de errores (vacío = sin errores).
     *
     * @param array<string, string> $data    Datos a validar
     * @param array<string, string> $rules   ['campo' => 'Nombre del campo']
     * @return array<string>
     */
    protected function validateRequired(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $label) {
            if (empty(trim($data[$field] ?? ''))) {
                $errors[] = "El campo [{$label}] es requerido.";
            }
        }
        return $errors;
    }

    /**
     * Devuelve el valor de un parámetro GET sanitizado.
     */
    protected function getParam(string $key, string $default = ''): string
    {
        return $this->sanitize($_GET[$key] ?? $default);
    }

    /**
     * Devuelve el valor de un campo POST sanitizado.
     */
    protected function postParam(string $key, string $default = ''): string
    {
        return $this->sanitize($_POST[$key] ?? $default);
    }

    /**
     * Devuelve el valor de un parámetro de ruta (capturado por el router).
     */
    protected function routeParam(array $params, string $key, string $default = ''): string
    {
        return $this->sanitize($params[$key] ?? $default);
    }

    /**
     * Verifica que la petición sea POST (protección básica).
     */
    protected function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');
        }
    }

    /**
     * Verifica si la petición es una llamada AJAX.
     */
    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Obtiene el cuerpo JSON de la petición (para APIs).
     *
     * @return array<string, mixed>
     */
    protected function getJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Valida que un entero sea positivo.
     */
    protected function validateId(mixed $id): int
    {
        $id = (int) $id;
        if ($id <= 0) {
            $this->json(['error' => 'ID inválido'], 400);
        }
        return $id;
    }
}
