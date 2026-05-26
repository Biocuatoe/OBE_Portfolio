<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/BaseController.php';

/**
 * AdminController - Quản lý chương trình đào tạo, PLO, môn học
 */
class AdminController extends BaseController
{
    // ── Dashboard ───────────────────────────────────────────────────
    public function dashboard(array $params): void
    {
        $this->requireAuth('admin');

        $stats = [
            'programs'  => $this->db->fetchOne("SELECT COUNT(*) as c FROM programs")['c'],
            'courses'   => $this->db->fetchOne("SELECT COUNT(*) as c FROM courses")['c'],
            'lecturers' => $this->db->fetchOne("SELECT COUNT(*) as c FROM users WHERE role='lecturer'")['c'],
            'students'  => $this->db->fetchOne("SELECT COUNT(*) as c FROM users WHERE role='student'")['c'],
            'plos'      => $this->db->fetchOne("SELECT COUNT(*) as c FROM plos")['c'],
            'clos'      => $this->db->fetchOne("SELECT COUNT(*) as c FROM clos")['c'],
        ];

        // Attainment tổng hợp theo PLO
        $ploStats = $this->db->fetchAll(
            "SELECT p.code, p.description,
                ROUND(AVG(pa.achieved_percentage), 1) AS avg_pct,
                COUNT(DISTINCT pa.student_id)          AS student_count
             FROM plos p
             LEFT JOIN plo_attainments pa ON pa.plo_id = p.id
             GROUP BY p.id
             ORDER BY p.code"
        );

        // Recent activity
        $recentLogs = $this->db->fetchAll(
            "SELECT al.action, al.created_at, u.full_name, u.role
             FROM activity_logs al
             JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC
             LIMIT 20"
        );

        $this->view('admin/dashboard', [
            'pageTitle'   => 'Bảng điều khiển Admin',
            'stats'       => $stats,
            'plo_stats'   => $ploStats,
            'recent_logs' => $recentLogs,
        ]);
    }

    // ── Programs ─────────────────────────────────────────────────────
    public function programs(array $params): void
    {
        $this->requireAuth('admin');

        $programs = $this->db->fetchAll(
            "SELECT p.*,
                u.full_name AS admin_name,
                COUNT(DISTINCT c.id) AS course_count,
                COUNT(DISTINCT pl.id) AS plo_count
             FROM programs p
             JOIN users u ON u.id = p.admin_id
             LEFT JOIN courses c ON c.program_id = p.id
             LEFT JOIN plos pl ON pl.program_id = p.id
             GROUP BY p.id
             ORDER BY p.created_at DESC"
        );

        $this->view('admin/programs', [
            'pageTitle' => 'Chương trình đào tạo',
            'programs'  => $programs,
            'csrf_token' => $this->csrfToken(),
        ]);
    }

    public function storeProgram(array $params): void
    {
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $data = [
            'code'        => trim($_POST['code'] ?? ''),
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'admin_id'    => (int)$_SESSION['user_id'],
        ];

        if (empty($data['code']) || empty($data['name'])) {
            $this->json(['error' => 'Mã chương trình và tên không được để trống.'], 422);
        }

        $this->db->query(
            "INSERT INTO programs (code, name, description, admin_id) VALUES (?, ?, ?, ?)",
            array_values($data)
        );

        $this->json(['status' => 'success', 'id' => $this->db->lastInsertId()]);
    }

    // ── PLOs ─────────────────────────────────────────────────────────
    public function plos(array $params): void
    {
        $this->requireAuth('admin');

        $programId = (int)($params['program_id'] ?? 0);
        $program   = $this->db->fetchOne("SELECT * FROM programs WHERE id = ?", [$programId]);

        if (!$program) $this->redirect('/admin/programs');

        $plos = $this->db->fetchAll(
            "SELECT pl.*,
                COUNT(DISTINCT m.clo_id) AS mapped_clo_count,
                ROUND(AVG(pa.achieved_percentage), 1) AS avg_attainment
             FROM plos pl
             LEFT JOIN clo_plo_mappings m ON m.plo_id = pl.id
             LEFT JOIN plo_attainments pa ON pa.plo_id = pl.id
             WHERE pl.program_id = ?
             GROUP BY pl.id
             ORDER BY pl.code",
            [$programId]
        );

        $this->view('admin/plos', [
            'pageTitle'  => 'Chuẩn đầu ra (PLO) - ' . $program['code'],
            'program'    => $program,
            'plos'       => $plos,
            'csrf_token' => $this->csrfToken(),
        ]);
    }

