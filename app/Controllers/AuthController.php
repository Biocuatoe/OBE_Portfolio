<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/BaseController.php';
require_once __DIR__ . '/../../core/AuthService.php';

/**
 * AuthController - Xử lý đăng nhập, đăng xuất.
 * All business logic delegated to AuthService.
 */
class AuthController extends BaseController
{
    private AuthService $auth;

    public function __construct()
    {
        parent::__construct();
        $this->auth = new AuthService($this->db);
    }

    public function showLogin(array $params): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect($this->auth->getRedirectPath($_SESSION['user_role']));
        }
        $this->view('auth/login', ['csrf_token' => $this->csrfToken()], 'auth');
    }

    public function processLogin(array $params): void
    {
        $this->verifyCsrf();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        try {
            $user = $this->auth->attempt($username, $password);
            $this->auth->login($user);
            $this->redirect($this->auth->getRedirectPath($user['role']));
        } catch (AuthException $e) {
            $this->view('auth/login', [
                'error'      => $e->getMessage(),
                'csrf_token' => $this->csrfToken(),
            ], 'auth');
        }
    }

    public function logout(array $params): void
    {
        $this->auth->logout();
        $this->redirect('/login');
    }
}
