# Development Rules

These are the non-negotiable guardrails for implementation.

1. Do not modify frontend files unless the user explicitly requests frontend changes.
2. Do not remove existing features.
3. Do not change existing attendance or QR scan behavior unless the user explicitly requests it.
4. Preserve existing database data.
5. Preserve existing APIs whenever possible.
6. Do not invent tables, columns, modules, seeders, or dummy data.
7. Do not redesign unrelated modules.
8. Use Laravel migrations for schema changes.
9. Follow the implementation order in `11_Implementation_Order.md`.
10. Follow backend architecture in `13_Backend_Architecture.md`.
11. Follow Laravel standards in `09_Laravel_Conventions.md`.
