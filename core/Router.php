<?php
declare(strict_types=1);

/**
 * Router - Bộ định tuyến URL tự xây dựng
 *
 * Hỗ trợ: GET, POST, middleware (auth guard), named routes, route groups.
 * URL pattern: /controller/action/param1/param2
 */
class Router
{
    private array $routes    = [];
    private array $namedRoutes = [];
    private string $basePath = '';

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }

    // ── Đăng ký routes ─────────────────────────────────────────────

    public function get(string $path, array|callable $handler, ?string $name = null, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $name, $middleware);
    }

    public function post(string $path, array|callable $handler, ?string $name = null, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $name, $middleware);
    }

    private function addRoute(string $method, string $path, array|callable $handler, ?string $name, array $middleware): self
    {
        $pattern = $this->pathToRegex($path);
        $route   = compact('method', 'path', 'pattern', 'handler', 'middleware');

        $this->routes[] = $route;

        if ($name) {
            $this->namedRoutes[$name] = $path;
        }

        return $this;
    }

    private function pathToRegex(string $path): string
    {
        // :id → named capture group (?P<id>[^/]+)
        $pattern = preg_replace('/\/:([a-zA-Z_]+)/', '/(?P<$1>[^/]+)', $path);
        return '#^' . $this->basePath . $pattern . '/?$#';
    }

    // ── Dispatch ────────────────────────────────────────────────────

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Hỗ trợ method override (PUT, DELETE từ form)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Lọc lấy named params
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Chạy middleware
                foreach ($route['middleware'] as $mw) {
                    if (is_callable($mw)) {
                        $mw();
                    } elseif (function_exists($mw)) {
                        call_user_func($mw);
                    }
                }

                // Gọi handler
                if (is_callable($route['handler'])) {
                    call_user_func($route['handler'], $params);
                } elseif (is_array($route['handler'])) {
                    [$controllerClass, $action] = $route['handler'];
                    $controller = new $controllerClass();
                    $controller->$action($params);
                }

                return;
            }
        }

        // 404
        http_response_code(404);
        require __DIR__ . '/../app/Views/errors/404.php';
    }

    // ── URL generation ──────────────────────────────────────────────

    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \InvalidArgumentException("Route '{$name}' không tồn tại.");
        }

        $path = $this->namedRoutes[$name];
        foreach ($params as $key => $val) {
            $path = str_replace(':' . $key, (string)$val, $path);
        }

        return $this->basePath . $path;
    }
}
