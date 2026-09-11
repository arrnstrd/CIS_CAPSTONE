---
name: audit-skill
description: Read-only codebase audit skill for reviewing code quality, security, and correctness. Use when the user asks to audit, review, check, or assess a specific file, folder, or feature - e.g. "audit this", "review my code", "check for issues", "security review", "sanity check this PR". Produces a markdown report in chat only - never edits, creates, or deletes files.
---

# Codebase Audit Skill

## Purpose

Perform a **read-only** audit of a specified scope (file, folder, or feature) and return
findings as a **markdown report printed in chat**. This skill NEVER modifies the
filesystem — no edits, no new files, no deletions, no formatting changes.

## Hard Rules (non-negotiable)

1. **Read-only.** Only use read/view/search tools. Never call edit, write, create,
   or delete tools during an audit — even if an obvious fix is spotted.
2. **Scope lock.** Only inspect files explicitly named or clearly inside the
   requested scope (e.g. "audit `src/booking/`" = that folder only). Do not walk
   into unrelated modules, node_modules, config, or the wider repo unless the user
   asks for a full repo audit.
3. **No unsolicited fixes.** Report issues; do not "helpfully" patch them. If the
   user wants fixes, that's a separate, explicit follow-up request.
4. **Budget the calls.** Don't re-read the same file twice. Don't recursively
   expand into every import just to "be thorough" — audit what's in scope, note
   what's out of scope as a follow-up suggestion instead of chasing it.
5. **Output stays in chat.** Never save the report to a file unless the user
   explicitly asks for a downloadable copy.

## Workflow

1. **Confirm scope** — restate exactly which file(s)/folder(s)/feature you're
   auditing before starting, so it's clear what was NOT touched.
2. **Read only what's in scope.** Skim structure first (file tree / list), then
   open the relevant files.
3. **Evaluate against relevant lenses** (pick what applies, skip what doesn't):
    - Correctness / logic bugs
    - Security (input validation, auth checks, secrets, injection risk)
    - Error handling / edge cases
    - Code quality (naming, duplication, dead code, complexity)
    - Consistency with rest of the codebase's patterns
    - Performance red flags (only if obviously relevant)
4. **Write the report** using the template below.
5. Stop. Do not proceed to fix anything unless asked.

## Report Template

```markdown
# Audit: <scope name>

**Scope:** <files/folders reviewed>
**Not reviewed:** <anything explicitly out of scope, for transparency>

## Summary

<2-3 sentence overall verdict>

## Findings

### 🔴 Critical

- **<title>** — `<file>:<line>` — <what's wrong and why it matters>

### 🟠 Moderate

- **<title>** — `<file>:<line>` — <description>

### 🟡 Minor / Style

- **<title>** — `<file>:<line>` — <description>

## Recommendations

- <short, actionable, ordered by priority>

## Not Flagged (optional, only if useful)

- <things that look risky at a glance but are actually fine — saves a re-ask>
```

## Example trigger phrases

- "audit yung `booking-service.ts`"
- "check this feature for issues, yung QR attendance module lang"
- "review my auth flow, security-wise"
- "sanity check itong PR bago i-merge"

## Notes

- If the requested scope is huge (e.g. "audit the whole repo"), first ask the
  user to confirm or narrow it, since that changes the read budget significantly.
- If no obvious issues are found, say so plainly — don't manufacture findings
  to look thorough.
