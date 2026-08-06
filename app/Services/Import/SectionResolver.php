<?php

namespace App\Services\Import;

use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;

/**
 * Resolves a Section ID from the three spreadsheet fields:
 * section name + department level + grade level.
 *
 * Pre-loads all sections into memory once to avoid N+1 queries
 * during large imports. The caller (BulkImportService) is responsible
 * for creating BulkImportIssue records when a section is not found.
 *
 * @see docs/bulk-import/03_business_rules.md  §4 Section Resolution
 */
class SectionResolver
{
    /** @var array<string, Section> Map: "name|level|grade_level" → Section model */
    private array $sectionMap = [];

    /**
     * Optionally inject a pre-loaded collection (useful for testing).
     * If omitted, all sections are loaded on the first resolve() call.
     */
    public function __construct(?Collection $sections = null)
    {
        if ($sections !== null) {
            $this->indexSections($sections);
        }
    }

    /**
     * Pre-load (or reload) the section index for the given school year.
     * Sections are not scoped to a school year, so this loads all sections.
     */
    public function loadActiveSections(?int $schoolYearId = null): void
    {
        $sections = Section::query()
            ->where('status', 'active')
            ->get(['id', 'name', 'level', 'grade_level', 'capacity', 'status']);

        $this->indexSections($sections);
    }

    /**
     * Resolve a section ID from the import row's lookup fields.
     *
     * @param  string      $sectionName   Section name from spreadsheet (e.g. "Einstein")
     * @param  string      $departmentLevel  Normalized level (elementary/highschool/senior_high_school)
     * @param  string      $gradeLevel    Grade level string (e.g. "5")
     * @return int|null    The section ID, or null if no matching active section exists
     */
    public function resolve(string $sectionName, string $departmentLevel, string $gradeLevel): ?int
    {
        if (empty($this->sectionMap)) {
            $this->loadActiveSections();
        }

        $key = $this->buildKey($sectionName, $departmentLevel, $gradeLevel);

        return isset($this->sectionMap[$key]) ? $this->sectionMap[$key]->id : null;
    }

    /**
     * Return the raw Section model for a lookup (used when caller needs
     * status/capacity info for issue reporting).
     */
    public function resolveSection(string $sectionName, string $departmentLevel, string $gradeLevel): ?Section
    {
        if (empty($this->sectionMap)) {
            $this->loadActiveSections();
        }

        $key = $this->buildKey($sectionName, $departmentLevel, $gradeLevel);

        return $this->sectionMap[$key] ?? null;
    }

    // -----------------------------------------------------------------
    //  Internal helpers
    // -----------------------------------------------------------------

    private function indexSections(Collection $sections): void
    {
        $this->sectionMap = [];

        foreach ($sections as $section) {
            $key = $this->buildKey($section->name, $section->level, (string) $section->grade_level);
            $this->sectionMap[$key] = $section;
        }
    }

    private function buildKey(string $name, string $level, string $gradeLevel): string
    {
        return strtolower(trim($name)) . '|' . strtolower(trim($level)) . '|' . trim($gradeLevel);
    }
}
