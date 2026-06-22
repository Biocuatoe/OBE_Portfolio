<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/BaseModel.php';

/**
 * ScoreModel - Xử lý điểm số và thuật toán tính toán CLO/PLO attainment
 *
 * CORE ALGORITHM:
 *   student_score / rubric.max_score  → CLO achievement %
 *   CLO %  × clo_plo_mapping.weight  → PLO contribution
 *   Tổng weighted contribution        → PLO achieved %
 */
class ScoreModel extends BaseModel
{
    protected string $table = 'student_scores';

    // ── Lưu điểm một tiêu chí rubric ────────────────────────────────

    /**
     * Upsert điểm và tự động kích hoạt tính lại attainment.
     */
    public function saveScore(int $studentId, int $rubricId, float $score, ?string $feedback = null): void
    {
        require_once __DIR__ . '/../../core/ScoreService.php';
        $service = new ScoreService();
        $service->saveScoreAndRecalculate($studentId, $rubricId, $score, $feedback);
    }

    // ── Query helpers ────────────────────────────────────────────────


    /**
     * Lấy bảng điểm chi tiết cho giảng viên (Grading Sheet)
     */
    public function getGradingSheet(int $assessmentId): array
    {
        return $this->db->fetchAll(
            "SELECT
                u.id AS student_id,
                u.full_name,
                u.username,
                r.id AS rubric_id,
                r.criteria_name,
                r.max_score,
                r.order_index,
                cl.code AS clo_code,
                COALESCE(ss.score, NULL) AS score,
                ss.feedback
             FROM enrollments e
             JOIN course_assignments ca ON ca.id = e.assignment_id
             JOIN users u ON u.id = e.student_id
             JOIN rubrics r ON r.assessment_id = ?
             JOIN clos cl ON cl.id = r.clo_id
             LEFT JOIN student_scores ss ON ss.student_id = e.student_id AND ss.rubric_id = r.id
             WHERE ca.id = (SELECT assignment_id FROM assessments WHERE id = ?)
             ORDER BY u.full_name, r.order_index",
            [$assessmentId, $assessmentId]
        );
    }

    /**
     * Lấy dữ liệu PLO cho radar chart của sinh viên
     */
    public function getStudentPloData(int $studentId, int $programId): array
    {
        return $this->db->fetchAll(
            "SELECT
                p.code,
                p.description,
                p.category,
                COALESCE(pa.achieved_percentage, 0) AS achieved_percentage
             FROM plos p
             LEFT JOIN plo_attainments pa ON pa.plo_id = p.id AND pa.student_id = ?
             WHERE p.program_id = ?
             ORDER BY p.code",
            [$studentId, $programId]
        );
    }

    /**
     * Lấy CLO attainment của sinh viên (breakdown chart)
     */
    public function getStudentCloData(int $studentId, int $courseId): array
    {
        return $this->db->fetchAll(
            "SELECT
                c.code,
                c.description,
                c.bloom_level,
                COALESCE(ca.achieved_percentage, 0) AS achieved_percentage
             FROM clos c
             JOIN rubrics r ON r.clo_id = c.id
             JOIN assessments a ON a.id = r.assessment_id
             LEFT JOIN clo_attainments ca ON ca.clo_id = c.id AND ca.student_id = ?
             WHERE c.course_id = ?
               AND a.is_published = 1
             GROUP BY c.id, c.code, c.description, c.bloom_level
             ORDER BY c.code",
            [$studentId, $courseId]
        );
    }

    /**
     * Thống kê tổng quan cho Dashboard giảng viên
     */
    public function getAssessmentStats(int $assessmentId): array
    {
        return $this->db->fetchAll(
            "SELECT
                r.criteria_name,
                cl.code AS clo_code,
                r.max_score,
                COUNT(ss.id)               AS graded_count,
                ROUND(AVG(ss.score), 2)    AS avg_score,
                ROUND(MIN(ss.score), 2)    AS min_score,
                ROUND(MAX(ss.score), 2)    AS max_score_val,
                ROUND(AVG(ss.score / r.max_score * 100), 2) AS avg_pct
             FROM rubrics r
             JOIN clos cl ON cl.id = r.clo_id
             LEFT JOIN student_scores ss ON ss.rubric_id = r.id
             WHERE r.assessment_id = ?
             GROUP BY r.id
             ORDER BY r.order_index",
            [$assessmentId]
        );
    }
}
