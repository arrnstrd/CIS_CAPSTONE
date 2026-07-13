# AI Implementation Instructions

Read `00_AI_Docs_Guide.md` first.

This file explains how an AI agent should use the project references before making backend changes.

## Required Reading Order

Read these files in this exact order:

1. `00_AI_Docs_Guide.md`
2. `06_AI_Implementation_Instructions.md`
3. `01_Project_Overview.md`
4. `CURRENT_DATABASE.json`
5. `FINAL_ERD.dbml`
6. `02_Database_Architecture.md`
7. `08_Database_Specification.md`
8. `03_Business_Rules.md`
9. `04_Feature_Architecture.md`
10. `05_Development_Guidelines.md`
11. `10_Project_Structure.md`
12. `13_Backend_Architecture.md`
13. `09_Laravel_Conventions.md`
14. `12_Development_Rules.md`
15. `11_Implementation_Order.md`

## How To Use The Reference Files

1. Treat `CURRENT_DATABASE.json` as the current database export.
2. Treat `FINAL_ERD.dbml` as the target schema.
3. Use `02_Database_Architecture.md` for the high-level migration plan.
4. Use `08_Database_Specification.md` for table details and relationships.
5. Use `03_Business_Rules.md` for application behavior.
6. Use `10_Project_Structure.md`, `13_Backend_Architecture.md`, and `09_Laravel_Conventions.md` for code organization and Laravel standards.

## Required Procedure

Follow this exact procedure before generating code:

1. Identify the requested feature or change.
2. Check whether it affects the database, backend code, frontend code, or all of them.
3. Compare `CURRENT_DATABASE.json` with `FINAL_ERD.dbml`.
4. List only the missing tables, missing columns, missing indexes, or missing relationships required for the request.
5. Check `02_Database_Architecture.md` and `08_Database_Specification.md` before writing migrations.
6. Check `03_Business_Rules.md` before writing business logic.
7. Check `10_Project_Structure.md` before choosing folders.
8. Check `13_Backend_Architecture.md` before deciding whether logic belongs in a Controller, Form Request, Service, Model, or migration.
9. Check `09_Laravel_Conventions.md` before naming classes, routes, methods, and files.
10. Apply the guardrails in `12_Development_Rules.md`.
11. Follow `11_Implementation_Order.md` when creating files.
12. Generate only the code required by the user request.

## Conflict Resolution

If two documents appear to conflict, use this priority order:

1. The user's latest instruction.
2. `CURRENT_DATABASE.json` for the current database state.
3. `FINAL_ERD.dbml` for the target database state.
4. `08_Database_Specification.md` for schema details.
5. `03_Business_Rules.md` for behavior.
6. `13_Backend_Architecture.md` and `09_Laravel_Conventions.md` for implementation style.

When uncertain, preserve existing functionality and avoid redesigning unrelated modules.