    public function storePlo(array $params): void
    {
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $body = $this->jsonBody();
        $data = [
            'program_id'  => (int)($body['program_id'] ?? 0),
            'code'        => trim($body['code'] ?? ''),
            'description' => trim($body['description'] ?? ''),
            'category'    => trim($body['category'] ?? ''),
        ];

        if (!$data['program_id'] || !$data['code'] || !$data['description']) {
            $this->json(['error' => 'Thiếu thông tin bắt buộc.'], 422);
        }

        $this->db->query(
            "INSERT INTO plos (program_id, code, description, category) VALUES (?,?,?,?)",
            array_values($data)
        );

        $this->json(['status' => 'success', 'id' => $this->db->lastInsertId()]);
    }

    public function deletePlo(array $params): void
    {
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $id = (int)($params['id'] ?? 0);
        $this->db->query("DELETE FROM plos WHERE id = ?", [$id]);
        $this->json(['status' => 'success']);
    }

    // ── Courses ──────────────────────────────────────────────────────
    public function courses(array $params): void
    {
        $this->requireAuth('admin');

        $courses = $this->db->fetchAll(
            "SELECT c.*,
                p.name AS program_name,
                COUNT(DISTINCT ca.id) AS assignment_count,
                COUNT(DISTINCT e.student_id) AS student_count
             FROM courses c
             JOIN programs p ON p.id = c.program_id
             LEFT JOIN course_assignments ca ON ca.course_id = c.id
             LEFT JOIN enrollments e ON e.assignment_id = ca.id
             GROUP BY c.id
             ORDER BY c.code"
        );

        $programs  = $this->db->fetchAll("SELECT id, code, name FROM programs ORDER BY name");
        $lecturers = $this->db->fetchAll("SELECT id, full_name FROM users WHERE role='lecturer' ORDER BY full_name");

        $this->view('admin/courses', [
            'pageTitle'  => 'Quản lý môn học',
            'courses'    => $courses,
            'programs'   => $programs,
            'lecturers'  => $lecturers,
            'csrf_token' => $this->csrfToken(),
        ]);
    }

