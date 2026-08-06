# Bulk Import User Experience

## Design Philosophy

The interface should resemble a modern enterprise dashboard.

Avoid traditional Bootstrap appearance.

Use:

- spacious cards
- soft shadows
- rounded corners
- clean spacing
- compact tables
- minimal colors
- status badges
- progress indicators

The page should feel similar to modern admin products such as Linear, Notion, Stripe Dashboard, or GitHub.

---

# Main Page Layout

Top Section

Contains one centered Import Card.

Contents

- Upload button
- Drag and drop area
- Accepted file types
- Maximum file size
- Download template button

This card should not occupy the full width.

---

Bottom Section

Contains tab navigation.

Tabs

History

Issues

History is the default tab.

---

# Upload Flow

Administrator uploads Excel.

System validates

- extension
- size
- file readability

If invalid

Show centered modal.

If valid

Proceed to validation.

---

# Validation Modal

Displays

Total Rows

Valid Rows

Warnings

Errors

Small preview table

Only first few issues are shown.

Actions

Cancel

Re-upload

Proceed

If critical validation errors exist

Proceed button remains disabled.

---

# Processing Modal

Centered modal.

Contains

Progress bar

Current row

Estimated remaining time

Current activity

Example

Creating Student

Creating Guardian

Creating Enrollment

Generating QR

Processing should feel live.

Background processing may be allowed.

---

# Success Modal

Shows

Successfully Imported

Total Students

Total Enrollments

Warnings

Errors

Buttons

View History

Import Another File

Close

---

# Error Modal

If processing finishes with issues

Show

Completed With Issues

Summary

Successful rows

Failed rows

Warnings

Message

Failed rows can be reviewed inside Issues.

Buttons

View Issues

Close

---

# History Tab

Shows previous imports.

Columns

Transaction ID

Filename

Uploaded By

Date

Status

Students Imported

Issues

Duration

Actions

Newest first.

Pagination

10–15 rows.

---

# History Details

Opens in a new browser tab.

Contains

Import summary

Timeline

Statistics

Validation summary

Issue summary

Download original file

Download issue report

This page acts as an audit trail.

---

# Issues Tab

Shows unresolved issues.

Columns

Row

Learner Name

Issue

Severity

Status

Created

Action

Filters

Severity

Status

Transaction

Date

Search

Pagination

10–15 rows.

---

# Issue Details

Opens in a new page.

Displays

Original row

Normalized values

Validation message

Suggested fix

Raw imported data

---

# Status Colors

Pending

Gray

Validating

Blue

Waiting Confirmation

Orange

Processing

Blue

Completed

Green

Completed With Issues

Yellow

Failed

Red

Cancelled

Gray

---

# Notifications

Do not use browser alerts.

Use centered modals.

Use toast notifications only for small confirmations.

Examples

Template downloaded

Issue acknowledged

History refreshed

---

# Empty States

History

"No imports yet."

Issues

"No issues found."

Validation

"No validation problems."

Each empty state should contain a helpful illustration and a primary action.

---

# Responsive Behavior

Desktop

Two-section layout.

Tablet

Cards stack vertically.

Mobile

Tables become responsive.

Tabs remain accessible.

All actions must remain available regardless of screen size.