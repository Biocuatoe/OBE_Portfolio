<?php

declare(strict_types=1);

require_once __DIR__ . '/Database.php';


/**
 * ScoreService
 * - Chứa toàn bộ business logic tính toán attainment sau khi lưu rubric score.
 * - ScoreModel chỉ giữ query helpers, còn logic tính điểm/attainment nằm trong service.
 */
final class ScoreService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Lưu/upsert score cho 1 rubric và recalculates CLO/PLO attainment.
     *
     * @throws InvalidArgumentException|RangeException
     */
    public function saveScoreAndRecalculate(int $studentId, int $rubricId, float $score, ?string $feedback = null): void
    {
        $rubric = $this->db->fetchOne(
            "SELECT r.*, c.course_id FROM rubrics r JOIN clos c ON c.id = r.clo_id WHERE r.id = ?",
            [$rubricId]
        );

        if (!$rubric) {
            throw new \InvalidArgumentException("Rubric ID {$rubricId} không tồn tại.");
        }

        $max = (float)$rubric['max_score'];
        if ($score < 0 || $score > $max) {
            throw new \RangeException("Điểm {$score} vượt quá điểm tối đa {$max}.");
        }

        $this->db->beginTransaction();
        try {
            $this->db->query(
                "INSERT INTO student_scores (student_id, rubric_id, score, feedback)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE score = VALUES(score), feedback = VALUES(feedback)",
                [$studentId, $rubricId, $score, $feedback]
            );

            // Recalculate CLO/PLO
            $courseId = (int)$rubric['course_id'];
            $this->recalculateCloAttainment($studentId, $courseId);
            $this->recalculatePloAttainment($studentId, $courseId);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function recalculateCloAttainment(int $studentId, int $courseId): void
    {
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
                ? round(((float)$result['earned'] / (float)$result['total']) * 100, 2)
                : 0.00;

            $this->db->query(
                "INSERT INTO clo_attainments (student_id, clo_id, achieved_percentage)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE achieved_percentage = VALUES(achieved_percentage), calculated_at = NOW()",
                [$studentId, (int)$clo['id'], $pct]
            );
        }
    }

    private function recalculatePloAttainment(int $studentId, int $courseId): void
    {
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
                [$studentId, (int)$plo['plo_id'], $courseId]
            );

            $pct = (isset($result['total_weight']) && (float)$result['total_weight'] > 0)
                ? round(((float)$result['weighted_sum'] / (float)$result['total_weight']), 2)
                : 0.00;

            $this->db->query(
                "INSERT INTO plo_attainments (student_id, plo_id, achieved_percentage)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE achieved_percentage = VALUES(achieved_percentage), calculated_at = NOW()",
                [$studentId, (int)$plo['plo_id'], $pct]
            );
        }
    }
}

