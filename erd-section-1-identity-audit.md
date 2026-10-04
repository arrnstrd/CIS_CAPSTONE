# Section 1: Identity, Authentication & System Audit ERD

Modular Entity Relationship Diagram documenting user identity, authentication credentials, security session logs, administrative audit trails, and bulk import tracking.

## Architectural Constraints & Specifications
- **Palette**: Pure monochrome (`#000000` text/stroke on `#FFFFFF` canvas). Zero colored fills or badges.
- **Connectors**: Strictly orthogonal Crow's Foot ERD notations (`||--o{`, `||--||`).
- **Precision Anchors**: Foreign Keys anchor directly from parent Primary Key (`users.id` / `bulk_imports.id`).
- **Clean Routing**: Pure blank connector paths with no text labels.

---

## High-Precision Vector Diagram

![Section 1 ERD Diagram](erd-section-1-identity-audit.svg)

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
    users ||--o{ invitation_tokens : ""
    users ||--o{ login_logs : ""
    users ||--o{ admin_activity_logs : ""
    users ||--|| notification_preferences : ""
    users ||--o{ bulk_imports : ""
    bulk_imports ||--o{ bulk_import_issues : ""

    users {
        bigint id PK
        string employee_id UK
        string first_name
        string last_name
        string email UK
        string role
        string status
        string password
        string remember_token
        timestamp email_verified_at
        timestamp created_at
        timestamp updated_at
    }

    password_reset_tokens {
        string email PK
        string token
        timestamp created_at
    }

    personal_access_tokens {
        bigint id PK
        string tokenable_type
        bigint tokenable_id
        string token UK
        string name
        timestamp expires_at
    }

    invitation_tokens {
        bigint id PK
        bigint user_id FK
        string token_hash UK
        timestamp expires_at
        timestamp used_at
    }

    login_logs {
        bigint id PK
        bigint user_id FK
        string email_attempted
        string status
        string ip_address
        timestamp attempted_at
    }

    admin_activity_logs {
        bigint id PK
        bigint actor_id FK
        string action
        string target_type
        bigint target_id
        string ip_address
        string result
        timestamp created_at
    }

    notification_preferences {
        bigint id PK
        bigint user_id FK
        boolean attendance_enabled
        boolean grading_enabled
        boolean at_risk_enabled
        boolean analytics_enabled
        boolean import_enabled
    }

    notifications {
        bigint id PK
        string type
        string notifiable_type
        bigint notifiable_id
        text data
        timestamp read_at
    }

    bulk_imports {
        bigint id PK
        bigint created_by FK
        string original_filename
        string file_path
        string file_hash
        string status
        int total_rows
        int success_count
        int failed_count
    }

    bulk_import_issues {
        bigint id PK
        bigint bulk_import_id FK
        int row_number
        string issue_type
        string severity
        text message
        string status
    }
```

---

## Entity Catalog & Relationships

| Source Entity (Parent) | Relationship | Target Entity (Child) | Foreign Key | Description |
| :--- | :---: | :--- | :--- | :--- |
| `users.id` | `1 : N` | `invitation_tokens` | `user_id` | Account setup and activation tokens sent to staff. |
| `users.id` | `1 : N` | `login_logs` | `user_id` | Audit trail of successful and failed authentication attempts. |
| `users.id` | `1 : N` | `admin_activity_logs` | `actor_id` | Detailed security action logs performed by administrative users. |
| `users.id` | `1 : 1` | `notification_preferences` | `user_id` | Granular subscription toggle switches per staff account. |
| `users.id` | `1 : N` | `bulk_imports` | `created_by` | History of uploaded CSV/Excel roster import batches. |
| `bulk_imports.id` | `1 : N` | `bulk_import_issues` | `bulk_import_id` | Line-by-line validation errors or warnings from an import file. |
