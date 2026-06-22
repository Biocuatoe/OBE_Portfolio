<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';

/**
 * AuthService - Business logic for authentication.
 * All auth-related operations live here; controllers only orchestrate.
 */
final class AuthService
{
    private Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Validate credentials and return the user record.
     *
     * @throws AuthException when validation fails
     */
    public function attempt(string $username, string $password): array
    {
        $username = trim($username);

        if ($username === '' || $password === '') {
            throw new AuthException('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.');
        }

        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1",
            [$username, $username]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            usleep(random_int(200000, 500000)); // delay chống brute force
            throw new AuthException('Tên đăng nhập hoặc mật khẩu không đúng.');
        }

        return $user;
    }

    /**
     * Start an authenticated session for the given user.
     */
    public function login(array $user): void
    {
        session_regenerate_id(true);

        $_SESSION['user_id']   = (int)$user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];

        $this->db->logActivity(
            (int)$user['id'],
            'login',
            'user',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        );
    }

    /**
     * End the current session and log the event.
     */
    public function logout(): void
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);

        if ($userId > 0) {
            $this->db->logActivity(
                $userId,
                'logout',
                'user',
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            );
        }

        $_SESSION = [];
        session_destroy();
    }

    /**
     * Resolve the post-login redirect path by role.
     */
    public function getRedirectPath(string $role): string
    {
        return match ($role) {
            'admin'    => '/admin/dashboard',
            'lecturer' => '/lecturer/dashboard',
            'student'  => '/student/dashboard',
            default    => '/login',
        };
    }
}

/**
 * AuthException - thrown when authentication fails.
 */
final class AuthException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
