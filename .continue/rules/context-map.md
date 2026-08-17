---
name: context-map
description: Read-only codebase context-mapping skill. Use when the user asks about the status, flow, or dependencies of a file/folder/feature - e.g. "what's the status of this", "how does this flow", "trace this function", "what files/dependencies does this use", "map this feature", "context transfer para sa ibang AI". Produces a standalone markdown report in chat that can be copy-pasted elsewhere as context - never edits, creates, or deletes files.
---

# Context Map Skill

## Purpose

Produce a **read-only** report documenting a feature's current status, the files and
dependencies it touches, ands how it flows/calls between files — written as a
**markdown report printed in chat** that's copy-paste-ready to hand off as context to
another AI or teammate. This skill NEVER modifies the filesystem — no edits, no new
files, no deletions.

This is NOT an audit. Don't evaluate quality, security, or correctness here — just
document what exists and how it connects. Use the separate audit-skill for that.

## Hard Rules (non-negotiable)

1. **Read-only.** Only use read/view/search tools. Never call edit, write, create,
   or delete tools.
2. **Scope lock.** Only inspect files explicitly named or clearly inside the
   requested scope. Do not wander into unrelated modules or the wider repo.
3. **Trace outward, not everywhere.** Follow imports/calls one or two hops out
   (what this file imports, what imports it, what it calls, what calls it). Name
   third-party libs or unrelated modules encountered along the way — don't open
   them just to be thorough.
4. **Status from evidence, not assumption.** Only describe what's observably true
   from the code (e.g. "handler exists, no error handling present", "stub/TODO
   found"). Don't guess at intent that isn't in the code.
5. **Budget the calls.** Don't re-read the same file twice.
6. **Output stays in chat.** Never save the report to a file unless the user
   explicitly asks for a downloadable copy.

## Workflow

1. **Confirm scope** — restate which file/folder/feature you're mapping.
2. **Locate the entry point(s)** — the component, route, or function that "starts"
   this feature.
3. **Trace outward** one or two hops in each direction (see Hard Rule 3).
4. **Note status** as observed from code.
5. **Write the report** using the template below, in a form that reads standalone —
   someone (or some AI) with zero prior context on this repo should be able to read
   it and understand the feature's shape.
6. Stop. Don't evaluate quality/issues — that's audit-skill's job. If the user wants
   both, run both skills and produce both reports.

## Report Template

```markdown
# Context Map: <feature/file name>

**Scope:** <files/folders reviewed>
**Not reviewed:** <related files that exist but weren't opened, if any>

## Status

<short, code-observed status - e.g. "core CRUD implemented, no validation on the
update endpoint, no tests present">

## Entry Point(s)

- `<file>` — <what triggers this, e.g. route, component mount, event>

## Files Involved

| File     | Role                           |
| -------- | ------------------------------ |
| `<path>` | <what it does in this feature> |

## Dependencies

- **Internal:** <other modules/files in the repo this relies on>
- **External:** <libraries/packages this relies on>

## Flow

1. <step-by-step: entry point calls X, which calls Y, which reads/writes Z>
2. ...

## Call Graph (optional, only if it clarifies)
```

ComponentA → useBookingHook → api/bookingService.ts → backend endpoint

```

## Open Questions / Gaps
- <anything ambiguous or missing that the next AI/person should know>
```

## Example trigger phrases

- "ano status ng booking feature, i-map mo yung flow"
- "anong files/dependencies ginagamit nito"
- "trace mo paano tumatawag itong function sa ibang files"
- "gawa ka ng context transfer para sa QR attendance module, ipapaste ko sa ibang AI"

## Notes

- If scope is huge (e.g. "map the whole repo"), ask the user to confirm or narrow
  it first, since that changes the read budget significantly.
- Keep the report standalone-readable — avoid references like "as mentioned above
  in our chat" since it's meant to be lifted out and pasted elsewhere.
