<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/BaseController.php';
require_once __DIR__ . '/../Models/ScoreModel.php';
require_once __DIR__ . '/../Models/AssessmentModel.php';

/**
 * ScoreController - Xử lý chấm điểm và API endpoints
 */
class ScoreController extends BaseController
{
    private ScoreModel $scoreModel;

    public function __construct()
    {
        parent::__construct();
        $this->scoreModel = new ScoreModel();
    }

    // ── Giao diện chấm điểm (Giảng viên) ───────────────────────────

    /**
     * GET /lecturer/assessment/:id/grade
     * Hiển thị bảng chấm điểm live
     */
    public function gradingSheet(array $params): void
    {
        $this->requireAuth('lecturer', 'admin');

        $assessmentId = (int)($params['id'] ?? 0);

        $assessmentModel = new AssessmentModel();
        $assessment      = $assessmentModel->findById($assessmentId);

        if (!$assessment) {
            $this->redirect('/lecturer/assessments');
        }

        // Kiểm tra quyền: chỉ giảng viên sở hữu assessment mới được chấm
        if ($_SESSION['user_role'] === 'lecturer') {
            $owns = $this->db->fetchOne(
                "SELECT 1 FROM assessments a
                 JOIN course_assignments ca ON ca.id = a.assignment_id
                 WHERE a.id = ? AND ca.lecturer_id = ?",
                [$assessmentId, $_SESSION['user_id']]
            );
            if (!$owns) {
                http_response_code(403);
                require __DIR__ . '/../Views/errors/403.php';
                return;
            }
        }

        $gradingData = $this->scoreModel->getGradingSheet($assessmentId);
        $stats       = $this->scoreModel->getAssessmentStats($assessmentId);

        // Pivot data: [student_id => [rubric_id => score_data]]
        $students = [];
        $rubrics  = [];

        foreach ($gradingData as $row) {
            $sid = $row['student_id'];
            $rid = $row['rubric_id'];

            if (!isset($students[$sid])) {
                $students[$sid] = [
                    'id'        => $sid,
                    'full_name' => $row['full_name'],
                    'username'  => $row['username'],
                    'scores'    => [],
                ];
            }

            $students[$sid]['scores'][$rid] = [
                'score'    => $row['score'],
                'feedback' => $row['feedback'],
            ];

            if (!isset($rubrics[$rid])) {
                $rubrics[$rid] = [
                    'id'            => $rid,
                    'criteria_name' => $row['criteria_name'],
                    'max_score'     => $row['max_score'],
                    'clo_code'      => $row['clo_code'],
                    'order_index'   => $row['order_index'],
                ];
            }
        }

        $this->view('lecturer/grading', [
            'assessment' => $assessment,
            'students'   => $students,
            'rubrics'    => $rubrics,
            'stats'      => $stats,
            'csrf_token' => $this->csrfToken(),
        ]);
    }

    // ── API Endpoints (AJAX) ────────────────────────────────────────

    /**
     * POST /api/score/save
     * Body JSON: { student_id, rubric_id, score, feedback?, _token }
     */
    public function apiSave(array $params): void
    {
        $this->requireAuth('lecturer', 'admin');
        $this->verifyCsrf();

        $body      = $this->jsonBody();
        $studentId = (int)($body['student_id'] ?? 0);
        $rubricId  = (int)($body['rubric_id']  ?? 0);
        $score     = (float)($body['score']     ?? -1);
        $feedback  = isset($body['feedback']) ? trim($body['feedback']) : null;

        if (!$studentId || !$rubricId || $score < 0) {
            $this->json(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.'], 422);
        }

        try {
            $this->scoreModel->saveScore($studentId, $rubricId, $score, $feedback);

            // Lấy lại PLO mới nhất để trả về realtime update
            $plos = $this->scoreModel->getStudentPloData(
                $studentId,
                (int)$this->db->fetchOne("SELECT program_id FROM programs p JOIN courses c ON c.program_id=p.id JOIN clos cl ON cl.course_id=c.id JOIN rubrics r ON r.clo_id=cl.id WHERE r.id=?", [$rubricId])['program_id']
            );

            $this->json([
                'status'  => 'success',
                'message' => 'Đã lưu điểm thành công!',
                'plo_data' => $plos,
            ]);
        } catch (\RangeException $e) {
            $this->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            error_log('[SCORE ERROR] ' . $e->getMessage());
            $this->json(['status' => 'error', 'message' => 'Lỗi hệ thống, vui lòng thử lại.'], 500);
        }
    }

    /**
     * GET /api/score/student/:id/plo
     * Lấy PLO data cho radar chart realtime
     */
    public function apiStudentPlo(array $params): void
    {
        $this->requireAuth();

        $studentId = (int)($params['id'] ?? 0);

        // Sinh viên chỉ xem được của mình
        if ($_SESSION['user_role'] === 'student' && $studentId !== (int)$_SESSION['user_id']) {
            $this->json(['error' => 'Không có quyền truy cập.'], 403);
        }

        $programId = (int)$this->db->fetchOne(
            "SELECT p.id FROM programs p
             JOIN courses c ON c.program_id = p.id
             JOIN course_assignments ca ON ca.course_id = c.id
             JOIN enrollments e ON e.assignment_id = ca.id
             WHERE e.student_id = ?
             LIMIT 1",
            [$studentId]
        )['id'];

        $plos = $this->scoreModel->getStudentPloData($studentId, $programId ?: 1);

        $this->json([
            'status' => 'success',
            'data'   => $plos,
        ]);
    }
}
