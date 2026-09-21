# Section 3: Hardware Scans, Gates & Attendance Tracking ERD

Modular Entity Relationship Diagram documenting high-throughput kiosk/station hardware scans, daily gate attendance tracking, anti-passback duplicate protection, scan flagging, and automated guardian email alerts.

## Architectural Constraints & Specifications
- **Palette**: Pure monochrome (`#000000` text/stroke on `#FFFFFF` canvas). Zero colored fills or badges.
- **Connectors**: Strictly orthogonal Crow's Foot ERD notations (`||--o{`, `||--o|`).
- **Precision Anchors**: Foreign Keys anchor directly from parent Primary Key (`students.id`, `enrollments.id`, `users.id`, `attendance_logs.id`).
- **Clean Routing**: Pure blank connector paths with no text labels.

---

## High-Precision Vector Diagram

![Section 3 ERD Diagram](erd-section-3-attendance-hardware.svg)

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
    students ||--o{ enrollments : ""
    enrollments ||--o{ attendance_logs : ""
    users ||--o{ attendance_logs : ""
    enrollments ||--o{ qr_attendances : ""
    attendance_logs ||--o| qr_attendances : ""
    attendance_logs ||--o{ flagged_scans : ""
    attendance_logs ||--o{ email_logs : ""
    students ||--o{ email_logs : ""

    users {
        bigint id PK
        string first_name
        string last_name
        string role
    }

    students {
        bigint id PK
        string student_number UK
        string first_name
        string last_name
    }

    enrollments {
        bigint id PK
        bigint student_id FK
        int grade_level
        string status
    }

    schedule_configs {
        bigint id PK
        string level
        string session_type
        time in_start
        time in_end
        time late_threshold
        time out_start
        time out_end
    }

    attendance_logs {
        bigint id PK
        bigint enrollment_id FK
        bigint scanned_by_user_id FK
        string scan_type
        string session_type
        timestamp scan_time
        string device_id
    }

    qr_attendances {
        bigint id PK
        bigint enrollment_id FK
        bigint time_in_log_id FK
        bigint time_out_log_id FK
        date attendance_date
        int spam_offense_count
        timestamp cooldown_expires_at
    }

    flagged_scans {
        bigint id PK
        bigint attendance_log_id FK
        string flag_type
        text description
    }

    email_logs {
        bigint id PK
        bigint attendance_log_id FK
        bigint student_id FK
        string email
        string scan_type
        string status
        timestamp sent_at
    }
```

---

## Entity Catalog & Relationships

| Source Entity (Parent) | Relationship | Target Entity (Child) | Foreign Key | Description |
| :--- | :---: | :--- | :--- | :--- |
| `students.id` | `1 : N` | `enrollments` | `student_id` | Student academic enrollment records. |
| `enrollments.id` | `1 : N` | `attendance_logs` | `enrollment_id` | Immutable physical scans recorded at hardware gates. |
| `users.id` | `1 : N` | `attendance_logs` | `scanned_by_user_id` | Scanner operator or automated kiosk system identity. |
| `enrollments.id` | `1 : N` | `qr_attendances` | `enrollment_id` | Daily consolidated attendance rollups with cooldown state. |
| `attendance_logs.id` | `1 : 0..1` | `qr_attendances` | `time_in_log_id` | Link to specific morning gate arrival log. |
| `attendance_logs.id` | `1 : 0..1` | `qr_attendances` | `time_out_log_id` | Link to specific dismissal gate exit log. |
| `attendance_logs.id` | `1 : N` | `flagged_scans` | `attendance_log_id` | Anomaly records (late arrival, duplicate scan, cooldown spam). |
| `attendance_logs.id` | `1 : N` | `email_logs` | `attendance_log_id` | Guardian notification dispatches triggered by gate scan. |
| `students.id` | `1 : N` | `email_logs` | `student_id` | History of notification emails sent regarding this student. |
