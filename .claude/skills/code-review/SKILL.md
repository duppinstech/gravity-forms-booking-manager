---
name: code-review
description: Reviews GF Booking Manager diffs for correctness, booking-rule enforcement, escaping, and ownership. Use when reviewing a pull request, a local diff, or when the user asks for a code review of this plugin.
---

# Code review

Read `.claude/rules/code-style.md`, `.claude/rules/api-conventions.md`, and `OWNERSHIP.md` before commenting.

## What to check

- Slot availability is enforced in PHP when the entry is validated, not only in the browser.
- Output is escaped. Input is sanitized. AJAX checks `gfbm_nonce`.
- New field settings survive save and reload through `GFBM_Helpers`.
- PHP stays compatible with 7.4.
- `Author` and copyright still name Duppins Technology / Odell Duppins.
- The change matches a line in `INTENT.md` or `PLAN.md`.

## Report

```markdown
# Code review

## Findings
- Critical: <must fix>
- Suggestion: <should fix>
- Note: <optional>

## Ownership
<pass or the exact line that drifted>

## Tests
<what evidence exists>
```

Lead with findings. Security depth belongs to `.claude/agents/security-reviewer.md`. Do not include exploit steps.
