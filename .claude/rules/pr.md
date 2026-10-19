---
paths:
  - "**/*"
---

# Pull requests

## Title and body

One sentence on the user-visible outcome, then what changed and how it was checked. Follow `.claude/skills/pr-description/SKILL.md`.

## Required before merge

- The diff matches an outcome in `INTENT.md` or a task in `PLAN.md`.
- `OWNERSHIP.md` still holds: author and copyright are Odell Duppins / Duppins Technology. No client is named as owner.
- `.claude/workflows/department-review.md` has run. Security has a verdict on the original diff and on any file a later department edited.
- License, price, and disclaimer edits are called out in the PR body. A new price is `BLOCK` until Odell Duppins has set it.
- Version bumps touch the plugin header and `GFBM_VERSION` together, and only on a release PR.

## Do not merge

- Secrets, `.env`, `CLAUDE.local.md`, or `.claude/settings.local.json`.
- A release zip under `release/`.
- A claim the test suite passed when no suite exists. Write "manual pass" or "not run" instead.
