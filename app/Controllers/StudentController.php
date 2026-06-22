<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/BaseController.php';
require_once __DIR__ . '/../Models/ScoreModel.php';

/**
 * StudentController - Handles Student Portal and E-Portfolio features
 */
class StudentController extends BaseController
{
    private ScoreModel $scoreModel;

    public function __construct()
    {
        parent::__construct();
        $this->scoreModel = new ScoreModel();
    }

    /**
     * GET /student/dashboard
     * E-Portfolio Dashboard với Radar Chart
     */
    public function dashboard(array $params): void
    {
        $this->requireAuth('student');
        $studentId = (int)$_SESSION['user_id'];

        // Lấy chương trình đào tạo của sinh viên
        $program = $this->db->fetchOne(
            "SELECT p.* FROM programs p
             JOIN courses c ON c.program_id = p.id
             JOIN course_assignments ca ON ca.course_id = c.id
             JOIN enrollments e ON e.assignment_id = ca.id
             WHERE e.student_id = ?
             LIMIT 1",
            [$studentId]
        );

        $programId = $program ? (int)$program['id'] : 1;

        // Dữ liệu PLO cho Radar Chart
        $ploData = $this->scoreModel->getStudentPloData($studentId, $programId);

        // Dữ liệu CLO theo từng môn
        $courses = $this->db->fetchAll(
            "SELECT DISTINCT c.id, c.code, c.name, c.credits
             FROM courses c
             JOIN course_assignments ca ON ca.course_id = c.id
             JOIN enrollments e ON e.assignment_id = ca.id
             WHERE e.student_id = ?",
            [$studentId]
        );

        $cloDataByCourse = [];
        foreach ($courses as $course) {
            $cloDataByCourse[$course['id']] = [
                'course' => $course,
                'clos'   => $this->scoreModel->getStudentCloData($studentId, (int)$course['id']),
            ];
        }

        // Tổng kết: điểm trung bình toàn phần
        $overallPct = count($ploData) > 0
            ? round(array_sum(array_column($ploData, 'achieved_percentage')) / count($ploData), 1)
            : 0;

        // Đếm PLO đạt (>= 70%)
        $achievedCount = count(array_filter($ploData, fn($p) => $p['achieved_percentage'] >= 70));

        // Assessments gần nhất
        $recentScores = $this->db->fetchAll(
            "SELECT a.title, a.type, r.criteria_name, r.max_score, ss.score, ss.graded_at, cl.code AS clo_code
             FROM student_scores ss
             JOIN rubrics r ON r.id = ss.rubric_id
             JOIN clos cl ON cl.id = r.clo_id
             JOIN assessments a ON a.id = r.assessment_id
             WHERE ss.student_id = ?
             ORDER BY ss.graded_at DESC
             LIMIT 10",
            [$studentId]
        );

        $this->view('student/dashboard', [
            'program'          => $program,
            'plo_data'         => $ploData,
            'clo_data_course'  => $cloDataByCourse,
            'overall_pct'      => $overallPct,
            'achieved_count'   => $achievedCount,
            'total_plo'        => count($ploData),
            'recent_scores'    => $recentScores,
            'student_name'     => $_SESSION['user_name'],
        ]);
    }

    /**
     * GET /student/portfolio/export
     * Xuất E-Portfolio ra PDF (dùng mPDF hoặc Dompdf)
     */
    public function exportPdf(array $params): void
    {
        $this->requireAuth('student');
        $studentId = (int)$_SESSION['user_id'];

        $ploData = $this->scoreModel->getStudentPloData($studentId, 1);
        $student = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$studentId]);

        // Render HTML template cho PDF
        ob_start();
        require __DIR__ . '/../Views/student/portfolio_pdf.php';
        $html = ob_get_clean();

        // Header để download
        header('Content-Type: text/html; charset=utf-8');
        // Trong production: dùng Dompdf/mPDF để convert
        // $dompdf = new Dompdf(); $dompdf->loadHtml($html); ...
        echo $html; // Fallback: in thẳng HTML để in qua browser
    }

    /**
     * GET /student/courses
     * Danh sách môn học và tiến độ
     */
    public function courses(array $params): void
    {
        $this->requireAuth('student');
        $studentId = (int)$_SESSION['user_id'];

        $courses = $this->db->fetchAll(
            "SELECT 
                c.id, c.code, c.name, c.credits,
                u.full_name AS lecturer_name,
                ca.semester,
                COUNT(DISTINCT r.id) AS total_rubrics,
                COUNT(DISTINCT ss.id) AS graded_rubrics
             FROM enrollments e
             JOIN course_assignments ca ON ca.id = e.assignment_id
             JOIN courses c ON c.id = ca.course_id
             JOIN users u ON u.id = ca.lecturer_id
             LEFT JOIN assessments a ON a.assignment_id = ca.id AND a.is_published = 1
             LEFT JOIN rubrics r ON r.assessment_id = a.id
             LEFT JOIN student_scores ss ON ss.rubric_id = r.id AND ss.student_id = e.student_id
             WHERE e.student_id = ?
             GROUP BY c.id, ca.id
             ORDER BY ca.semester DESC, c.name",
            [$studentId]
        );

        $this->view('student/courses', ['courses' => $courses]);
    }
}
