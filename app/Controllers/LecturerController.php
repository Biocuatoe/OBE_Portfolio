<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/BaseController.php';
require_once __DIR__ . '/../Models/ScoreModel.php';
require_once __DIR__ . '/../Models/AssessmentModel.php';

/**
 * LecturerController - Quản lý CLO, Assessment, Rubric
 */
class LecturerController extends BaseController
{
    private ScoreModel $scoreModel;

    public function __construct()
    {
        parent::__construct();
        $this->scoreModel = new ScoreModel();
    }

    // ── Dashboard ───────────────────────────────────────────────────
    public function dashboard(array $params): void
    {
        $this->requireAuth('lecturer', 'admin');
        $lecturerId = (int)$_SESSION['user_id'];

        // Môn học đang phụ trách
        $assignments = $this->db->fetchAll(
            "SELECT ca.*, c.code AS course_code, c.name AS course_name, c.credits,
                c.id AS course_id,
                COUNT(DISTINCT e.student_id)  AS student_count,
                COUNT(DISTINCT a.id)          AS assessment_count
             FROM course_assignments ca
             JOIN courses c ON c.id = ca.course_id
             LEFT JOIN enrollments e ON e.assignment_id = ca.id
             LEFT JOIN assessments a ON a.assignment_id = ca.id
             WHERE ca.lecturer_id = ? AND ca.is_active = 1
             GROUP BY ca.id
             ORDER BY ca.semester DESC",
            [$lecturerId]
        );

        // ── QUERY SỬA: Bài kiểm tra cần chấm điểm ──────────────────
        // Logic: một bài là "cần chấm" khi có ít nhất 1 sinh viên
        // chưa có đủ điểm ở tất cả rubric của bài đó
        $pendingGrading = $this->db->fetchAll(
            "SELECT
                a.id AS assessment_id,
                a.title,
                a.type,
                ca.id AS assignment_id,
                COUNT(DISTINCT e.student_id) AS total_students,
                -- Số SV đã có điểm ở TẤT CẢ rubric của bài này
                COUNT(DISTINCT CASE
                    WHEN graded_rubrics.rubric_count = total_rubrics.rubric_count
                    THEN e.student_id
                END) AS fully_graded_students
             FROM assessments a
             JOIN course_assignments ca ON ca.id = a.assignment_id
             JOIN enrollments e ON e.assignment_id = ca.id
             -- Đếm tổng số rubric của bài
             JOIN (
                 SELECT assessment_id, COUNT(*) AS rubric_count
                 FROM rubrics
                 GROUP BY assessment_id
             ) total_rubrics ON total_rubrics.assessment_id = a.id
             -- Đếm số rubric đã có điểm cho từng SV
             LEFT JOIN (
                 SELECT ss.student_id, r.assessment_id, COUNT(*) AS rubric_count
                 FROM student_scores ss
                 JOIN rubrics r ON r.id = ss.rubric_id
                 GROUP BY ss.student_id, r.assessment_id
             ) graded_rubrics ON graded_rubrics.student_id = e.student_id
                              AND graded_rubrics.assessment_id = a.id
             WHERE ca.lecturer_id = ?
               AND a.is_published = 1
             GROUP BY a.id
             HAVING fully_graded_students < total_students
             ORDER BY a.due_date ASC",
            [$lecturerId]
        );

        $this->view('lecturer/dashboard', [
            'pageTitle'       => 'Tổng quan giảng dạy',
            'assignments'     => $assignments,
            'pending_grading' => $pendingGrading,
        ]);
    }

    // ── CLO Management ───────────────────────────────────────────────
    public function clos(array $params): void
    {
        $this->requireAuth('lecturer', 'admin');

        $assignmentId = (int)($params['assignment_id'] ?? 0);
        $assignment   = $this->db->fetchOne(
            "SELECT ca.*, c.code, c.name, c.id AS course_id, c.program_id
             FROM course_assignments ca JOIN courses c ON c.id = ca.course_id
             WHERE ca.id = ?",
            [$assignmentId]
        );

        if (!$assignment) $this->redirect('/lecturer/dashboard');

        $clos = $this->db->fetchAll(
            "SELECT cl.*,
                COUNT(DISTINCT m.plo_id) AS mapped_plo_count
             FROM clos cl
             LEFT JOIN clo_plo_mappings m ON m.clo_id = cl.id
             WHERE cl.course_id = ?
             GROUP BY cl.id
             ORDER BY cl.code",
            [$assignment['course_id']]
        );

        $plos = $this->db->fetchAll(
            "SELECT * FROM plos WHERE program_id = ? ORDER BY code",
            [$assignment['program_id']]
        );

        $bloomLevels = [
            1 => 'Nhớ (Remember)',    2 => 'Hiểu (Understand)',
            3 => 'Áp dụng (Apply)',   4 => 'Phân tích (Analyze)',
            5 => 'Đánh giá (Evaluate)', 6 => 'Sáng tạo (Create)',
        ];

        $this->view('lecturer/clos', [
            'pageTitle'    => 'Chuẩn đầu ra môn - ' . $assignment['code'],
            'assignment'   => $assignment,
            'clos'         => $clos,
            'plos'         => $plos,
            'bloom_levels' => $bloomLevels,
            'csrf_token'   => $this->csrfToken(),
        ]);
    }

