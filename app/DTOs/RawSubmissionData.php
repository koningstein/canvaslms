<?php

namespace App\DTOs;

class RawSubmissionData
{
    public function __construct(
        public readonly int $studentId,
        public readonly int $courseId,
        public readonly int $assignmentId,
        public readonly string $workflowState,
        public readonly ?float $score = null,
        public readonly ?string $grade = null,
        public readonly ?string $submittedAt = null,
        public readonly ?string $gradedAt = null,
        public readonly bool $excused = false,
        public readonly bool $late = false
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            studentId: $data['student_id'],
            courseId: $data['course_id'],
            assignmentId: $data['assignment_id'],
            workflowState: $data['workflow_state'],
            score: $data['score'] ?? null,
            grade: $data['grade'] ?? null,
            submittedAt: $data['submitted_at'] ?? null,
            gradedAt: $data['graded_at'] ?? null,
            excused: $data['excused'] ?? false,
            late: $data['late'] ?? false
        );
    }

    public function isLate(string $dueAt): bool
    {
        if (!$this->submittedAt || !$dueAt) {
            return false;
        }

        return strtotime($this->submittedAt) > strtotime($dueAt);
    }

    public function hasGrade(): bool
    {
        return $this->workflowState === 'graded' &&
            ($this->score !== null || !empty($this->grade));
    }
}
