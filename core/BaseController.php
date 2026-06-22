<?php
declare(strict_types=1);

/**
 * BaseController - Controller cha
 *
 * Cung cấp helper: render view, redirect, JSON response, auth check.
 */
abstract class BaseController
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── View Rendering ───────────────────────────────────────────────

    /**
     * Render một view với layout
     *
     * @param string $viewPath  VD: 'student/dashboard'
     * @param array  $data      Dữ liệu truyền vào view
     * @param string $layout    Tên layout file (không có .php)
     */
    protected function view(string $viewPath, array $data = [], string $layout = 'main'): void
    {
        $data['csrf_token'] = $this->csrfToken();
        extract($data, EXTR_SKIP);

        $viewFile = __DIR__ . "/../app/Views/{$viewPath}.php";
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View không tồn tại: {$viewFile}");
        }

        // Render view content vào buffer
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Wrap vào layout
        $layoutFile = __DIR__ . "/../app/Views/layouts/{$layout}.php";
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    // ── HTTP Helpers ─────────────────────────────────────────────────

    protected function redirect(string $url, int $code = 302): never
    {
        http_response_code($code);
        header("Location: {$url}");
        exit;
    }

    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    // ── Auth Helpers ─────────────────────────────────────────────────

    protected function requireAuth(string ...$roles): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        if (!empty($roles) && !in_array($_SESSION['user_role'], $roles, true)) {
            http_response_code(403);
            require __DIR__ . '/../app/Views/errors/403.php';
            exit;
        }
    }

    protected function currentUser(): ?array
    {
        if (!isset($_SESSION['user_id'])) return null;
        return [
            'id'        => $_SESSION['user_id'],
            'full_name' => $_SESSION['user_name'],
            'role'      => $_SESSION['user_role'],
        ];
    }

    // ── Input Helpers ────────────────────────────────────────────────

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }
        return $data ?? [];
    }

    protected function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrf(): void
    {
        $token = $_POST['_token'] ?? $this->jsonBody()['_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            throw new \JsonException('CSRF token không hợp lệ.');
        }
    }
}
