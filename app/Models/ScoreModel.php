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
     * Upsert điểm và tự động kích hoạt tính lại attainment
     *
     * @throws RuntimeException nếu score vượt max_score
     */
    public function saveScore(int $studentId, int $rubricId, float $score, ?string $feedback = null): void
    {
        // Kiểm tra max_score
        $rubric = $this->db->fetchOne(
            "SELECT r.*, c.course_id FROM rubrics r JOIN clos c ON c.id = r.clo_id WHERE r.id = ?",
            [$rubricId]
        );

        if (!$rubric) {
            throw new \InvalidArgumentException("Rubric ID {$rubricId} không tồn tại.");
        }

        if ($score < 0 || $score > (float)$rubric['max_score']) {
            throw new \RangeException("Điểm {$score} vượt quá điểm tối đa {$rubric['max_score']}.");
        }

        $this->db->beginTransaction();
        try {
            // Upsert student_scores
            $this->db->query(
                "INSERT INTO student_scores (student_id, rubric_id, score, feedback)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE score = VALUES(score), feedback = VALUES(feedback)",
                [$studentId, $rubricId, $score, $feedback]
            );

            // Tính lại CLO attainment cho môn này
            $this->recalculateCloAttainment($studentId, (int)$rubric['course_id']);

            // Tính lại PLO attainment dựa trên CLO mới
            $this->recalculatePloAttainment($studentId, (int)$rubric['course_id']);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ── THUẬT TOÁN 1: Tính CLO Attainment ───────────────────────────

    /**
     * Tính % đạt từng CLO của sinh viên trong 1 môn học
     *
     * Công thức:
     *   CLO_achieved% = SUM(student_score) / SUM(rubric.max_score) × 100
     *   (Chỉ tính rubric thuộc assessment đã published của môn)
     */
    private function recalculateCloAttainment(int $studentId, int $courseId): void
    {
        // Lấy tất cả CLO của môn
        $clos = $this->db->fetchAll(
            "SELECT id FROM clos WHERE course_id = ?",
            [$courseId]
        );

        foreach ($clos as $clo) {
            $result = $this->db->fetchOne(
                "SELECT 
                    COALESCE(SUM(ss.score), 0)       AS earned,
                    COALESCE(SUM(r.max_score), 0)    AS total
                 FROM rubrics r
                 JOIN assessments a ON a.id = r.assessment_id
                 LEFT JOIN student_scores ss ON ss.rubric_id = r.id AND ss.student_id = ?
                 WHERE r.clo_id = ?
                   AND a.is_published = 1",
                [$studentId, $clo['id']]
            );

            $pct = ($result['total'] > 0)
                ? round(($result['earned'] / $result['total']) * 100, 2)
                : 0.00;

            $this->db->query(
                "INSERT INTO clo_attainments (student_id, clo_id, achieved_percentage)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE achieved_percentage = VALUES(achieved_percentage), calculated_at = NOW()",
                [$studentId, $clo['id'], $pct]
            );
        }
    }

    // ── THUẬT TOÁN 2: Tính PLO Attainment ───────────────────────────

    /**
     * Tính % đạt từng PLO dựa trên CLO đã tính
     *
     * Công thức:
     *   PLO_achieved% = SUM( CLO_achieved% × mapping.weight ) / SUM(mapping.weight)
     *   (Weighted average theo trọng số ánh xạ)
     */
    private function recalculatePloAttainment(int $studentId, int $courseId): void
    {
        // Lấy tất cả PLO có liên quan đến môn này (qua CLO-PLO mapping)
        $plos = $this->db->fetchAll(
            "SELECT DISTINCT m.plo_id
             FROM clo_plo_mappings m
             JOIN clos c ON c.id = m.clo_id
             WHERE c.course_id = ?",
            [$courseId]
        );

        foreach ($plos as $plo) {
            $result = $this->db->fetchOne(
                "SELECT
                    SUM(ca.achieved_percentage * m.weight) AS weighted_sum,
                    SUM(m.weight)                          AS total_weight
                 FROM clo_plo_mappings m
                 JOIN clos c ON c.id = m.clo_id
                 JOIN clo_attainments ca ON ca.clo_id = m.clo_id AND ca.student_id = ?
                 WHERE m.plo_id = ?
                   AND c.course_id = ?",
                [$studentId, $plo['plo_id'], $courseId]
            );

            $pct = (isset($result['total_weight']) && $result['total_weight'] > 0)
                ? round($result['weighted_sum'] / $result['total_weight'], 2)
                : 0.00;

            $this->db->query(
                "INSERT INTO plo_attainments (student_id, plo_id, achieved_percentage)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE achieved_percentage = VALUES(achieved_percentage), calculated_at = NOW()",
                [$studentId, $plo['plo_id'], $pct]
            );
        }
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
             CROSS JOIN rubrics r ON r.assessment_id = ?
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
             LEFT JOIN clo_attainments ca ON ca.clo_id = c.id AND ca.student_id = ?
             WHERE c.course_id = ?
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
