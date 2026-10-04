---
name: cis-feedback-auditor
description: Audits, implements, and refactors user-facing state feedback, error sanitization, and edge-case handling across the CIS_CAPSTONE Laravel codebase. Use when reviewing, modifying, or creating controllers, Blade templates, API routes, QR attendance scanner workflows, forms, tables, and reports to eliminate raw technical exceptions, blank states, and silent failures.
---

# CIS Feedback Auditor

## system context

The project is **CIS_CAPSTONE**, a web-based QR Student Attendance Monitoring with Centralized Academic Management System for Concepcion Integrated School. The system is built with Laravel and is used by different roles such as Super Admin, School Administrator, Teacher, and Scanner Operator. The system handles QR-based attendance, time-in/time-out, student records, academic management, grading, reports, notifications, user management, and other administrative functions.

The purpose of this skill is to ensure that the system provides **clear, user-friendly, and meaningful feedback for every possible user-facing state or action**.

The system must not expose raw technical errors, backend exceptions, framework messages, database errors, HTTP errors, validation internals, stack traces, or other technical implementation details to ordinary users.

The user should never be left confused about what happened.

## core principle

Every meaningful user action or system state must have a corresponding user-facing response.

The response should communicate at least one of the following:

* what happened
* whether the action was successful
* why the action could not proceed
* what information is missing or incorrect
* what the user should do next
* whether the system is currently processing something
* whether there is simply no available data

The interface must not appear blank, frozen, broken, or unresponsive when a known or unexpected condition occurs.

This applies across the entire system, not only to QR attendance scanning.

## user-facing behavior

When designing, reviewing, or modifying any page, feature, action, form, modal, table, scanner, dashboard, report, or workflow, check whether all relevant states have an appropriate response.

At minimum, consider these states:

1. Successful action
2. Failed action
3. Invalid input
4. Missing required information
5. Duplicate action
6. Action that is not currently allowed
7. Unauthorized action
8. No available data
9. No search/filter results
10. Loading or processing state
11. Network or connectivity problem
12. Temporary server/service problem
13. Unexpected or unhandled system problem
14. Expired session
15. Missing or unavailable resource
16. Conflicting or outdated data
17. Action that requires another action to happen first
18. Operation that is taking longer than expected
19. Partial or incomplete operation
20. Any other edge case that could leave the user uncertain about what happened

The exact message should depend on the actual situation. Do not use the same generic error message for every case when the system can provide more useful information.

## important rule: do not expose technical errors

Never show messages such as:

* "500 Internal Server Error"
* "SQLSTATE..."
* "Undefined variable..."
* "Call to a member function..."
* "Trying to access array offset..."
* raw Laravel/PHP exceptions
* database query errors
* stack traces
* raw API responses
* technical validation exceptions
* internal class, method, route, or controller names
* debugging information
* server configuration details

These may still be logged internally for developers or administrators, but they must not be presented directly to ordinary users.

Instead, convert technical failures into a clear user-facing fallback message.

Example:

Technical error:
"SQLSTATE[23505]: duplicate key value violates unique constraint..."

User-facing:
"This record already exists. Please check the existing information before trying again."

If the actual cause cannot safely or reliably be explained:

"Something went wrong while processing your request. Please try again."

## fallback behavior

The system should have a general fallback strategy for unexpected situations.

When a specific edge case is known, provide a specific message.

When the exact technical cause is unknown to the user interface, use a safe generic message that does not expose implementation details.

A generic fallback should still tell the user what they can do next.

Preferred structure:

**What happened + what to do next**

Examples:

"Your request could not be completed. Please try again."

"We couldn't load this information right now. Please refresh the page and try again."

"Something went wrong while saving the changes. Your changes may not have been saved. Please try again."

"Attendance could not be updated at this time. Please try again."

If the issue persists:

"Something went wrong while processing your request. Please try again later or contact the system administrator if the problem continues."

Do not invent a specific cause when the system does not actually know the cause.

## empty states are not errors

A page with no data should not simply appear blank.

If a table, dashboard, report, search result, notification list, attendance list, or other component has no data, display an appropriate empty state.

Examples:

"No attendance records found for this date."

"No students found matching your search."

"No teaching assignments have been added yet."

"No notifications available."

"No records are available for the selected period."

The message should make it clear that the system is functioning but there is currently no data to display.

## loading states

