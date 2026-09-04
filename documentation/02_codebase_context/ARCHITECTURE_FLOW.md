# Architecture & Request Lifecycle

This document illustrates the execution lifecycle of requests through the **CIS Capstone** application.

---

## 1. High-Level Request Pipeline

Every incoming HTTP request traverses a clean layered architecture:

```text
  [ Client Browser / Scanner Camera ]
                  │
                  ▼
         [ public/index.php ]
                  │
                  ▼
        [ bootstrap/app.php ]
       (Registers Middleware & Routes)
                  │
                  ▼
         [ Route Matcher ]
    (routes/super-admin.php, school-admin.php,
     routes/teacher.php, scanner-operator.php,
     routes/web.php, routes/api.php)
                  │
                  ▼
        [ Middleware Stack ]
   (auth, role:EnsureUserHasRole, throttle)
                  │
                  ▼
       [ Form Request Validation ]
   (app/Http/Requests/{Domain}/*Request)
                  │
                  ▼
         [ POV Controller ]
   (app/Http/Controllers/{POV}/*Controller)
                  │
                  ▼
         [ Domain Service Layer ]
  (AttendanceStateMachine, GradingService,
   BulkImportService, QRCodeService, etc.)
                  │
                  ▼
       [ Eloquent ORM Models ]
   (Student, AttendanceLog, QuarterlyGrade, etc.)
                  │
                  ▼
        [ Database (PostgreSQL) ]
                  │
                  ├───────────────────────────────┐
                  ▼                               ▼
       [ Blade View / JSON ]             [ Event Dispatcher ]
    (resources/views/{pov}/*)           (TableUpdated -> Reverb)
                  │                               │
                  ▼                               ▼
          [ HTTP Response ]             [ WebSocket Client ]
```

---

## 2. Detailed Lifecycle Stages

### Stage 1: Bootstrapping & Route Routing (`bootstrap/app.php`)
In Laravel 13, all routes are mounted in `bootstrap/app.php`. Rather than maintaining a monolithic `web.php`, dedicated POV files are mounted cleanly via the `then:` callback:
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    channels: __DIR__.'/../routes/channels.php',
    health: '/up',
    then: function () {
        Route::middleware('web')->group(base_path('routes/super-admin.php'));
        Route::middleware('web')->group(base_path('routes/school-admin.php'));
        Route::middleware('web')->group(base_path('routes/teacher.php'));
        Route::middleware('web')->group(base_path('routes/scanner-operator.php'));
    },
)
```

### Stage 2: Role Authorization (`EnsureUserHasRole`)
Protected routes are guarded by the `'role'` middleware:
* Evaluates `Auth::user()->role`.
* Compares against the route argument (e.g. `role:admin`, `role:teacher`, `role:super_admin`, `role:scanner_operator`).
* If unauthorized, aborts immediately with `403 Unauthorized`.

### Stage 3: Request Validation (`app/Http/Requests/`)
Controllers do not perform inline validation. Dedicated FormRequest classes validate incoming payloads:
* `Academic/*`: Validates sections, subjects, school years.
* `Grading/*`: Validates assessment criteria, category weights, score ranges.
* `Import/*`: Validates uploaded spreadsheet structure.

### Stage 4: Service Layer Isolation (`app/Services/`)
Controllers remain thin, orchestrating HTTP input and output. Domain calculations, complex SQL joins, state transitions, and business rules are encapsulated in dedicated Services:
* **`AttendanceStateMachine`**: Handles student entry/exit rules, daily scan order, and anomaly flagging.
* **`GradingService`**: Computes weighted scores, transmutations, and trimester assessments.
* **`BulkImportService`**: Validates rows, matches sections, and batch-inserts student rosters.

### Stage 5: Real-Time Event Dispatching
State-changing operations trigger broadcasting events:
* Example: A valid QR station scan creates an `AttendanceLog` and fires `TableUpdated`.
* The event is broadcast via **Laravel Reverb** to authorized channels in `routes/channels.php`.
* Connected frontends receive the payload via `resources/js/echo.js` and update DOM tables dynamically.
