<?php

namespace App\DTOs;

class RawStudentData
{
    public function __construct(
        public readonly int $canvasId,
        public readonly string $studentName,
        public readonly string $studentId,
        public readonly ?string $sisUserId = null,
        public readonly ?string $loginId = null,
        public readonly ?string $integrationId = null,
        public readonly ?string $sectionName = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            canvasId: $data['canvas_id'],
            studentName: $data['student_name'],
            studentId: $data['student_id'],
            sisUserId: $data['sis_user_id'] ?? null,
            loginId: $data['login_id'] ?? null,
            integrationId: $data['integration_id'] ?? null,
            sectionName: $data['section_name'] ?? null
        );
    }
}
