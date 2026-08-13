<x-layouts.admin>
    <x-slot name="title">
        Student Management
    </x-slot>

    <x-slot name="subtitle">
        Select a grade level to view, edit, or organize student records.
    </x-slot>

    <x-slot name="pageName">
        Student Management
    </x-slot>

    <style>
        .grade-section {
            margin-bottom: 2.5rem;
        }

        .grade-section-title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #5a5c69;
            margin-bottom: 1.25rem;
            padding-left: 0.25rem;
        }

        .grade-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            border-radius: 1rem;
            padding: 1.75rem 1.5rem;
            text-decoration: none;
            color: inherit;
            border: 1px solid #e3e6f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            transition: all 0.2s ease;
            height: 100%;
        }

        .grade-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            border-color: #d1d3e2;
            color: inherit;
        }

        .grade-card:active {
            transform: translateY(0);
        }

        .grade-info {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .grade-name {
            font-size: 1.15rem;
            font-weight: 700;
            color: #2e2f38;
            line-height: 1.2;
        }

        .grade-level {
            font-size: 0.8rem;
            color: #6e707e;
            font-weight: 500;
        }

        .student-count-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #4e73df;
            background: rgba(78, 115, 223, 0.08);
            padding: 0.35rem 0.75rem;
            border-radius: 2rem;
        }

        .student-count-badge i {
            font-size: 0.7rem;
        }

        .grade-chevron {
            color: #b7b9cc;
            font-size: 1rem;
            transition: transform 0.2s ease, color 0.2s ease;
            margin-left: 1rem;
            flex-shrink: 0;
        }

        .grade-card:hover .grade-chevron {
            transform: translateX(3px);
            color: #4e73df;
        }

        /* Bulkier responsive grid */
        .grade-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1.25rem;
        }

        @media (min-width: 576px) {
            .grade-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 992px) {
            .grade-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1400px) {
            .grade-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
    </style>

    <div class="grade-selection-wrapper">
        @foreach ($grades as $level => $gradeRange)
            <div class="grade-section">
                <p class="grade-section-title">{{ $level }}</p>

                <div class="grade-grid">
                    @foreach ($gradeRange as $g)
                        <a href="{{ route('student-management.grade', $g) }}" class="grade-card">
                            <div class="grade-info">
                                <span class="grade-name">Grade {{ $g }}</span>
                                <span class="grade-level">
                                    @if (str_contains(strtolower($level), 'primary'))
                                        Primary Level
                                    @elseif (str_contains(strtolower($level), 'junior') || str_contains(strtolower($level), 'secondary'))
                                        Junior High
                                    @elseif (str_contains(strtolower($level), 'senior'))
                                        Senior High
                                    @else
                                        {{ $level }}
                                    @endif
                                </span>
                                <span class="student-count-badge">
                                    <i class="fas fa-user-graduate"></i>
                                    {{ $gradeCounts[(string) $g] ?? 0 }} students
                                </span>
                            </div>
                            <i class="fas fa-chevron-right grade-chevron"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.admin>