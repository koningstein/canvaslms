<?php

namespace App\DTOs;

class RawAssignmentData
{
    public function __construct(
        public readonly int $assignmentId,
        public readonly string $assignmentName,
        public readonly int $courseId,
        public readonly int $moduleId,
        public readonly string $moduleName,
        public readonly float $pointsPossible,
        public readonly ?string $dueAt = null,
        public readonly array $submissionTypes = [],
        public readonly string $assignmentGroupName = 'Unknown'
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            assignmentId: $data['assignment_id'],
            assignmentName: $data['assignment_name'],
            courseId: $data['course_id'],
            moduleId: $data['module_id'],
            moduleName: $data['module_name'],
            pointsPossible: $data['points_possible'],
            dueAt: $data['due_at'] ?? null,
            submissionTypes: $data['submission_types'] ?? [],
            assignmentGroupName: $data['assignment_group_name'] ?? 'Unknown'
        );
    }

    public function isNonSubmittable(): bool
    {
        return empty($this->submissionTypes) || in_array('none', $this->submissionTypes);
    }
}
