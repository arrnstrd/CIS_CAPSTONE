# Session Logs

## Purpose

This folder contains chronological records of AI coding sessions, architectural changes, and codebase modifications. The logs serve as an audit trail for understanding:

- What changes were made during each session
- Files that were modified, created, or deleted
- Date and timestamp of each session
- Summary of changes and their purpose
- Context for resolving merge conflicts
- Evolution of the codebase over time

## AI Logging Instructions

**The AI assistant is required to log a session only when it creates, modifies, or deletes files.** Read-only actions such as reading documentation, analyzing code, reviewing code, or troubleshooting without file changes do not require a session log. Each session log should include:

### Required Log Format

```markdown
# Session Log - [YYYY-MM-DD]_[HH-MM-SS]

## Date & Time
- Start: [YYYY-MM-DD HH:MM:SS]
- End: [YYYY-MM-DD HH:MM:SS]

## Session Purpose
[Brief description of the session goals and objectives]

## Files Modified
- **Created:** [list of new files]
- **Modified:** [list of existing files]
- **Deleted:** [list of deleted files]

## Changes Summary
[Bullet point list of key changes made]

## Context & Purpose
[Explanation of why these changes were made and how they fit into the overall project]

## Next Steps
[Any follow-up actions or pending items]

## Related Documentation
- Links to relevant documentation files
- References to business logic or technical requirements
```

### Logging Trigger

**AI MUST create a session log when one or more files are:**
1. Created
2. Modified
3. Deleted

**AI MUST NOT create a session log for read-only actions**, including reading documentation, analyzing or reviewing code, or troubleshooting when no files are changed.

### Session Log Naming Convention

- Use format: `SESSION_[YYYY-MM-DD]_[HH-MM-SS].md`
- Example: `SESSION_2026-09-05_14-30-00.md`

## User Awareness

**Users should be aware that:**
- AI sessions that change files are logged
- Logs provide transparency into code changes
- Logs help with merge conflict resolution
- Logs maintain a historical record of the codebase evolution
- Each log includes the purpose and context of changes

## Log Maintenance

- Old logs should be retained for historical reference
- Logs can be archived periodically if needed
- Ensure logs are readable and well-structured
- Include relevant links to documentation or issues
