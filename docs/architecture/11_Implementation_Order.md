# Implementation Order

Follow this order when implementing backend changes.

## Step 1: Scope The Request

1. Identify the feature or module being changed.
2. Identify whether the change needs database work.
3. Identify whether the change needs backend code only or frontend code too.
4. Do not include unrelated modules.

## Step 2: Plan Database Changes

1. Read `CURRENT_DATABASE.json`.
2. Read `FINAL_ERD.dbml`.
3. Read `02_Database_Architecture.md`.
4. Read `08_Database_Specification.md`.
5. Create migrations only for missing or changed schema items required by the request.
6. Modify existing tables before creating dependent foreign keys.
7. Create new lookup tables before tables that reference them.
8. Add indexes and foreign keys after required columns exist.

## Step 3: Update Models

1. Add or update Eloquent models for affected tables.
2. Add relationships defined in `08_Database_Specification.md`.
3. Keep large business logic out of models.

## Step 4: Add Form Requests

1. Create Form Request classes for create and update actions.
2. Put validation rules in Form Requests.
3. Do not validate directly inside controllers.

## Step 5: Add Services

1. Put business logic in Services.
2. Use transactions when multiple related records are created or updated.
3. Preserve the existing attendance and QR behavior.

## Step 6: Add Controllers

1. Keep controllers thin.
2. Controllers should receive requests, call Form Requests, call Services, and return responses or views.
3. Do not place grade computation, attendance processing, QR generation, or complex rules inside controllers.

## Step 7: Add Routes

1. Use resource routes where appropriate.
2. Follow the route naming conventions in `09_Laravel_Conventions.md`.
3. Avoid deeply nested routes unless required.

## Step 8: Frontend

Do not modify frontend files unless the user explicitly requests frontend changes.




## Completion

After completing the requested phase:

1. Summarize the implementation.
2. List every file created.
3. List every file modified.
4. Explain why each change was made.
5. Generate an implementation report in Markdown.
6. List any remaining work that is outside the requested scope.
7. Create md file for each changes under the `docs/implementaion` folder. 

Stop after completing the requested phase.