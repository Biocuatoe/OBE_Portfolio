<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/BaseModel.php';

class AssessmentModel extends BaseModel
{
    protected string $table = 'assessments';

    public function getByAssignment(int $assignmentId): array
    {
        return $this->db->fetchAll(
            "SELECT a.*, 
                COUNT(DISTINCT r.id) AS rubric_count,
                ROUND(SUM(r.max_score), 2) AS total_max_score
             FROM assessments a
             LEFT JOIN rubrics r ON r.assessment_id = a.id
             WHERE a.assignment_id = ?
             GROUP BY a.id
             ORDER BY a.created_at DESC",
            [$assignmentId]
        );
    }
}