When an operation takes time, the user should receive a visible indication that the system is working.

Avoid making the interface appear frozen.

Use appropriate loading states such as:

"Loading attendance records..."

"Saving changes..."

"Generating report..."

"Processing scan..."

"Sending notification..."

The loading state should disappear or transition into a success/failure/empty state once the operation finishes.

## QR attendance and time-in/time-out

QR attendance is an important part of CIS_CAPSTONE and requires especially clear feedback.

The system should never simply display "Invalid" or a raw error when a scan cannot proceed.

The feedback should explain the actual attendance state whenever possible.

Examples:

Successful time-in:

"Time-in recorded successfully at 7:32 AM."

Successful time-out:

"Time-out recorded successfully at 4:15 PM."

Already timed in:

"Your time-in has already been recorded at 7:32 AM."

Already timed out:

"Your time-out has already been recorded at 4:15 PM."

Time-out attempted before time-in:

"Time-in has not been recorded yet. Please record your time-in first."

QR cannot be recognized:

"We couldn't recognize this QR code. Please make sure the QR code is visible and try again."

Attendance cannot currently be updated:

"Attendance could not be updated at this time. Please try again."

Temporary service problem:

"Attendance service is temporarily unavailable. Please try again in a moment."

The exact wording should reflect the actual attendance state and should provide useful information whenever available, such as the recorded time or required next action.

Do not expose backend reasons such as database failures, API exceptions, HTTP status codes, or implementation details.

## validation messages

Validation feedback should tell the user exactly what needs to be corrected.

Avoid vague messages such as:

"Invalid input."

Instead use:

"Please enter the student's name."

"Please select a grade level."

"Please select a subject before continuing."

"The password must contain at least 8 characters."

"The selected date is required."

Validation messages should be understandable without technical knowledge.

## duplicate actions

If a user performs an action that has already been completed, the system should explain the existing state instead of behaving like a generic error.

Examples:

"This student has already been added."

"Your time-in has already been recorded at 7:32 AM."

"This report has already been generated."

"The selected record already exists."

Where appropriate, provide the existing information or an available next action.

## actions that require another action first

If an operation cannot proceed because a prerequisite has not been completed, explain the dependency.

Examples:

"Please record the student's time-in before recording time-out."

"Please select a subject before adding a teaching assignment."

"Please complete the required fields before saving."

"Please assign a teacher before continuing."

Do not simply disable the interface without explanation if the user may reasonably wonder why the action is unavailable.

## unauthorized and restricted actions

When the user does not have permission to perform an action, do not expose internal authorization details.

Use clear language such as:

"You do not have permission to access this page."

"This action is not available for your account."

"Please contact the system administrator if you need access to this feature."

Do not expose role middleware names, permission IDs, policy classes, or backend authorization errors.

## session expiration

If the user's session expires, do not leave the page in a broken or unexplained state.

Use a clear message such as:

"Your session has expired. Please log in again to continue."

If possible, provide a clear path back to the login page.

## search and filtering

Search and filtering must have clear states.

If results exist, display them normally.

If no results exist:

"No students found matching your search."

"No attendance records found for the selected date."

"No records match the selected filters."

Do not leave an empty table without explanation.

If filters are active, make it clear that the lack of results may be caused by the selected criteria.

## forms and save/update operations

Every form submission should provide clear feedback.

Success:

"Student information updated successfully."

"Teaching assignment saved successfully."

"Changes saved successfully."

Failure:

"We couldn't save the changes. Please check the information and try again."

If the failure is caused by a known user issue, explain the required correction.

If it is a system issue, use a safe fallback message.

The system should not imply that data was saved when the operation actually failed.

## destructive actions

For deletion or other destructive operations, the system should clearly communicate the result.

Before destructive actions where appropriate, provide confirmation.

After success:

"Student record deleted successfully."

If unsuccessful:

"The record could not be deleted. Please try again."

If deletion is blocked because the record is being used elsewhere:

"This record cannot be deleted because it is currently being used by other records."

Only state the reason if the system actually knows and can safely communicate it.

## reports, exports, and file generation

Report and export functions should also provide meaningful states.

Loading:

"Preparing your report..."

Success:

"Report generated successfully."

Failure:

"We couldn't generate the report right now. Please try again."

No data:

"There are no records available for the selected period."

The interface should never appear to do nothing after the user clicks an export or report-generation action.

