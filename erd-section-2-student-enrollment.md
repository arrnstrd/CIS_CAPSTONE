# Section 2: Student Profiles & Enrollment ERD

Modular Entity Relationship Diagram documenting student master profiles, demographic data, guardian emergency contacts, unique QR authentication tokens, academic school years, class sections, and student enrollment records.

## Architectural Constraints & Specifications
- **Palette**: Pure monochrome (`#000000` text/stroke on `#FFFFFF` canvas). Zero colored fills or badges.
- **Connectors**: Strictly orthogonal Crow's Foot ERD notations (`||--o{`, `||--||`).
- **Precision Anchors**: Foreign Keys anchor directly from parent Primary Key (`users.id`, `students.id`, `school_years.id`, `sections.id`).
- **Clean Routing**: Pure blank connector paths with no text labels.

---

## High-Precision Vector Diagram

![Section 2 ERD Diagram](erd-section-2-student-enrollment.svg)

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
    users ||--|| teachers : ""
    teachers ||--o{ sections : ""
    students ||--o{ guardians : ""
    students ||--o{ qr_codes : ""
    students ||--o{ enrollments : ""
    school_years ||--o{ enrollments : ""
    sections ||--o{ enrollments : ""

    users {
        bigint id PK
        string first_name
        string last_name
        string email UK
        string role
    }

    teachers {
        bigint id PK
        bigint user_id FK
        string status
    }

    school_years {
        bigint id PK
        string school_year UK
        boolean is_active
    }

    sections {
        bigint id PK
        bigint advisor_id FK
        string name
        string level
        int grade_level
        int capacity
        string session_type
        string track
        string status
    }

    students {
        bigint id PK
        string lrn UK
        string student_number UK
        string first_name
        string middle_name
        string last_name
        string suffix
        string sex
        int age
        text address
        string status
    }

    guardians {
        bigint id PK
        bigint student_id FK
        string name
        string relationship
        string contact_number
        string email
    }

    qr_codes {
        bigint id PK
        bigint student_id FK
        string code UK
        string image_path
        boolean is_active
    }

    enrollments {
        bigint id PK
        bigint student_id FK
        bigint school_year_id FK
        bigint section_id FK
        int grade_level
        string level
        string status
    }
```

---

## Entity Catalog & Relationships

| Source Entity (Parent) | Relationship | Target Entity (Child) | Foreign Key | Description |
| :--- | :---: | :--- | :--- | :--- |
| `users.id` | `1 : 1` | `teachers` | `user_id` | Staff account linking to academic faculty profile. |
| `teachers.id` | `1 : N` | `sections` | `advisor_id` | Section advisory assignment held by faculty. |
| `students.id` | `1 : N` | `guardians` | `student_id` | Parents, guardians, and emergency contact details. |
| `students.id` | `1 : N` | `qr_codes` | `student_id` | Cryptographic QR identification card/token. |
| `students.id` | `1 : N` | `enrollments` | `student_id` | Annual enrollment history across academic levels. |
| `school_years.id` | `1 : N` | `enrollments` | `school_year_id` | Active academic school calendar binding. |
| `sections.id` | `1 : N` | `enrollments` | `section_id` | Classroom section placement for the term. |
