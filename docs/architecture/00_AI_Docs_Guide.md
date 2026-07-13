# AI Docs Guide

This is the first file an AI agent should read.

## Rule

Before doing any implementation, read all architecture docs first. Do not jump directly to `11_Implementation_Order.md`.

`11_Implementation_Order.md` is the task order for implementation. It is not the full reading guide.

## Read The Docs In This Order

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

## After Reading

After reading all docs:

1. Understand the requested task.
2. Check which module is affected.
3. Compare the current database export with the target ERD if the task affects schema.
4. Follow `11_Implementation_Order.md` as the implementation sequence.
5. Stop at the requested scope.

## Important

- `CURRENT_DATABASE.json` shows the current database state.
- `FINAL_ERD.dbml` shows the target database design.
- `08_Database_Specification.md` explains table details.
- `03_Business_Rules.md` explains system behavior.
- `11_Implementation_Order.md` explains what to do first during implementation.
