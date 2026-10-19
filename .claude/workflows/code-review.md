# Code review

Engineering review of a diff. Department gates are `.claude/workflows/department-review.md`. This workflow does not replace them.

1. Read `.claude/rules/code-style.md`, `.claude/rules/api-conventions.md`, and `.claude/rules/testing-standard.md`.
2. Use `.claude/skills/code-review/SKILL.md`.
3. Check ownership: `Author` and copyright still say Duppins Technology / Odell Duppins.
4. Check booking rules stay on the server, not only in `assets/js/booking-manager.js`.
5. Return findings first, ordered by severity, using the skill's labels.

Then hand the same diff to `.claude/workflows/department-review.md`.
