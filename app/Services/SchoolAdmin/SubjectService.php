<?php

namespace App\Services\SchoolAdmin;

use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubjectService
{
    /**
     * Generate a unique subject code from a subject name.
     *
     * @param string $name
     * @return string
     */
    public function generateCode(string $name): string
    {
        $base = Str::upper(Str::slug($name));
        $base = str_replace($base, '-', '');
        $base = mb_substr($base, 0, 8);

        if (empty($base)) {
            $base = 'SUBJ';
        }

        $candidate = $base;
        $suffix = 1;

        while (Subject::where('code', $candidate)->exists()) {
            $candidate = $base . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    /**
     * Create a new subject.
     *
     * @param array{code?: ?string, name: string, level: string, code_mode?: string} $data
     * @return Subject
     */
    public function create(array $data): Subject
    {
        $code = $data['code'] ?? null;

        // Auto-generate a code when the user chose auto mode or left the code blank.
        if (empty($code) || ($data['code_mode'] ?? null) === 'auto') {
            $code = $this->generateCode($data['name']);
        }

        return DB::transaction(function () use ($data, $code) {
            return Subject::create([
                'code' => $code,
                'name' => $data['name'],
                'level' => Subject::normalizeLevel($data['level']),
            ]);
        });
    }

    /**
     * Create multiple subjects at once from a comma-separated list of names.
     *
     * @param array{names: array<string>, level: string, code_mode?: string} $data
     * @return array<Subject>
     */
    public function createMany(array $data): array
    {
        $names = collect($data['names'])
            ->map(fn($name) => trim($name))
            ->filter(fn($name) => filled($name))
            ->unique();

        if ($names->isEmpty()) {
            throw new \InvalidArgumentException('No valid subject names were provided.');
        }

        return DB::transaction(function () use ($names, $data) {
            return $names->map(function ($name) use ($data) {
                $code = ($data['code_mode'] ?? null) === 'auto'
                    ? $this->generateCode($name)
                    : null;

                return Subject::create([
                    'code' => $code,
                    'name' => $name,
                    'level' => Subject::normalizeLevel($data['level']),
                ]);
            })->values();
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
