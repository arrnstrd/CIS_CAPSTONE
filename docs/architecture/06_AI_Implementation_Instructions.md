# AI Implementation Instructions

Before generating any code, read:

1. CURRENT_DATABASE.json
2. FINAL_ERD.dbml
3. DATABASE_ARCHITECTURE.md
4. BUSINESS_RULES.md
5. FEATURE_ARCHITECTURE.md
6. DEVELOPMENT_GUIDELINES.md

## General Rules

Do not redesign the architecture.

Do not invent new tables.

Do not remove existing functionality.

Preserve compatibility with the existing attendance system.

## Migration Generation

Generate migrations that transform the current database into the target architecture.

Do not recreate tables that already exist.

Only create new tables when required.

Modify existing tables only if specified.

## Code Style

Follow Laravel 13 conventions.

Generate:

- Migration
- Model
- Form Request
- Controller
- Service (when appropriate)

Use Eloquent relationships.

Keep controllers lightweight.

## Restrictions

Do not generate frontend code unless requested.

Do not generate seeders unless requested.

Do not modify unrelated modules.

Always preserve backward compatibility.