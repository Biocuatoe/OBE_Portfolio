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

        // Bar chart: sinh viên theo chương trình đào tạo
        $programStudentData = $this->db->fetchAll(
            "SELECT p.name AS program_name, p.code AS program_code,
                COUNT(DISTINCT CASE WHEN u.role='student' THEN u.id END) AS student_count
             FROM programs p
             LEFT JOIN courses c ON c.program_id = p.id
             LEFT JOIN course_assignments ca ON ca.course_id = c.id
             LEFT JOIN enrollments e ON e.assignment_id = ca.id
             LEFT JOIN users u ON u.id = e.student_id
             GROUP BY p.id
             ORDER BY p.code"
        );

        // Doughnut: tỷ lệ đạt / không đạt PLO (threshold 70%)
        $ploAttainmentData = $this->db->fetchAll(
            "SELECT
                COUNT(DISTINCT CASE WHEN pa.achieved_percentage >= 70 THEN pa.student_id END) AS pass_count,
                COUNT(DISTINCT CASE WHEN pa.achieved_percentage < 70  THEN pa.student_id END) AS fail_count
             FROM plos p
             LEFT JOIN plo_attainments pa ON pa.plo_id = p.id
             WHERE pa.student_id IS NOT NULL"
        );

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
            "SELECT al.action, al.created_at, u.full_name, u.role, al.entity
             FROM activity_logs al
             JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC
             LIMIT 30"
        );

        $this->view('admin/dashboard', [
            'pageTitle'             => 'Bảng điều khiển Admin',
            'stats'                 => $stats,
            'plo_stats'             => $ploStats,
            'recent_logs'           => $recentLogs,
            'program_student_data'  => $programStudentData,
            'plo_attainment_data'   => $ploAttainmentData,
        ]);
    }

    // ── Programs ─────────────────────────────────────────────────────
    public function programs(array $params): void
    {
        $this->requireAuth('admin');

        $programs = $this->db->fetchAll(
            "SELECT p.*,
                u.full_name AS admin_name,
                COUNT(DISTINCT c.id)          AS course_count,
                COUNT(DISTINCT pl.id)         AS plo_count,
                COUNT(DISTINCT ca.id)         AS assignment_count,
                COUNT(DISTINCT e.student_id)  AS student_count
             FROM programs p
             JOIN users u ON u.id = p.admin_id
             LEFT JOIN courses c ON c.program_id = p.id
             LEFT JOIN plos pl ON pl.program_id = p.id
             LEFT JOIN course_assignments ca ON ca.course_id = c.id
             LEFT JOIN enrollments e ON e.assignment_id = ca.id
             GROUP BY p.id
             ORDER BY p.created_at DESC"
        );

        $this->view('admin/programs', [
            'pageTitle' => 'Chương trình đào tạo',
            'programs'  => $programs,
            'csrf_token' => $this->csrfToken(),
        ]);
    }

    public function programs(array $params): void
    {
        $this->requireAuth('admin');

        $programs = $this->db->fetchAll(
            "SELECT p.*,
                u.full_name AS admin_name,
                COUNT(DISTINCT c.id)   AS course_count,
                COUNT(DISTINCT pl.id)  AS plo_count,
                COUNT(DISTINCT ca.id)  AS lecturer_count,
                COUNT(DISTINCT e.student_id) AS student_count
             FROM programs p
             JOIN users u ON u.id = p.admin_id
             LEFT JOIN courses c ON c.program_id = p.id
             LEFT JOIN plos pl ON pl.program_id = p.id
             LEFT JOIN course_assignments ca ON ca.course_id = c.id
             LEFT JOIN enrollments e ON e.assignment_id = ca.id
             GROUP BY p.id
             ORDER BY p.created_at DESC"
        );

        $this->view('admin/programs', [
            'pageTitle'   => 'Chương trình đào tạo',
            'programs'    => $programs,
            'csrf_token' => $this->csrfToken(),
        ]);
    }

    public function storeProgram(array $params): void
    {
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $body = $this->jsonBody();
        $data = [
            'code'        => trim($body['code'] ?? ''),
            'name'        => trim($body['name'] ?? ''),
            'description' => trim($body['description'] ?? ''),
            'admin_id'    => (int)$_SESSION['user_id'],
        ];

        $errors = [];
        if ($data['code'] === '') {
            $errors['code'] = 'Mã chương trình không được để trống.';
        } elseif (!preg_match('/^[A-Z0-9\-]{2,20}$/', $data['code'])) {
            $errors['code'] = 'Mã chỉ gồm chữ hoa, số, gạch ngang (2-20 ký tự).';
        }
        if ($data['name'] === '') {
            $errors['name'] = 'Tên chương trình không được để trống.';
        }

        if (!empty($errors)) {
            $this->json(['error' => 'Validation failed', 'fields' => $errors], 422);
        }

        $existing = $this->db->fetchOne(
            "SELECT id FROM programs WHERE code = ? LIMIT 1",
            [$data['code']]
        );
        if ($existing) {
            $this->json([
                'error'  => 'Mã chương trình đã tồn tại.',
                'fields' => ['code' => 'Mã chương trình đã tồn tại.'],
            ], 409);
        }

        $this->db->query(
            "INSERT INTO programs (code, name, description, admin_id) VALUES (?, ?, ?, ?)",
            array_values($data)
        );
        $this->json(['status' => 'success', 'id' => $this->db->lastInsertId()]);
    }

    public function updateProgram(array $params): void
    {
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $id   = (int)($params['id'] ?? 0);
        $body = $this->jsonBody();

        $data = [
            'code'        => trim($body['code'] ?? ''),
            'name'        => trim($body['name'] ?? ''),
            'description' => trim($body['description'] ?? ''),
        ];

        $errors = [];
        if ($data['code'] === '') {
            $errors['code'] = 'Mã chương trình không được để trống.';
        } elseif (!preg_match('/^[A-Z0-9\-]{2,20}$/', $data['code'])) {
            $errors['code'] = 'Mã chỉ gồm chữ hoa, số, gạch ngang (2-20 ký tự).';
        }
        if ($data['name'] === '') {
            $errors['name'] = 'Tên chương trình không được để trống.';
        }

        if (!empty($errors)) {
            $this->json(['error' => 'Validation failed', 'fields' => $errors], 422);
        }

        $existing = $this->db->fetchOne(
            "SELECT id FROM programs WHERE code = ? AND id != ? LIMIT 1",
            [$data['code'], $id]
        );
        if ($existing) {
            $this->json([
                'error'  => 'Mã chương trình đã tồn tại.',
                'fields' => ['code' => 'Mã chương trình đã tồn tại.'],
            ], 409);
        }

        $this->db->query(
            "UPDATE programs SET code=?, name=?, description=? WHERE id=?",
            [$data['code'], $data['name'], $data['description'], $id]
        );
        $this->json(['status' => 'success']);
    }

    public function deleteProgram(array $params): void
    {
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $id = (int)($params['id'] ?? 0);

        $courseCount = $this->db->fetchOne(
            "SELECT COUNT(*) as c FROM courses WHERE program_id = ?",
            [$id]
        )['c'];

        if ($courseCount > 0) {
            $this->json([
                'error' => "Không thể xóa: chương trình đang có {$courseCount} môn học gắn kết.",
            ], 409);
        }

        $this->db->query("DELETE FROM programs WHERE id = ?", [$id]);
        $this->json(['status' => 'success']);
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

        // Validate required fields
        $errors = [];
        if (!$data['program_id']) {
            $errors['program_id'] = 'Vui lòng chọn chương trình đào tạo.';
        }
        if ($data['code'] === '') {
            $errors['code'] = 'Mã PLO không được để trống.';
        } elseif (!preg_match('/^[A-Z0-9\-]{2,20}$/', $data['code'])) {
            $errors['code'] = 'Mã PLO gồm chữ hoa, số, gạch ngang (2-20 ký tự).';
        }
        if ($data['description'] === '') {
            $errors['description'] = 'Mô tả không được để trống.';
        }

        if (!empty($errors)) {
            $this->json(['error' => 'Validation failed', 'fields' => $errors], 422);
        }

        // Check duplicate code within the same program
        $existing = $this->db->fetchOne(
            "SELECT id FROM plos WHERE program_id = ? AND code = ? LIMIT 1",
            [$data['program_id'], $data['code']]
        );
        if ($existing) {
            $this->json([
                'error'  => 'Mã PLO đã tồn tại trong chương trình này.',
                'fields' => ['code' => 'Mã PLO đã tồn tại trong chương trình này.'],
            ], 409);
        }

        $this->db->query(
            "INSERT INTO plos (program_id, code, description, category) VALUES (?,?,?,?)",
            array_values($data)
        );
        $this->json(['status' => 'success', 'id' => $this->db->lastInsertId()]);
    }

    public function updatePlo(array $params): void
    {
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $id   = (int)($params['id'] ?? 0);
        $body = $this->jsonBody();

        $data = [
            'program_id'  => (int)($body['program_id'] ?? 0),
            'code'        => trim($body['code'] ?? ''),
            'description' => trim($body['description'] ?? ''),
            'category'    => trim($body['category'] ?? ''),
        ];

        // Validate required fields
        $errors = [];
        if (!$data['program_id']) {
            $errors['program_id'] = 'Vui lòng chọn chương trình đào tạo.';
        }
        if ($data['code'] === '') {
            $errors['code'] = 'Mã PLO không được để trống.';
        } elseif (!preg_match('/^[A-Z0-9\-]{2,20}$/', $data['code'])) {
            $errors['code'] = 'Mã PLO gồm chữ hoa, số, gạch ngang (2-20 ký tự).';
        }
        if ($data['description'] === '') {
            $errors['description'] = 'Mô tả không được để trống.';
        }

        if (!empty($errors)) {
            $this->json(['error' => 'Validation failed', 'fields' => $errors], 422);
        }

        // Check duplicate code (excluding current record)
        $existing = $this->db->fetchOne(
            "SELECT id FROM plos WHERE program_id = ? AND code = ? AND id != ? LIMIT 1",
            [$data['program_id'], $data['code'], $id]
        );
        if ($existing) {
            $this->json([
                'error'  => 'Mã PLO đã tồn tại trong chương trình này.',
                'fields' => ['code' => 'Mã PLO đã tồn tại trong chương trình này.'],
            ], 409);
        }

        $this->db->query(
            "UPDATE plos SET program_id=?, code=?, description=?, category=? WHERE id=?",
            [$data['program_id'], $data['code'], $data['description'], $data['category'], $id]
        );
        $this->json(['status' => 'success']);
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
                p.code AS program_code,
                COUNT(DISTINCT ca.id)        AS assignment_count,
                COUNT(DISTINCT e.student_id) AS student_count,
                COUNT(DISTINCT ca.lecturer_id) AS lecturer_count
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

        // Validation
        $errors = [];
        if ($data['program_id'] === 0) {
            $errors['program_id'] = 'Vui lòng chọn chương trình đào tạo.';
        }
        if ($data['code'] === '') {
            $errors['code'] = 'Mã môn học không được để trống.';
        } elseif (!preg_match('/^[A-Z]{2,6}[0-9]{2,4}[A-Z]?$/i', $data['code'])) {
            $errors['code'] = 'Mã môn học không hợp lệ (VD: ITEC2201).';
        }
        if ($data['name'] === '') {
            $errors['name'] = 'Tên môn học không được để trống.';
        }
        if ($data['credits'] < 1 || $data['credits'] > 10) {
            $errors['credits'] = 'Số tín chỉ phải từ 1 đến 10.';
        }

        if (!empty($errors)) {
            $this->json(['error' => 'Validation failed', 'fields' => $errors], 422);
        }

        // Check duplicate code
        $existing = $this->db->fetchOne(
            "SELECT id FROM courses WHERE code = ? LIMIT 1",
            [$data['code']]
        );
        if ($existing) {
            $this->json([
                'error'  => 'Mã môn học đã tồn tại.',
                'fields' => ['code' => 'Mã môn học đã tồn tại.'],
            ], 409);
        }

        $this->db->beginTransaction();
        try {
            $this->db->query(
                "INSERT INTO courses (program_id, code, name, credits, description) VALUES (?,?,?,?,?)",
                array_values($data)
            );
            $courseId = (int)$this->db->lastInsertId();

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

    public function updateCourse(array $params): void
    {
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $id   = (int)($params['id'] ?? 0);
        $body = $this->jsonBody();

        $data = [
            'program_id'  => (int)($body['program_id'] ?? 0),
            'code'        => strtoupper(trim($body['code'] ?? '')),
            'name'        => trim($body['name'] ?? ''),
            'credits'     => (int)($body['credits'] ?? 3),
            'description' => trim($body['description'] ?? ''),
        ];

        $errors = [];
        if ($data['program_id'] === 0) {
            $errors['program_id'] = 'Vui lòng chọn chương trình đào tạo.';
        }
        if ($data['code'] === '') {
            $errors['code'] = 'Mã môn học không được để trống.';
        } elseif (!preg_match('/^[A-Z]{2,6}[0-9]{2,4}[A-Z]?$/i', $data['code'])) {
            $errors['code'] = 'Mã môn học không hợp lệ (VD: ITEC2201).';
        }
        if ($data['name'] === '') {
            $errors['name'] = 'Tên môn học không được để trống.';
        }
        if ($data['credits'] < 1 || $data['credits'] > 10) {
            $errors['credits'] = 'Số tín chỉ phải từ 1 đến 10.';
        }

        if (!empty($errors)) {
            $this->json(['error' => 'Validation failed', 'fields' => $errors], 422);
        }

        $existing = $this->db->fetchOne(
            "SELECT id FROM courses WHERE code = ? AND id != ? LIMIT 1",
            [$data['code'], $id]
        );
        if ($existing) {
            $this->json([
                'error'  => 'Mã môn học đã tồn tại.',
                'fields' => ['code' => 'Mã môn học đã tồn tại.'],
            ], 409);
        }

        $this->db->query(
            "UPDATE courses SET program_id=?, code=?, name=?, credits=?, description=? WHERE id=?",
            [$data['program_id'], $data['code'], $data['name'], $data['credits'], $data['description'], $id]
        );
        $this->json(['status' => 'success']);
    }

    public function deleteCourse(array $params): void
    {
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $id = (int)($params['id'] ?? 0);

        $enrollmentCount = $this->db->fetchOne(
            "SELECT COUNT(*) as c FROM enrollments e JOIN course_assignments ca ON ca.id = e.assignment_id WHERE ca.course_id = ?",
            [$id]
        )['c'];

        if ($enrollmentCount > 0) {
            $this->json([
                'error' => "Không thể xóa: môn học đang có {$enrollmentCount} sinh viên đăng ký.",
            ], 409);
        }

        $this->db->query("DELETE FROM courses WHERE id = ?", [$id]);
        $this->json(['status' => 'success']);
    }

    public function storeAssignment(array $params): void
    {
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $body = $this->jsonBody();
        $data = [
            'course_id'   => (int)($body['course_id'] ?? 0),
            'lecturer_id' => (int)($body['lecturer_id'] ?? 0),
            'semester'    => trim($body['semester'] ?? ''),
        ];

        $errors = [];
        if ($data['course_id'] === 0)  $errors['course_id']   = 'Vui lòng chọn môn học.';
        if ($data['lecturer_id'] === 0) $errors['lecturer_id'] = 'Vui lòng chọn giảng viên.';
        if ($data['semester'] === '')   $errors['semester']    = 'Học kỳ không được để trống.';

        if (!empty($errors)) {
            $this->json(['error' => 'Validation failed', 'fields' => $errors], 422);
        }

        // Prevent duplicate assignment
        $existing = $this->db->fetchOne(
            "SELECT id FROM course_assignments WHERE course_id=? AND lecturer_id=? AND semester=? LIMIT 1",
            [$data['course_id'], $data['lecturer_id'], $data['semester']]
        );
        if ($existing) {
            $this->json(['error' => 'Phân công này đã tồn tại.', 'fields' => ['lecturer_id' => 'Phân công này đã tồn tại.']], 409);
        }

        $this->db->query(
            "INSERT INTO course_assignments (course_id, lecturer_id, semester) VALUES (?,?,?)",
            array_values($data)
        );
        $this->json(['status' => 'success', 'id' => $this->db->lastInsertId()]);
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
