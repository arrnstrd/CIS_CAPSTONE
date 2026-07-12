# Development Guidelines

## Architecture

Follow Laravel MVC.

Organize code by feature whenever possible.

## Controllers

Controllers should remain thin.

Business logic belongs in Services or Actions when appropriate.

## Models

Use Eloquent relationships.

Avoid duplicated queries.

## Validation

Use Form Request validation.

Avoid validation inside controllers.

## Database

Use foreign keys.

Maintain referential integrity.

Do not duplicate data.

## UI

Bootstrap 5.

Use reusable Blade components.

Keep interfaces clean and consistent.

## Naming

Use descriptive names.

Follow Laravel naming conventions.

Avoid abbreviations.