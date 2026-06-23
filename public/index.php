<?php
declare(strict_types=1);

/**
 * ================================================================
 * OBE & E-PORTFOLIO SYSTEM - FRONT CONTROLLER
 * Điểm vào duy nhất của toàn bộ ứng dụng (Single Entry Point)
 * ================================================================
 */

// ── Bootstrap ─────────────────────────────────────────────────────
define('ROOT_PATH', dirname(__DIR__));
define('APP_VERSION', '2.0.0');

// Load core classes
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/BaseModel.php';
require_once ROOT_PATH . '/core/BaseController.php';
require_once ROOT_PATH . '/core/Router.php';

// Load controllers
require_once ROOT_PATH . '/app/Controllers/AuthController.php';
require_once ROOT_PATH . '/app/Controllers/StudentController.php';
require_once ROOT_PATH . '/app/Controllers/ScoreController.php';
require_once ROOT_PATH . '/app/Controllers/AdminController.php';
require_once ROOT_PATH . '/app/Controllers/LecturerController.php';

// Load models
require_once ROOT_PATH . '/app/Models/ScoreModel.php';
require_once ROOT_PATH . '/app/Models/AssessmentModel.php';

// ── Session ────────────────────────────────────────────────────────
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');
session_start();

// ── Error Handling ─────────────────────────────────────────────────
$isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';
if ($isProduction) {
    ini_set('display_errors', '0');
    error_reporting(0);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// ── Security Headers ───────────────────────────────────────────────
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ── Router ─────────────────────────────────────────────────────────
$router = new Router();

// ── Auth routes ────────────────────────────────────────────────────
$router->get('/login',  [AuthController::class, 'showLogin'],    'auth.login');
$router->post('/login', [AuthController::class, 'processLogin'], 'auth.process');
$router->get('/logout', [AuthController::class, 'logout'],       'auth.logout');

// ── Admin routes ───────────────────────────────────────────────────
$router->get('/admin/dashboard',                        [AdminController::class, 'dashboard'],        'admin.dashboard');
$router->get('/admin/programs',                         [AdminController::class, 'programs'],         'admin.programs');
$router->post('/admin/programs',                        [AdminController::class, 'storeProgram'],     'admin.programs.store');
$router->post('/admin/program/:id/update',             [AdminController::class, 'updateProgram'],     'admin.programs.update');
$router->post('/admin/program/:id/delete',             [AdminController::class, 'deleteProgram'],     'admin.programs.delete');
$router->get('/admin/program/:program_id/plos',         [AdminController::class, 'plos'],             'admin.plos');
$router->post('/admin/plos',                            [AdminController::class, 'storePlo'],         'admin.plos.store');
$router->post('/admin/plo/:id/delete',                  [AdminController::class, 'deletePlo'],        'admin.plos.delete');
$router->get('/admin/courses',                          [AdminController::class, 'courses'],          'admin.courses');
$router->post('/admin/courses',                         [AdminController::class, 'storeCourse'],      'admin.courses.store');
$router->get('/admin/course/:course_id/mapping',        [AdminController::class, 'mappingMatrix'],    'admin.mapping');
$router->get('/admin/users',                            [AdminController::class, 'users'],             'admin.users');
$router->post('/admin/user/store',                       [AdminController::class, 'storeUser'],         'admin.users.store');
$router->post('/admin/user/:id/update',                  [AdminController::class, 'updateUser'],        'admin.users.update');
$router->post('/admin/user/:id/toggle',                  [AdminController::class, 'toggleUserStatus'], 'admin.users.toggle');
$router->get('/admin/activity-log',                      [AdminController::class, 'activityLogs'],      'admin.activity_log');
$router->get('/admin/activity-logs',                     [AdminController::class, 'activityLogs'],      'admin.activity_logs');
$router->post('/admin/activity-log/:id/delete',          [AdminController::class, 'deleteActivityLog'], 'admin.activity_logs.delete');
$router->get('/admin/report/attainment/:program_id',    [AdminController::class, 'reportAttainment'], 'admin.report');

// ── Lecturer routes ────────────────────────────────────────────────
$router->post('/lecturer/assignment/:id/delete',         [LecturerController::class, 'deleteAssignment'], 'lecturer.assignments.delete');
$router->get('/lecturer/dashboard',                             [LecturerController::class, 'dashboard'],         'lecturer.dashboard');
$router->get('/lecturer/assignment/:assignment_id/clos',        [LecturerController::class, 'clos'],              'lecturer.clos');
$router->post('/lecturer/clos',                                 [LecturerController::class, 'storeClo'],          'lecturer.clos.store');
$router->post('/lecturer/clo/:id/delete',                       [LecturerController::class, 'deleteClo'],         'lecturer.clos.delete');
$router->get('/lecturer/assignment/:assignment_id/assessments', [LecturerController::class, 'assessments'],       'lecturer.assessments');
$router->post('/lecturer/assessments',                          [LecturerController::class, 'storeAssessment'],   'lecturer.assessments.store');
$router->get('/lecturer/assessment/:assessment_id/rubrics',     [LecturerController::class, 'rubrics'],           'lecturer.rubrics');
$router->post('/lecturer/rubrics',                              [LecturerController::class, 'storeRubric'],       'lecturer.rubrics.store');
$router->post('/lecturer/rubric/:id/delete',                    [LecturerController::class, 'deleteRubric'],      'lecturer.rubrics.delete');
$router->get('/lecturer/assessment/:id/grade',                  [ScoreController::class,    'gradingSheet'],      'lecturer.grade');
$router->get('/lecturer/assessment/:id/stats',                  [LecturerController::class, 'apiAssessmentStats'],'lecturer.stats');

// ── Student routes ─────────────────────────────────────────────────
$router->get('/student/dashboard',        [StudentController::class, 'dashboard'], 'student.dashboard');
$router->get('/student/courses',          [StudentController::class, 'courses'],   'student.courses');
$router->get('/student/portfolio/export', [StudentController::class, 'exportPdf'], 'student.export');

// ── API routes (AJAX) ──────────────────────────────────────────────
$router->post('/api/score/save',           [ScoreController::class, 'apiSave'],       'api.score.save');
$router->get('/api/score/student/:id/plo', [ScoreController::class, 'apiStudentPlo'], 'api.student.plo');
$router->post('/api/mapping/save',         [AdminController::class, 'saveMapping'],   'api.mapping.save');

// Root redirect
$router->get('/', function (array $p) {
    if (isset($_SESSION['user_id'])) {
        $role = $_SESSION['user_role'];
        header("Location: /{$role}/dashboard");
    } else {
        header('Location: /login');
    }
    exit;
}, 'home');

// ── Dispatch ────────────────────────────────────────────────────────
$router->dispatch();