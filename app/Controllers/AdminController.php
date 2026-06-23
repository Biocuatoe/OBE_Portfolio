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

        $search   = trim($_GET['search'] ?? '');
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $sortCol  = $_GET['sort'] ?? 'code';
        $sortDir  = strtoupper($_GET['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        $perPage  = 20;
        $offset   = ($page - 1) * $perPage;

        $validSorts = ['code','name','created_at','course_count','plo_count','assignment_count','student_count'];
        if (!in_array($sortCol, $validSorts, true)) $sortCol = 'code';

        $where = [];
        $args  = [];

        if ($search !== '') {
            $where[] = "(p.code LIKE ? OR p.name LIKE ?)";
            $args[]  = "%{$search}%";
            $args[]  = "%{$search}%";
        }

        $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "WHERE 1=1";

        $total = (int)$this->db->fetchOne(
            "SELECT COUNT(*) as c FROM programs p {$whereClause}",
            $args
        )['c'];
        $totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;

        $programs = $this->db->fetchAll(
            "SELECT p.*,
                (SELECT COUNT(*) FROM courses c WHERE c.program_id = p.id) AS course_count,
                (SELECT COUNT(*) FROM plos pl WHERE pl.program_id = p.id) AS plo_count,
                (SELECT COUNT(DISTINCT ca.lecturer_id) FROM course_assignments ca JOIN courses c ON c.id = ca.course_id WHERE c.program_id = p.id) AS assignment_count,
                (SELECT COUNT(DISTINCT e.student_id) FROM enrollments e JOIN course_assignments ca ON ca.id = e.assignment_id JOIN courses c ON c.id = ca.course_id WHERE c.program_id = p.id) AS student_count,
                COALESCE(a.full_name, '—') AS admin_name
             FROM programs p
             LEFT JOIN users a ON a.id = p.admin_id
             {$whereClause}
             ORDER BY {$sortCol} {$sortDir}
             LIMIT {$perPage} OFFSET {$offset}",
            $args
        );

        $this->view('admin/programs', [
            'pageTitle'    => 'Chương trình đào tạo',
            'programs'     => $programs,
            'search_query' => $search,
            'csrf_token'   => $this->csrfToken(),
            'total_pages'  => $totalPages,
            'current_page' => $page,
            'sort_col'     => $sortCol,
            'sort_dir'     => $sortDir,
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
            "UPDATE programs SET code=?, name=?, description=?, updated_at=NOW() WHERE id=?",
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

        $id = (int)($params['id'] ?? $params['program_id'] ?? 0);
        if ($id <= 0) {
            header('Location: /admin/programs');
            exit;
        }

        $program = $this->db->fetchOne("SELECT name, code FROM programs WHERE id = ?", [$id]);
        if (!$program) {
            header('Location: /admin/programs');
            exit;
        }

        $plos = $this->db->fetchAll("SELECT id, code, description, category FROM plos WHERE program_id = ? ORDER BY code ASC", [$id]);

        $this->view('admin/plos', [
            'pageTitle'    => 'Chuẩn đầu ra (PLO) - ' . $program['code'],
            'program_name' => $program['name'],
            'program_code' => $program['code'],
            'program_id'   => $id,
            'plos'        => $plos,
            'csrf_token'  => $this->csrfToken(),
        ]);
    }

    public function storePlo(array $params): void
    {
        $this->requireAuth('admin');

        // Accept both JSON body (API) and form POST
        $body = $this->jsonBody();
        $errors = [];

        $code        = trim($body['code'] ?? '');
        $category    = trim(ucfirst(strtolower($body['category'] ?? '')));
        $description = trim($body['description'] ?? '');
        $programId   = (int)($params['id'] ?? 0);

        if (!$code) {
            $errors['code'] = 'Mã PLO không được để trống.';
        } elseif (!preg_match('/^[A-Z0-9\-]{2,20}$/', $code)) {
            $errors['code'] = 'Mã PLO gồm chữ hoa, số, gạch ngang (2-20 ký tự).';
        }
        $allowedCategories = ['Knowledge', 'Skill', 'Attitude'];
        if ($category === '' || !in_array($category, $allowedCategories, true)) {
            $errors['category'] = 'Danh mục không hợp lệ. Chọn Knowledge, Skill hoặc Attitude.';
        }
        if (!$description) {
            $errors['description'] = 'Mô tả không được để trống.';
        }
        if ($programId <= 0) {
            $errors['program_id'] = 'Chương trình không hợp lệ.';
        }

        // Check duplicate code within the same program
        if ($code && $programId > 0) {
            $existing = $this->db->fetchOne(
                "SELECT id FROM plos WHERE program_id = ? AND code = ? LIMIT 1",
                [$programId, $code]
            );
            if ($existing) {
                $errors['code'] = 'Mã PLO đã tồn tại trong chương trình này.';
            }
        }

        if (!empty($errors)) {
            $this->json(['error' => 'Validation failed', 'fields' => $errors], 422);
            return;
        }

        $this->db->query(
            "INSERT INTO plos (program_id, code, description, category) VALUES (?,?,?,?)",
            [$programId, $code, $description, $category]
        );
        $this->json(['status' => 'success', 'id' => $this->db->lastInsertId()]);
    }

    public function updatePlo(array $params): void
    {
        $this->requireAuth('admin');

        $id   = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['error' => 'ID không hợp lệ.'], 400);
            return;
        }

        $body = $this->jsonBody();
        $errors = [];

        $code        = trim($body['code'] ?? '');
        $category    = trim(ucfirst(strtolower($body['category'] ?? '')));
        $description = trim($body['description'] ?? '');

        if (!$code) {
            $errors['code'] = 'Mã PLO không được để trống.';
        } elseif (!preg_match('/^[A-Z0-9\-]{2,20}$/', $code)) {
            $errors['code'] = 'Mã PLO gồm chữ hoa, số, gạch ngang (2-20 ký tự).';
        }
        $allowedCategories = ['Knowledge', 'Skill', 'Attitude'];
        if ($category === '' || !in_array($category, $allowedCategories, true)) {
            $errors['category'] = 'Danh mục không hợp lệ. Chọn Knowledge, Skill hoặc Attitude.';
        }
        if (!$description) {
            $errors['description'] = 'Mô tả không được để trống.';
        }

        if (!empty($errors)) {
            $this->json(['error' => 'Validation failed', 'fields' => $errors], 422);
            return;
        }

        // Get the PLO's program_id for duplicate check
        $existingPlo = $this->db->fetchOne("SELECT program_id FROM plos WHERE id = ?", [$id]);
        if (!$existingPlo) {
            $this->json(['error' => 'PLO không tồn tại.'], 404);
            return;
        }

        // Check duplicate code (excluding current record)
        $duplicate = $this->db->fetchOne(
            "SELECT id FROM plos WHERE program_id = ? AND code = ? AND id != ? LIMIT 1",
            [$existingPlo['program_id'], $code, $id]
        );
        if ($duplicate) {
            $this->json([
                'error'  => 'Mã PLO đã tồn tại trong chương trình này.',
                'fields' => ['code' => 'Mã PLO đã tồn tại trong chương trình này.'],
            ], 409);
            return;
        }

        $this->db->query(
            "UPDATE plos SET code = ?, description = ?, category = ?, updated_at = NOW() WHERE id = ?",
            [$code, $description, $category, $id]
        );
        $this->json(['status' => 'success']);
    }

    public function deletePlo(array $params): void
    {
        $this->requireAuth('admin');

        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['error' => 'ID không hợp lệ.'], 400);
            return;
        }

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
        } else {
            // Verify program exists
            $programExists = $this->db->fetchOne("SELECT id FROM programs WHERE id = ?", [$data['program_id']]);
            if (!$programExists) {
                $errors['program_id'] = 'Chương trình đào tạo không tồn tại.';
            }
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
                // Verify lecturer role
                $lecturer = $this->db->fetchOne(
                    "SELECT id FROM users WHERE id = ? AND role = 'lecturer' LIMIT 1",
                    [(int)$body['lecturer_id']]
                );
                if (!$lecturer) {
                    $this->db->rollBack();
                    $this->json(['error' => 'Người dùng không phải giảng viên.', 'fields' => ['lecturer_id' => 'Người dùng không phải giảng viên.']], 422);
                }

                // Check for duplicate assignment
                $existingAssignment = $this->db->fetchOne(
                    "SELECT id FROM course_assignments WHERE course_id = ? AND lecturer_id = ? AND semester = ? LIMIT 1",
                    [$courseId, (int)$body['lecturer_id'], trim($body['semester'])]
                );
                if ($existingAssignment) {
                    $this->db->rollBack();
                    $this->json(['error' => 'Phân công này đã tồn tại.', 'fields' => ['semester' => 'Phân công này đã tồn tại cho học kỳ này.']], 409);
                }

                $this->db->query(
                    "INSERT INTO course_assignments (course_id, lecturer_id, semester) VALUES (?,?,?)",
                    [$courseId, (int)$body['lecturer_id'], trim($body['semester'])]
                );
            }

            $this->db->commit();
            $this->json(['status' => 'success', 'id' => $courseId]);
        } catch (\PDOException $e) {
            $this->db->rollBack();
            if (str_contains($e->getMessage(), 'Duplicate')) {
                $this->json(['error' => 'Mã môn học đã tồn tại.', 'fields' => ['code' => 'Mã môn học đã tồn tại.']], 409);
            }
            $this->json(['error' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()], 500);
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

        if ($weight < 0 || $weight > 100) {
            $this->json(['error' => 'Trọng số phải từ 0 đến 100.'], 422);
        }

        // FK validation: CLO must exist and belong to a valid course
        $clo = $this->db->fetchOne("SELECT id FROM clos WHERE id = ?", [$cloId]);
        if (!$clo) {
            $this->json(['error' => 'CLO không tồn tại.'], 404);
        }

        // FK validation: PLO must exist
        $plo = $this->db->fetchOne("SELECT id FROM plos WHERE id = ?", [$ploId]);
        if (!$plo) {
            $this->json(['error' => 'PLO không tồn tại.'], 404);
        }

        // Weight sum validation: sum of weights for this CLO row must not exceed 100%
        $rowMappings = $this->db->fetchAll(
            "SELECT plo_id, weight FROM clo_plo_mappings WHERE clo_id = ? AND plo_id != ?",
            [$cloId, $ploId]
        );
        $currentRowSum = 0;
        foreach ($rowMappings as $m) {
            $currentRowSum += (float)$m['weight'];
        }
        if ($currentRowSum + $weight > 100) {
            $this->json([
                'error' => "Tổng trọng số cho CLO này vượt quá 100% (hiện tại: {$currentRowSum}%, thêm: {$weight}%).",
            ], 422);
        }

        if ($weight === 0.0) {
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

        $role   = $_GET['role'] ?? '';
        $search = trim($_GET['search'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        // Base query parts
        $where = [];
        $args  = [];

        if ($role !== '') {
            $where[] = "u.role = ?";
            $args[]  = $role;
        }

        if ($search !== '') {
            $where[] = "(u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?)";
            $args[]  = "%{$search}%";
            $args[]  = "%{$search}%";
            $args[]  = "%{$search}%";
        }

        $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "WHERE 1=1";

        // Total count
        $total = (int)$this->db->fetchOne(
            "SELECT COUNT(*) as c FROM users u {$whereClause}",
            $args
        )['c'];

        $totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;

        $sortCol  = $_GET['sort'] ?? 'full_name';
        $sortDir  = strtoupper($_GET['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        $validSorts = ['username','email','full_name','role','is_active','created_at'];
        if (!in_array($sortCol, $validSorts, true)) $sortCol = 'full_name';

        // Paginated results
        $users = $this->db->fetchAll(
            "SELECT id, username, email, full_name, role, is_active, created_at
             FROM users u
             {$whereClause}
             ORDER BY {$sortCol} {$sortDir}
             LIMIT {$perPage} OFFSET {$offset}",
            $args
        );

        // Role counts for filter pills
        $roleCounts = $this->db->fetchAll(
            "SELECT role, COUNT(*) as c FROM users GROUP BY role"
        );
        $roleCountsMap = ['admin' => 0, 'lecturer' => 0, 'student' => 0];
        foreach ($roleCounts as $rc) {
            $roleCountsMap[$rc['role']] = (int)$rc['c'];
        }

        $this->view('admin/users', [
            'pageTitle'    => 'Quản lý người dùng',
            'users'        => $users,
            'filter_role'  => $role,
            'search_query' => $search,
            'csrf_token'   => $this->csrfToken(),
            'total_pages'  => $totalPages,
            'current_page' => $page,
            'role_counts'  => $roleCountsMap,
            'sort_col'     => $sortCol,
            'sort_dir'     => $sortDir,
        ]);
    }

    public function updateUser(array $params): void
    {
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $id   = (int)($params['id'] ?? 0);
        $body = $this->jsonBody();

        $username  = trim($body['username'] ?? '');
        $email     = trim($body['email'] ?? '');
        $fullName  = trim($body['full_name'] ?? '');
        $role      = $body['role'] ?? '';
        $password  = trim($body['password'] ?? '');

        // Validation
        $errors = [];
        if ($username === '') {
            $errors['username'] = 'Username không được để trống.';
        } elseif (strlen($username) > 50) {
            $errors['username'] = 'Username tối đa 50 ký tự.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ.';
        }

        if ($fullName === '') {
            $errors['full_name'] = 'Họ tên không được để trống.';
        } elseif (strlen($fullName) > 100) {
            $errors['full_name'] = 'Họ tên tối đa 100 ký tự.';
        }

        if (!in_array($role, ['admin', 'lecturer', 'student'], true)) {
            $errors['role'] = 'Vai trò không hợp lệ.';
        }

        if ($password !== '' && strlen($password) < 8) {
            $errors['password'] = 'Mật khẩu phải có ít nhất 8 ký tự.';
        }

        if (!empty($errors)) {
            $this->json(['error' => 'Validation failed', 'fields' => $errors], 422);
        }

        // Duplicate checks
        $existingUser = $this->db->fetchOne(
            "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ? LIMIT 1",
            [$username, $email, $id]
        );
        if ($existingUser) {
            $this->json([
                'error'  => 'Username hoặc email đã tồn tại.',
                'fields' => ['username' => 'Username hoặc email đã tồn tại.'],
            ], 409);
        }

        if ($password !== '') {
            $this->db->query(
                "UPDATE users SET username=?, email=?, full_name=?, role=?, password=? WHERE id=?",
                [$username, $email, $fullName, $role, password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $id]
            );
        } else {
            $this->db->query(
                "UPDATE users SET username=?, email=?, full_name=?, role=? WHERE id=?",
                [$username, $email, $fullName, $role, $id]
            );
        }

        $this->db->logActivity($_SESSION['user_id'], 'Update user', 'user');
        $this->json(['status' => 'success']);
    }

    public function toggleUserStatus(array $params): void
    {
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $id = (int)($params['id'] ?? 0);

        // Cannot toggle own account
        if ($id === (int)($_SESSION['user_id'] ?? 0)) {
            $this->json(['error' => 'Không thể thay đổi trạng thái tài khoản của chính bạn.'], 409);
        }

        $user = $this->db->fetchOne("SELECT is_active, full_name FROM users WHERE id = ?", [$id]);
        if (!$user) {
            $this->json(['error' => 'Người dùng không tồn tại.'], 404);
        }

        $newValue = $user['is_active'] ? 0 : 1;
        $action   = $newValue ? 'Activate user' : 'Deactivate user';

        $this->db->query("UPDATE users SET is_active = ? WHERE id = ?", [$newValue, $id]);
        $this->db->logActivity($_SESSION['user_id'], $action, 'user');

        $this->json(['status' => 'success', 'is_active' => $newValue]);
    }

    public function activityLogs(array $params): void
    {
        $this->requireAuth('admin');

        $role       = $_GET['role']       ?? '';
        $action     = trim($_GET['action'] ?? '');
        $dateFrom   = $_GET['date_from']  ?? '';
        $dateTo     = $_GET['date_to']    ?? '';
        $userId     = trim($_GET['user_id'] ?? '');
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $perPage    = 30;
        $offset     = ($page - 1) * $perPage;

        $where = [];
        $args  = [];

        if ($role !== '') {
            $where[] = "u.role = ?";
            $args[]  = $role;
        }
        if ($action !== '') {
            $where[] = "al.action LIKE ?";
            $args[]  = "%{$action}%";
        }
        if ($dateFrom !== '') {
            $where[] = "DATE(al.created_at) >= ?";
            $args[]  = $dateFrom;
        }
        if ($dateTo !== '') {
            $where[] = "DATE(al.created_at) <= ?";
            $args[]  = $dateTo;
        }
        if ($userId !== '') {
            $where[] = "al.user_id = ?";
            $args[]  = (int)$userId;
        }

        $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "WHERE 1=1";

        $total = (int)$this->db->fetchOne(
            "SELECT COUNT(*) as c FROM activity_logs al JOIN users u ON u.id = al.user_id {$whereClause}",
            $args
        )['c'];

        $totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;

        $logs = $this->db->fetchAll(
            "SELECT al.id, al.user_id, al.action, al.entity, al.ip_address, al.created_at,
                    u.full_name, u.role, u.username
             FROM activity_logs al
             JOIN users u ON u.id = al.user_id
             {$whereClause}
             ORDER BY al.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $args
        );

        // Users list for dropdown
        $usersList = $this->db->fetchAll(
            "SELECT id, full_name, role FROM users ORDER BY full_name"
        );

        // Distinct action types
        $distinctActions = $this->db->fetchAll(
            "SELECT DISTINCT action FROM activity_logs ORDER BY action"
        );

        $this->view('admin/activity_logs', [
            'pageTitle'       => 'Nhật ký hoạt động',
            'logs'            => $logs,
            'total_pages'     => $totalPages,
            'current_page'    => $page,
            'filter_role'     => $role,
            'filter_action'   => $action,
            'filter_date_from'=> $dateFrom,
            'filter_date_to'  => $dateTo,
            'filter_user_id'  => $userId,
            'users_list'      => $usersList,
            'distinct_actions'=> array_column($distinctActions, 'action'),
            'csrf_token'      => $this->csrfToken(),
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
        $this->requireAuth('admin', 'lecturer');

        $programId = (int)($_GET['program_id'] ?? ($params['program_id'] ?? 0));

        // Fetch all programs
        $programs = $this->db->fetchAll(
            "SELECT id, code, name FROM programs ORDER BY name"
        );

        // Default to first program if none selected
        if ($programId === 0 && !empty($programs)) {
            $programId = (int)$programs[0]['id'];
        }

        if ($programId === 0) {
            $this->view('admin/report_attainment', [
                'pageTitle'           => 'Báo cáo đạt chuẩn PLO',
                'programs'            => $programs,
                'selected_program_id' => 0,
                'plo_report'          => [],
                'top_students'        => [],
                'csrf_token'          => $this->csrfToken(),
            ]);
            return;
        }

        // Fetch PLOs with attainment metrics
        $plos = $this->db->fetchAll(
            "SELECT p.id, p.code, p.description, p.category
             FROM plos p
             WHERE p.program_id = ?
             ORDER BY p.code",
            [$programId]
        );

        $ploReport = [];
        foreach ($plos as $plo) {
            // Measured students: distinct students with CLO attainment linked to this PLO
            $measured = $this->db->fetchOne(
                "SELECT COUNT(DISTINCT ca.student_id) AS c
                 FROM clo_attainment ca
                 JOIN clos c ON c.id = ca.clo_id
                 JOIN clo_plo_mappings cp ON cp.clo_id = c.id
                 WHERE cp.plo_id = ?",
                [$plo['id']]
            );
            $measuredStudents = (int)($measured['c'] ?? 0);

            // Average attainment from clo_attainment
            $avgRow = $this->db->fetchOne(
                "SELECT ROUND(AVG(ca.achieved_percentage), 1) AS avg_pct
                 FROM clo_attainment ca
                 JOIN clos c ON c.id = ca.clo_id
                 JOIN clo_plo_mappings cp ON cp.clo_id = c.id
                 WHERE cp.plo_id = ?",
                [$plo['id']]
            );
            $avgAttainment = (float)($avgRow['avg_pct'] ?? 0);

            // Students passed (achieved_percentage >= 70)
            $passedRow = $this->db->fetchOne(
                "SELECT COUNT(DISTINCT ca.student_id) AS c
                 FROM clo_attainment ca
                 JOIN clos c ON c.id = ca.clo_id
                 JOIN clo_plo_mappings cp ON cp.clo_id = c.id
                 WHERE cp.plo_id = ? AND ca.achieved_percentage >= 70",
                [$plo['id']]
            );
            $studentsPassed = (int)($passedRow['c'] ?? 0);

            // Pass rate
            $passRate = $measuredStudents > 0
                ? round($studentsPassed / $measuredStudents * 100, 1)
                : 0;

            // CLO codes mapped to this PLO
            $clos = $this->db->fetchAll(
                "SELECT c.code
                 FROM clos c
                 JOIN clo_plo_mappings cp ON cp.clo_id = c.id
                 WHERE cp.plo_id = ?
                 ORDER BY c.code",
                [$plo['id']]
            );
            $cloCodes = implode(', ', array_column($clos, 'code'));

            $ploReport[] = [
                'id'                => $plo['id'],
                'code'              => $plo['code'],
                'description'       => $plo['description'],
                'category'          => $plo['category'],
                'measured_students' => $measuredStudents,
                'avg_attainment'    => $avgAttainment,
                'students_passed'   => $studentsPassed,
                'pass_rate'         => $passRate,
                'clo_codes'         => $cloCodes,
            ];
        }

        // Top 10 students by overall PLO attainment
        $topStudents = $this->db->fetchAll(
            "SELECT u.id, u.full_name, u.username,
                ROUND(AVG(ca.achieved_percentage), 1) AS overall_pct,
                COUNT(DISTINCT cp.plo_id) AS plos_measured
             FROM users u
             JOIN enrollments e ON e.student_id = u.id
             JOIN course_assignments ca2 ON ca2.id = e.assignment_id
             JOIN courses c ON c.id = ca2.course_id
             JOIN clo_attainment ca ON ca.student_id = u.id
             JOIN clos cl ON cl.id = ca.clo_id
             JOIN clo_plo_mappings cp ON cp.clo_id = cl.id
             JOIN plos pl ON pl.id = cp.plo_id AND pl.program_id = ?
             WHERE c.program_id = ?
             GROUP BY u.id
             ORDER BY overall_pct DESC
             LIMIT 10",
            [$programId, $programId]
        );

        $this->view('admin/report_attainment', [
            'pageTitle'           => 'Báo cáo đạt chuẩn PLO',
            'programs'            => $programs,
            'selected_program_id' => $programId,
            'plo_report'          => $ploReport,
            'top_students'        => $topStudents,
            'csrf_token'          => $this->csrfToken(),
        ]);
    }
}