## notifications

When an operation involves notifications, the system should communicate whether the notification was successfully processed.

Examples:

"Notification sent successfully."

"Notification could not be sent. Please try again."

If the system only saved the notification for later processing, do not claim that it was delivered. Use wording that reflects the actual system state.

## consistency across the system

User-facing messages should follow a consistent tone and terminology.

Use simple, direct language.

Avoid overly technical wording.

Avoid blaming the user.

Avoid exposing internal implementation details.

Avoid unnecessary severity words such as "FATAL ERROR" or "SYSTEM FAILURE."

Avoid vague messages when the system can provide more useful information.

The wording should be appropriate for school administrators, teachers, scanner operators, and other ordinary users of the system.

## edge-case review process

Whenever modifying an existing feature, do not only review the normal successful flow.

For each feature, consider:

* What happens when the action succeeds?
* What happens when the input is incomplete?
* What happens when the input is invalid?
* What happens when the action was already performed?
* What happens when there is no data?
* What happens when the required record does not exist?
* What happens when the user lacks permission?
* What happens when the session expires?
* What happens when the network fails?
* What happens when the backend returns an unexpected response?
* What happens when a database or service operation fails?
* What happens when the user performs an action in an unexpected order?
* What happens when the operation takes time?
* What does the user see if an unexpected exception occurs?

Every applicable case should have a defined UI response or safe fallback.

## important implementation principle

Do not create unnecessary custom messages for every imaginable technical exception.

Instead, use a layered approach:

1. **Specific known condition** → specific user-friendly message.
2. **Known validation problem** → tell the user what to correct.
3. **Known business-rule condition** → explain the current state and next action.
4. **Temporary service/network issue** → explain that the request could not currently be completed and suggest retrying.
5. **Unexpected/unhandled exception** → show a generic safe fallback while logging the technical details internally.
6. **No data** → show an appropriate empty state.
7. **Loading operation** → show a loading state.

The objective is not to hide problems from developers. Technical errors should still be properly logged and traceable for debugging. The objective is to prevent technical implementation details from becoming the user's interface.

## when reviewing or editing the CIS_CAPSTONE system

Whenever asked to implement, audit, improve, or review a feature, proactively check the relevant user-facing states and edge cases related to that feature.

Do not assume that the successful/normal flow is enough.

If a feature currently exposes raw errors, blank states, unexplained failures, or ambiguous feedback, replace the user-facing behavior with an appropriate message or fallback state while preserving the underlying technical logging and debugging information.

Do not invent business rules that do not exist in the system.

Do not change the actual system behavior merely to create a message. First determine the real state or failure condition, then provide feedback that accurately represents it.

The guiding rule is:

**The system should always tell the user what happened, what state it is in, or what the user should do next. The user should never be left guessing because the interface is blank, silent, technically worded, or unclear.**

---

## practical implementation patterns (Laravel & Blade)

### Pattern A: Controller Try/Catch & Error Masking

```php
use Illuminate\Support\Facades\Log;
use Throwable;

public function store(StoreStudentRequest $request)
{
    try {
        $student = $this->studentService->create($request->validated());

        return redirect()
            ->route('students.index')
            ->with('success', 'Student record created successfully.');
    } catch (Throwable $e) {
        Log::error('Student creation failed', [
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
            'payload' => $request->except(['password', 'password_confirmation']),
        ]);

        return back()
            ->withInput()
            ->with('error', 'We couldn\'t save the changes. Please check the information and try again.');
    }
}
```

### Pattern B: Blade Contextual Empty States

```html
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
                <tr>
                    <td>{{ $student->student_id }}</td>
                    <td>{{ $student->full_name }}</td>
                    <td>{{ $student->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center py-4 text-muted">
                        No students found matching your search.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
```

---

## feature audit checklist

Before finalizing any controller, view, or API route in CIS_CAPSTONE, verify:

- [ ] Are raw exceptions, SQL errors, or 500 pages completely unreachable by ordinary users?
- [ ] Is every catch block logging complete trace details to Laravel logs?
- [ ] Does every table or list implement a dedicated `@empty` or empty-state component?
- [ ] Do async triggers and scanner feeds render visual loading states?
- [ ] Are duplicate scans and inverted attendance attempts handled with explanatory timestamps/instructions?
- [ ] Are all validation messages specific and non-technical?
