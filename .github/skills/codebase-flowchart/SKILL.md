---
name: codebase-flowchart
description: Generate an evidence-based, non-programmer-friendly Mermaid system flowchart (as a downloadable .md file) documenting how a specific feature actually works in an existing codebase. Use this skill whenever the user asks to "diagram", "flowchart", "visualize the flow of", or "document the process" for a feature, module, or workflow that already exists in their code — especially attendance systems, approval/verification pipelines, scan/check-in flows, or any multi-step backend process. Trigger this even if the user just says "make a flowchart of how X works" without naming Mermaid or Markdown explicitly. Do NOT use this for diagramming a feature that doesn't exist yet (that's a design/architecture task, not a codebase-inspection task) or for whole-application architecture diagrams — this skill is scoped to ONE feature's operational flow at a time.
---

# Codebase Flowchart Generator

Produces a single downloadable Markdown file containing a Mermaid flowchart that documents the **actual, current, implemented** behavior of one specific feature — written in plain operational language a non-programmer (professor, client, adviser, teammate) can follow, backed by cited evidence from the real code.

This skill exists because generic "make me a diagram" requests tend to produce two failure modes: (1) a flowchart of an idealized/assumed design instead of what the code really does, or (2) a diagram cluttered with class names, SQL, and method calls that only a programmer can read. This skill's whole job is to avoid both.

## When to use this

Use whenever the request is "diagram/flowchart/visualize/document the flow of `<feature>`" for something that already exists in the user's repo. Signs it fits:
- The user names a concrete feature or pipeline (attendance, checkout, approval, grading, onboarding, etc.)
- The output is meant to be shown to someone non-technical, or opened in a Markdown/Mermaid previewer
- The user cares about accuracy to the real implementation, not a proposed design

Don't use this for brand-new/not-yet-built features, for whole-app architecture maps, or for pure code review with no diagram deliverable.

## Inputs to collect before starting

If the user's request doesn't already specify these (their message may already contain everything, as in the reference prompt below — in that case just extract the answers, don't re-ask), ask briefly:

1. **Feature/flow scope** — the exact start and end points of the flow (e.g. "QR Scan → Time In/Out → Teacher Verification"). Keep it to ONE feature, not the whole app.
2. **Output filename** — a `kebab-case-name.md` (default: `<feature-slug>-flowchart.md`).
3. **Audience** — usually "non-programmer," which drives the wording rules below.
4. **Repo location** — where the relevant code lives, if not already open/known.

## Workflow

### Step 1 — Inspect the actual codebase (do this before drafting anything)

Trace the real implementation end-to-end for the named feature only. Depending on the stack, this typically means:
- Entry points: routes, controllers, API endpoints tied to the feature
- Core logic: services, use-cases, helper functions that make decisions
- Data layer: models, tables/columns actually read or written
- Every conditional branch that changes the outcome (validation checks, status checks, business rules)
- Any related feature the flow hands off to (e.g., a verification step owned by a different role)

Keep a running note of **file path → what it does**, in the user's own terms, as you go — you'll need this for the technical reference table later. If a step described in the request (e.g. "duplicate scan handling", "teacher verification") is NOT found in the code, do not assume it exists — flag it explicitly (see Step 4).

### Step 2 — Draft the operational step list

Convert what you found into a linear (with branches) list of **operational** steps — what the system does, not how the code is structured. Translate mechanically:

| Code-level thing you found | Operational wording to use instead |
|---|---|
| `QrScanService::process()` | "Scan Student QR Code" |
| `AttendanceLog::create()` | "Record Attendance" |
| a SQL `WHERE status = 'active'` check | "Verify Active Enrollment" |
| a controller method name | the business action it performs |
| a DB column name | the concept it represents |

Never put class names, method calls, SQL, or column names in the main diagram. Those go only in the technical reference table (Step 5).

### Step 3 — Build the Mermaid flowchart

Use `flowchart TD`. Structure:
- Start and end nodes
- One rectangular node per operational step, in the wording from Step 2
- One diamond (decision) node per branch that actually exists in the code — label it as a plain yes/no question (e.g. `Is QR Code Valid?`, `Time In or Time Out?`)
- Route failure/invalid branches to a clear error/end state instead of silently dead-ending
- If the flow hands off to a second actor (e.g., a teacher, an approver, a second system), make that boundary visually obvious — a subgraph or a clearly labeled section break works well
- Keep the happy path visually straight down the middle; branch off it for exceptions

Validate the Mermaid syntax mentally (matching brackets, valid node IDs, no reserved-word collisions) since the user will render this directly in a Markdown previewer — broken syntax means total failure of the primary deliverable.

### Step 4 — Handle gaps honestly

For any requested step, decision, or downstream workflow (e.g. "teacher verification") that isn't actually implemented in the code:
- Do not draw it in the diagram
- Do not fabricate the logic
- Add a plain note in the Markdown body, outside the diagram: `<Feature X>: NOT FOUND / NOT IMPLEMENTED IN CURRENT CODEBASE`, optionally with what would be needed to add it

### Step 5 — Write the supporting sections

After the diagram, in the same file, include:

**Technical Implementation Reference** — a table mapping each operational step to the real file/class/method/table backing it, e.g.:

| Operational Step | Implementation |
|---|---|
| QR Scan | `routes/api.php` → `AttendanceController@scan` |
| Attendance Recording | `AttendanceLog` model, `attendance_logs` table |

**Flowchart Process Breakdown** — one short paragraph per numbered stage, in plain language, explaining what happens and (briefly) why, for a reader who will never open the code.

### Step 6 — Save and hand off

Save as the agreed filename (default `<feature-slug>-flowchart.md`) and present it to the user as a downloadable file. Do not modify any application source code — this skill only ever produces one new documentation file.

## Hard constraints (do not violate)

- **Evidence over assumption.** Every node and decision must trace back to something you actually found in the code. If you're inferring instead of observing, say so.
- **No code-speak in the main diagram.** Class/method/SQL/column names are reference-table-only.
- **One feature, not the whole app.** Resist scope creep into unrelated parts of the system.
- **Don't invent missing steps.** Flag gaps instead of filling them in.
- **Don't touch source code.** This is a documentation-only deliverable.
- **Mermaid must actually render.** Double-check syntax before finalizing since the whole point is a rendered diagram, not a text description.

## Reference: example request this skill was built from

A concrete instance of this pattern: "inspect the codebase and produce `attendance-system-flowchart.md`, a Mermaid flowchart of QR Scan → Time In → Time Out → Attendance Record → Teacher Verification, in operational (non-programmer) language, backed by the real implementation, with a technical reference table and a plain-language breakdown, flagging anything not actually implemented (e.g., teacher verification) instead of fabricating it." Use this as the template for scope, tone, and structure when a new feature-flowchart request comes in — swap in the new feature's steps, decisions, and file evidence.
