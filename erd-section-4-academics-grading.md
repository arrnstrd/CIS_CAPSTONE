# Section 4: Academics, Grading, Verification & Interventions ERD

Modular Entity Relationship Diagram documenting teacher instructional loads, DepEd academic grading configurations, student quarterly assessment scores, classroom attendance verifications, and academic intervention notes.

## Architectural Constraints & Specifications
- **Palette**: Pure monochrome (`#000000` text/stroke on `#FFFFFF` canvas). Zero colored fills or badges.
- **Connectors**: Strictly orthogonal Crow's Foot ERD notations (`||--o{`).
- **Precision Anchors**: Foreign Keys anchor directly from parent Primary Key (`teachers.id`, `subjects.id`, `teaching_assignments.id`, `grading_periods.id`, `assessment_categories.id`, `assessments.id`, `attendance_verifications.id`).
- **Clean Routing**: Pure blank connector paths with no text labels.

---

## High-Precision Vector Diagram

![Section 4 ERD Diagram](erd-section-4-academics-grading.svg)

---

## Mermaid UML Definition

```mermaid
---
config:
  theme: base
  themeVariables:
    primaryColor: '#ffffff'
    primaryTextColor: '#000000'
    primaryBorderColor: '#000000'
    lineColor: '#000000'
    textColor: '#000000'
    background: '#ffffff'
---
erDiagram
    teachers ||--o{ teaching_assignments : ""
    subjects ||--o{ teaching_assignments : ""
    teaching_assignments ||--o{ grading_configs : ""
    assessment_categories ||--o{ grading_configs : ""
    teaching_assignments ||--o{ assessments : ""
    grading_periods ||--o{ assessments : ""
    assessment_categories ||--o{ assessments : ""
    assessments ||--o{ student_assessment_scores : ""
    teaching_assignments ||--o{ term_grades : ""
    grading_periods ||--o{ term_grades : ""
    teaching_assignments ||--o{ attendance_verifications : ""
    teachers ||--o{ attendance_verifications : ""
    attendance_verifications ||--o{ attendance_verification_histories : ""
    teachers ||--o{ risk_remarks : ""
    teachers ||--o{ risk_follow_ups : ""
    teachers ||--o{ academic_notes : ""

    teachers {
        bigint id PK
        bigint user_id
        string status
    }

    subjects {
        bigint id PK
        string code UK
        string name
        string level
        string subject_type
    }

    grading_periods {
        bigint id PK
        string name
        int sequence UK
        string period_type
        boolean is_active
        date start_date
        date end_date
    }

    assessment_categories {
        bigint id PK
        string name UK
    }

    teaching_assignments {
        bigint id PK
        bigint teacher_id FK
        bigint subject_id FK
        bigint section_id
        bigint school_year_id
        string status
    }

    grading_configs {
        bigint id PK
        bigint teaching_assignment_id FK
        bigint assessment_category_id FK
        decimal weight
    }

    assessments {
        bigint id PK
        bigint teaching_assignment_id FK
        bigint assessment_category_id FK
        bigint grading_period_id FK
        string title
        int total_items
        date assessment_date
    }

    student_assessment_scores {
        bigint id PK
        bigint assessment_id FK
        bigint enrollment_id
        decimal score
        text remarks
    }

    term_grades {
        bigint id PK
        bigint teaching_assignment_id FK
        bigint enrollment_id
        bigint grading_period_id FK
        decimal written_work_grade
        decimal performance_task_grade
        decimal transmuted_grade
    }

    attendance_verifications {
        bigint id PK
        bigint enrollment_id
        bigint teaching_assignment_id FK
        bigint teacher_id FK
        date attendance_date
        string status
        text remarks
        bigint resolved_by
    }

    attendance_verification_histories {
        bigint id PK
        bigint attendance_verification_id FK
        bigint changed_by
        string previous_status
        string new_status
        text remarks
    }

    risk_remarks {
        bigint id PK
        bigint enrollment_id
        bigint teacher_id FK
        text remark
        timestamp created_at
    }

    risk_follow_ups {
        bigint id PK
        bigint enrollment_id
        bigint teacher_id FK
        date follow_up_date
        text intervention
        string status
        text notes
    }

    academic_notes {
        bigint id PK
        bigint enrollment_id
        bigint teacher_id FK
        text note
        timestamp created_at
    }
```

---

## Entity Catalog & Relationships

| Source Entity (Parent) | Relationship | Target Entity (Child) | Foreign Key | Description |
| :--- | :---: | :--- | :--- | :--- |
| `teachers.id` | `1 : N` | `teaching_assignments` | `teacher_id` | Instructional schedule linking teacher, subject, section, and term. |
| `subjects.id` | `1 : N` | `teaching_assignments` | `subject_id` | Curriculum course associated with a teaching assignment. |
| `teaching_assignments.id` | `1 : N` | `grading_configs` | `teaching_assignment_id` | Category weight distribution (e.g. 40% Written Work, 60% Perf Tasks). |
| `assessment_categories.id` | `1 : N` | `grading_configs` | `assessment_category_id` | DepEd component type link (WW, PT, QA). |
| `teaching_assignments.id` | `1 : N` | `assessments` | `teaching_assignment_id` | Quizzes, exams, and projects created by the instructor. |
| `grading_periods.id` | `1 : N` | `assessments` | `grading_period_id` | Quarter/Term association for assessment items. |
| `assessment_categories.id` | `1 : N` | `assessments` | `assessment_category_id` | Component categorization of the assessment. |
| `assessments.id` | `1 : N` | `student_assessment_scores` | `assessment_id` | Individual raw student scores recorded in E-Class record. |
| `teaching_assignments.id` | `1 : N` | `term_grades` | `teaching_assignment_id` | Quarterly composite grade records. |
| `grading_periods.id` | `1 : N` | `term_grades` | `grading_period_id` | Grading quarter target for term report cards. |
| `teaching_assignments.id` | `1 : N` | `attendance_verifications` | `teaching_assignment_id` | Subject-room physical attendance audit checks. |
| `teachers.id` | `1 : N` | `attendance_verifications` | `teacher_id` | Verification logging by the presiding instructor. |
| `attendance_verifications.id` | `1 : N` | `attendance_verification_histories` | `attendance_verification_id` | Immutable audit changes for attendance resolutions. |
| `teachers.id` | `1 : N` | `risk_remarks` | `teacher_id` | Early warning academic/attendance risk observations. |
| `teachers.id` | `1 : N` | `risk_follow_ups` | `teacher_id` | Scheduled remediation, counseling, and parent conferences. |
| `teachers.id` | `1 : N` | `academic_notes` | `teacher_id` | General qualitative teacher commentary on student behavior. |