    public function storeClo(array $params): void
    {
        $this->requireAuth('lecturer', 'admin');
        $this->verifyCsrf();

        $body = $this->jsonBody();
        $data = [
            'course_id'   => (int)($body['course_id'] ?? 0),
            'code'        => strtoupper(trim($body['code'] ?? '')),
            'description' => trim($body['description'] ?? ''),
            'bloom_level' => isset($body['bloom_level']) ? (int)$body['bloom_level'] : null,
        ];

        if (!$data['course_id'] || !$data['code'] || !$data['description']) {
            $this->json(['error' => 'Thiếu thông tin bắt buộc.'], 422);
        }

        try {
            $this->db->query(
                "INSERT INTO clos (course_id, code, description, bloom_level) VALUES (?,?,?,?)",
                array_values($data)
            );
            $this->json(['status' => 'success', 'id' => $this->db->lastInsertId()]);
        } catch (\PDOException $e) {
            $this->json(['error' => 'Mã CLO đã tồn tại trong môn này.'], 409);
        }
    }

    public function deleteClo(array $params): void
    {
        $this->requireAuth('lecturer', 'admin');
        $this->verifyCsrf();

        $id   = (int)($params['id'] ?? 0);
        $used = $this->db->fetchOne("SELECT COUNT(*) as c FROM rubrics WHERE clo_id = ?", [$id]);
        if ($used['c'] > 0) {
            $this->json(['error' => 'Không thể xóa CLO đang được sử dụng trong Rubric.'], 409);
        }

        $this->db->query("DELETE FROM clos WHERE id = ?", [$id]);
        $this->json(['status' => 'success']);
    }

    // ── Assessments ──────────────────────────────────────────────────
    public function assessments(array $params): void
    {
        $this->requireAuth('lecturer', 'admin');

        $assignmentId = (int)($params['assignment_id'] ?? 0);
        $assignment   = $this->db->fetchOne(
            "SELECT ca.*, c.code, c.name, c.id AS course_id
             FROM course_assignments ca JOIN courses c ON c.id = ca.course_id
             WHERE ca.id = ?",
            [$assignmentId]
        );

        if (!$assignment) $this->redirect('/lecturer/dashboard');

        $model       = new AssessmentModel();
        $assessments = $model->getByAssignment($assignmentId);

        $this->view('lecturer/assessments', [
            'pageTitle'   => 'Bài kiểm tra - ' . $assignment['code'],
            'assignment'  => $assignment,
            'assessments' => $assessments,
            'csrf_token'  => $this->csrfToken(),
        ]);
    }

    public function storeAssessment(array $params): void
    {
        $this->requireAuth('lecturer', 'admin');
        $this->verifyCsrf();

        $body = $this->jsonBody();
        $data = [
            'assignment_id' => (int)($body['assignment_id'] ?? 0),
            'title'         => trim($body['title'] ?? ''),
            'type'          => in_array($body['type'] ?? '', ['quiz','assignment','midterm','final','project','lab'])
                               ? $body['type'] : 'assignment',
            'description'   => trim($body['description'] ?? ''),
            'weight'        => (float)($body['weight'] ?? 0),
            'due_date'      => !empty($body['due_date']) ? $body['due_date'] : null,
            'is_published'  => (int)($body['is_published'] ?? 0),
        ];

        if (!$data['assignment_id'] || !$data['title']) {
            $this->json(['error' => 'Thiếu thông tin bắt buộc.'], 422);
        }

        $this->db->query(
            "INSERT INTO assessments (assignment_id, title, type, description, weight, due_date, is_published)
             VALUES (?,?,?,?,?,?,?)",
            array_values($data)
        );

        $this->json(['status' => 'success', 'id' => $this->db->lastInsertId()]);
    }

