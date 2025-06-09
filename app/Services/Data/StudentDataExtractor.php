<?php

namespace App\Services\Data;

use Illuminate\Support\Collection;

class StudentDataExtractor
{
    public function extract(array $students): Collection
    {
        return collect($students)->map(function ($student) {
            return [
                'canvas_id' => $student['id'],
                'student_name' => $student['name'] ?? "Unknown ({$student['id']})",
                'student_id' => $this->getStudentNumber($student),
                'sis_user_id' => $student['sis_user_id'] ?? null,
                'login_id' => $student['login_id'] ?? null,
                'integration_id' => $student['integration_id'] ?? null,
                'section_name' => $student['section_name'] ?? null,
            ];
        });
    }

    protected function getStudentNumber(array $student): string
    {
        return $student['integration_id'] ??
            $student['sis_user_id'] ??
            $student['login_id'] ??
            (string) $student['id'];
    }
}
