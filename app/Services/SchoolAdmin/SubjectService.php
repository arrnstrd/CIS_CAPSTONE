<?php

namespace App\Services\SchoolAdmin;

use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class SubjectService
{
    /**
     * Create a new subject.
     *
     * @param array{code: string, name: string, level: string} $data
     * @return Subject
     */
    public function create(array $data): Subject
    {
        return DB::transaction(function () use ($data) {
            return Subject::create([
                'code' => $data['code'],
                'name' => $data['name'],
                'level' => Subject::normalizeLevel($data['level']),
            ]);
        });
    }

    /**
     * Update an existing subject.
     *
     * @param Subject $subject
     * @param array{code?: string, name?: string, level?: string} $data
     * @return Subject
     */
    public function update(Subject $subject, array $data): Subject
    {
        return DB::transaction(function () use ($subject, $data) {
            if (array_key_exists('level', $data)) {
                $data['level'] = Subject::normalizeLevel($data['level']);
            }

            $subject->update(array_filter($data, fn($value) => $value !== null));
            return $subject->fresh();
        });
    }

    /**
     * Delete a subject.
     *
     * @param Subject $subject
     * @return bool
     */
    public function delete(Subject $subject): bool
    {
        return DB::transaction(function () use ($subject) {
            return $subject->delete();
        });
    }
}