    // ── Rubrics ──────────────────────────────────────────────────────
    public function rubrics(array $params): void
    {
        $this->requireAuth('lecturer', 'admin');

        $assessmentId = (int)($params['assessment_id'] ?? 0);
        $assessment   = $this->db->fetchOne(
            "SELECT a.*, c.code AS course_code, c.id AS course_id
             FROM assessments a
             JOIN course_assignments ca ON ca.id = a.assignment_id
             JOIN courses c ON c.id = ca.course_id
             WHERE a.id = ?",
            [$assessmentId]
        );

        if (!$assessment) $this->redirect('/lecturer/dashboard');

        $rubrics = $this->db->fetchAll(
            "SELECT r.*, cl.code AS clo_code, cl.description AS clo_desc
             FROM rubrics r JOIN clos cl ON cl.id = r.clo_id
             WHERE r.assessment_id = ?
             ORDER BY r.order_index",
            [$assessmentId]
        );

        $clos     = $this->db->fetchAll(
            "SELECT * FROM clos WHERE course_id = ? ORDER BY code",
            [$assessment['course_id']]
        );
        $totalMax = array_sum(array_column($rubrics, 'max_score'));

        $this->view('lecturer/rubrics', [
            'pageTitle'   => 'Rubric - ' . $assessment['title'],
            'assessment'  => $assessment,
            'rubrics'     => $rubrics,
            'clos'        => $clos,
            'total_max'   => $totalMax,
            'csrf_token'  => $this->csrfToken(),
        ]);
    }

    public function storeRubric(array $params): void
    {
        $this->requireAuth('lecturer', 'admin');
        $this->verifyCsrf();

        $body = $this->jsonBody();
        $data = [
            'assessment_id' => (int)($body['assessment_id'] ?? 0),
            'clo_id'        => (int)($body['clo_id'] ?? 0),
            'criteria_name' => trim($body['criteria_name'] ?? ''),
            'max_score'     => (float)($body['max_score'] ?? 10),
            'description'   => trim($body['description'] ?? ''),
            'order_index'   => (int)($body['order_index'] ?? 0),
        ];

        if (!$data['assessment_id'] || !$data['clo_id'] || !$data['criteria_name']) {
            $this->json(['error' => 'Thiếu thông tin bắt buộc.'], 422);
        }

        $this->db->query(
            "INSERT INTO rubrics (assessment_id, clo_id, criteria_name, max_score, description, order_index)
             VALUES (?,?,?,?,?,?)",
            array_values($data)
        );

        $this->json(['status' => 'success', 'id' => $this->db->lastInsertId()]);
    }

    public function deleteRubric(array $params): void
    {
        $this->requireAuth('lecturer', 'admin');
        $this->verifyCsrf();

        $id   = (int)($params['id'] ?? 0);
        $used = $this->db->fetchOne("SELECT COUNT(*) as c FROM student_scores WHERE rubric_id = ?", [$id]);

        if ($used['c'] > 0) {
            $this->json(['error' => 'Không thể xóa tiêu chí đã có điểm của sinh viên.'], 409);
        }

        $this->db->query("DELETE FROM rubrics WHERE id = ?", [$id]);
        $this->json(['status' => 'success']);
    }

    // ── API: Assessment Stats ────────────────────────────────────────
    public function apiAssessmentStats(array $params): void
    {
        $this->requireAuth('lecturer', 'admin');

        $assessmentId = (int)($params['id'] ?? 0);
        $scoreModel   = new ScoreModel();
        $stats        = $scoreModel->getAssessmentStats($assessmentId);

        $distribution = $this->db->fetchAll(
            "SELECT
                CASE
                    WHEN (ss.score / r.max_score * 100) >= 90 THEN 'A (90-100%)'
                    WHEN (ss.score / r.max_score * 100) >= 80 THEN 'B (80-89%)'
                    WHEN (ss.score / r.max_score * 100) >= 70 THEN 'C (70-79%)'
                    WHEN (ss.score / r.max_score * 100) >= 60 THEN 'D (60-69%)'
                    ELSE 'F (<60%)'
                END AS grade_band,
                COUNT(*) AS count
             FROM student_scores ss
             JOIN rubrics r ON r.id = ss.rubric_id
             WHERE r.assessment_id = ?
             GROUP BY grade_band
             ORDER BY grade_band",
            [$assessmentId]
        );

        $this->json(['status' => 'success', 'stats' => $stats, 'distribution' => $distribution]);
    }
}