    public function storeCourse(array $params): void
    {
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $body = $this->jsonBody();
        $data = [
            'program_id'  => (int)($body['program_id'] ?? 0),
            'code'        => strtoupper(trim($body['code'] ?? '')),
            'name'        => trim($body['name'] ?? ''),
            'credits'     => (int)($body['credits'] ?? 3),
            'description' => trim($body['description'] ?? ''),
        ];

        if (!$data['program_id'] || !$data['code'] || !$data['name']) {
            $this->json(['error' => 'Thiếu thông tin bắt buộc.'], 422);
        }

        $this->db->beginTransaction();
        try {
            $this->db->query(
                "INSERT INTO courses (program_id, code, name, credits, description) VALUES (?,?,?,?,?)",
                array_values($data)
            );
            $courseId = (int)$this->db->lastInsertId();

            // Nếu có phân công giảng viên ngay
            if (!empty($body['lecturer_id']) && !empty($body['semester'])) {
                $this->db->query(
                    "INSERT INTO course_assignments (course_id, lecturer_id, semester) VALUES (?,?,?)",
                    [$courseId, (int)$body['lecturer_id'], trim($body['semester'])]
                );
            }

            $this->db->commit();
            $this->json(['status' => 'success', 'id' => $courseId]);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── Mapping Matrix ───────────────────────────────────────────────
    public function mappingMatrix(array $params): void
    {
        $this->requireAuth('admin', 'lecturer');

        $courseId = (int)($params['course_id'] ?? 0);
        $course   = $this->db->fetchOne("SELECT c.*, p.id AS program_id FROM courses c JOIN programs p ON p.id = c.program_id WHERE c.id = ?", [$courseId]);

        if (!$course) $this->redirect('/admin/courses');

        $clos = $this->db->fetchAll("SELECT * FROM clos WHERE course_id = ? ORDER BY code", [$courseId]);
        $plos = $this->db->fetchAll("SELECT * FROM plos WHERE program_id = ? ORDER BY code", [$course['program_id']]);

        // Lấy ma trận hiện tại
        $mappings = $this->db->fetchAll(
            "SELECT m.clo_id, m.plo_id, m.weight
             FROM clo_plo_mappings m
             JOIN clos c ON c.id = m.clo_id
             WHERE c.course_id = ?",
            [$courseId]
        );

        // Chuyển sang cấu trúc: $matrix[clo_id][plo_id] = weight
        $matrix = [];
        foreach ($mappings as $m) {
            $matrix[$m['clo_id']][$m['plo_id']] = $m['weight'];
        }

        $this->view('admin/mapping_matrix', [
            'pageTitle'  => 'Ma trận CLO-PLO - ' . $course['code'],
            'course'     => $course,
            'clos'       => $clos,
            'plos'       => $plos,
            'matrix'     => $matrix,
            'csrf_token' => $this->csrfToken(),
        ]);
    }

    /**
     * POST /api/mapping/save
     * Body: { clo_id, plo_id, weight }
     */
    public function saveMapping(array $params): void
    {
        $this->requireAuth('admin', 'lecturer');
        $this->verifyCsrf();

        $body   = $this->jsonBody();
        $cloId  = (int)($body['clo_id']  ?? 0);
        $ploId  = (int)($body['plo_id']  ?? 0);
        $weight = (float)($body['weight'] ?? 0);

        if (!$cloId || !$ploId || $weight < 0 || $weight > 100) {
            $this->json(['error' => 'Dữ liệu không hợp lệ. Trọng số phải từ 0-100.'], 422);
        }

        if ($weight === 0.0) {
            // Xóa mapping nếu weight = 0
            $this->db->query("DELETE FROM clo_plo_mappings WHERE clo_id=? AND plo_id=?", [$cloId, $ploId]);
        } else {
            $this->db->query(
                "INSERT INTO clo_plo_mappings (clo_id, plo_id, weight) VALUES (?,?,?)
                 ON DUPLICATE KEY UPDATE weight = VALUES(weight)",
                [$cloId, $ploId, $weight]
            );
        }

        $this->json(['status' => 'success']);
    }

    // ── Users Management ─────────────────────────────────────────────
    public function users(array $params): void
    {
        $this->requireAuth('admin');

        $role  = $_GET['role'] ?? '';
        $where = $role ? "WHERE role = ?" : "WHERE 1=1";
        $args  = $role ? [$role] : [];

        $users = $this->db->fetchAll("SELECT id, username, email, full_name, role, is_active, created_at FROM users $where ORDER BY role, full_name", $args);

        $this->view('admin/users', [
            'pageTitle'  => 'Quản lý người dùng',
            'users'      => $users,
            'filter_role' => $role,
            'csrf_token' => $this->csrfToken(),
        ]);
    }

    public function storeUser(array $params): void
    {
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $body = $this->jsonBody();
        $password = $body['password'] ?? '';

        if (strlen($password) < 8) {
            $this->json(['error' => 'Mật khẩu phải có ít nhất 8 ký tự.'], 422);
        }

        $data = [
            'username'  => trim($body['username'] ?? ''),
            'email'     => trim($body['email'] ?? ''),
            'password'  => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'full_name' => trim($body['full_name'] ?? ''),
            'role'      => in_array($body['role'] ?? '', ['admin','lecturer','student']) ? $body['role'] : 'student',
        ];

        if (!$data['username'] || !$data['email'] || !$data['full_name']) {
            $this->json(['error' => 'Thiếu thông tin bắt buộc.'], 422);
        }

        try {
            $this->db->query(
                "INSERT INTO users (username, email, password, full_name, role) VALUES (?,?,?,?,?)",
                array_values($data)
            );
            $this->json(['status' => 'success', 'id' => $this->db->lastInsertId()]);
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate')) {
                $this->json(['error' => 'Username hoặc email đã tồn tại.'], 409);
            }
            throw $e;
        }
    }

    // ── Report: Program Attainment Overview ──────────────────────────
    public function reportAttainment(array $params): void
    {
        $this->requireAuth('admin');

        $programId = (int)($params['program_id'] ?? 1);

        // Tổng quan attainment theo từng PLO
        $ploReport = $this->db->fetchAll(
            "SELECT
                p.code, p.description, p.category,
                COUNT(DISTINCT pa.student_id)          AS measured_students,
                ROUND(AVG(pa.achieved_percentage), 1)  AS avg_pct,
                SUM(pa.achieved_percentage >= 70)       AS passed_count,
                COUNT(pa.student_id)                    AS total_count
             FROM plos p
             LEFT JOIN plo_attainments pa ON pa.plo_id = p.id
             WHERE p.program_id = ?
             GROUP BY p.id
             ORDER BY p.code",
            [$programId]
        );

        // Top sinh viên
        $topStudents = $this->db->fetchAll(
            "SELECT u.full_name, u.username,
                ROUND(AVG(pa.achieved_percentage), 1) AS overall_pct
             FROM plo_attainments pa
             JOIN users u ON u.id = pa.student_id
             JOIN plos p ON p.id = pa.plo_id
             WHERE p.program_id = ?
             GROUP BY pa.student_id
             ORDER BY overall_pct DESC
             LIMIT 10",
            [$programId]
        );

        $programs = $this->db->fetchAll("SELECT * FROM programs ORDER BY code");

        $this->view('admin/report_attainment', [
            'pageTitle'    => 'Báo cáo đạt chuẩn PLO',
            'plo_report'   => $ploReport,
            'top_students' => $topStudents,
            'programs'     => $programs,
            'program_id'   => $programId,
        ]);
    }
}
