<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/BaseController.php';

/**
 * AuthController - Xử lý đăng nhập, đăng xuất
 */
class AuthController extends BaseController
{
    public function showLogin(array $params): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirectByRole();
        }
        $this->view('auth/login', ['csrf_token' => $this->csrfToken()], 'auth');
    }

    public function processLogin(array $params): void
    {
        $this->verifyCsrf();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->view('auth/login', [
                'error'      => 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.',
                'csrf_token' => $this->csrfToken(),
            ], 'auth');
            return;
        }

        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1",
            [$username, $username]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            // Delay chống brute force
            usleep(random_int(200000, 500000));

            $this->view('auth/login', [
                'error'      => 'Tên đăng nhập hoặc mật khẩu không đúng.',
                'csrf_token' => $this->csrfToken(),
            ], 'auth');
            return;
        }

        // Regenerate session ID để ngăn session fixation
        session_regenerate_id(true);

        $_SESSION['user_id']   = (int)$user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];

        // Log hoạt động
        $this->db->query(
            "INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, 'login', ?)",
            [$user['id'], $_SERVER['REMOTE_ADDR'] ?? 'unknown']
        );

        $this->redirectByRole();
    }

    public function logout(array $params): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->db->query(
                "INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, 'logout', ?)",
                [$_SESSION['user_id'], $_SERVER['REMOTE_ADDR'] ?? 'unknown']
            );
        }

        $_SESSION = [];
        session_destroy();
        $this->redirect('/login');
    }

    private function redirectByRole(): never
    {
        $role = $_SESSION['user_role'] ?? 'student';
        $destinations = [
            'admin'    => '/admin/dashboard',
            'lecturer' => '/lecturer/dashboard',
            'student'  => '/student/dashboard',
        ];
        $this->redirect($destinations[$role] ?? '/login');
    }
}
