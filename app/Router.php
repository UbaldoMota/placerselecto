<?php
/**
 * Router.php
 * Motor de enrutamiento de la aplicación.
 * Resuelve la URI actual contra las rutas definidas en routes/web.php,
 * aplica middleware y despacha al controlador correspondiente.
 */

class Router
{
    /** @var array<int, array> Tabla de rutas cargadas */
    private array $routes = [];

    /** @var array<string, callable> Middleware registrados */
    private array $middlewares = [];

    /**
     * Carga las rutas desde el archivo de definición.
     *
     * @param string $routesFile Ruta absoluta al archivo de rutas
     */
    public function loadRoutes(string $routesFile): void
    {
        $this->routes = require $routesFile;
    }

    /**
     * Registra un middleware con su nombre.
     * Signature del callable: function(callable $next): void
     */
    public function registerMiddleware(string $name, callable $handler): void
    {
        $this->middlewares[$name] = $handler;
    }

    /**
     * Despacha la petición actual.
     * Busca la ruta que coincide con METHOD + URI, ejecuta middlewares y controlador.
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = $this->parseUri();

        // Soporte básico para POST tunneling (_method override)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper(trim($_POST['_method']));
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $override;
            }
        }

        foreach ($this->routes as $route) {
            [$routeMethod, $pattern, $controllerName, $action, $routeMiddlewares] = $route;

            if ($routeMethod !== $method) {
                continue;
            }

            $params = $this->matchRoute($pattern, $uri);
            if ($params === false) {
                continue;
            }

            // Ruta encontrada — ejecutar middleware pipeline
            $this->runMiddlewares(
                $routeMiddlewares,
                function () use ($controllerName, $action, $params) {
                    $this->callController($controllerName, $action, $params);
                }
            );

            return; // Ruta resuelta, salir del dispatch
        }

        // Ninguna ruta coincidió → 404
        $this->notFound();
    }

    /**
     * Compara el patrón de ruta con la URI actual.
     * Convierte {param} en grupos de captura regex.
     *
     * @return array|false  Array de parámetros capturados, o false si no coincide
     */
    private function matchRoute(string $pattern, string $uri): array|false
    {
        // Separar el patrón en segmentos estáticos y tokens {param}
        $parts = preg_split(
            '/(\{[a-zA-Z_][a-zA-Z0-9_]*\})/',
            $pattern,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );

        $regexPattern = '';
        foreach ($parts as $part) {
            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $part, $m)) {
                // Token dinámico → grupo de captura nombrado
                $regexPattern .= '(?P<' . $m[1] . '>[^/]+)';
            } else {
                // Segmento estático → escapar para regex
                $regexPattern .= preg_quote($part, '#');
            }
        }

        $regex = '#^' . $regexPattern . '$#';

        if (!preg_match($regex, $uri, $matches)) {
            return false;
        }

        // Devolver solo los grupos nombrados (sin índices numéricos)
        return array_filter(
            $matches,
            fn($key) => !is_int($key),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Ejecuta el pipeline de middlewares y finalmente el controlador.
     *
     * @param array<string> $middlewareNames
     * @param callable      $final Controlador a ejecutar al final
     */
    private function runMiddlewares(array $middlewareNames, callable $final): void
    {
        // Construir pipeline en orden inverso (onion)
        $pipeline = $final;

        foreach (array_reverse($middlewareNames) as $name) {
            if (!isset($this->middlewares[$name])) {
                throw new RuntimeException("Middleware no registrado: [{$name}]");
            }

            $handler  = $this->middlewares[$name];
            $next     = $pipeline;
            $pipeline = fn() => $handler($next);
        }

        $pipeline();
    }

    /**
     * Instancia el controlador y llama al método de acción.
     *
     * @param string $controllerName Nombre de la clase (sin namespace)
     * @param string $action         Nombre del método
     * @param array  $params         Parámetros de ruta capturados
     */
    private function callController(string $controllerName, string $action, array $params): void
    {
        $file = APP_PATH . '/controllers/' . $controllerName . '.php';

        if (!file_exists($file)) {
            throw new RuntimeException("Controlador no encontrado: [{$controllerName}]");
        }

        require_once $file;

        if (!class_exists($controllerName)) {
            throw new RuntimeException("Clase no definida: [{$controllerName}]");
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $action)) {
            throw new RuntimeException("Método [{$action}] no existe en [{$controllerName}]");
        }

        $controller->$action($params);
    }

    /**
     * Normaliza y retorna la URI actual (sin query string ni base path).
     */
    private function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Eliminar query string
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        // Eliminar posible subdirectorio base (útil en localhost/Publicidad)
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($basePath && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = '/' . ltrim(rawurldecode($uri), '/');

        // Eliminar trailing slash (excepto root)
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        return $uri;
    }

    /**
     * Responde con un error 404.
     */
    private function notFound(): void
    {
        http_response_code(404);
        $viewFile = VIEWS_PATH . '/partials/404.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo '<h1>404 - Página no encontrada</h1>';
        }
    }
}
