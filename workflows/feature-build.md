# Feature build

Use this when adding behavior. Bug fixes use `workflows/bug-fix.md`.

1. Read `OWNERSHIP.md`, `INTENT.md`, `PLAN.md`, and `memory/decisions.md`.
2. If the idea is outside `INTENT.md`, stop and ask Odell Duppins before writing code.
3. Invoke `agents/research.md` when the change touches slot math, Gravity Forms storage, or the updater.
4. Implement the smallest change that meets the outcome. Follow `rules/code-style.md` and `rules/api-conventions.md`.
5. Invoke `agents/test-writer.md`, or write the manual evidence in `rules/testing-standard.md`.
6. Run `workflows/department-review.md`.
7. Update `memory/progress.md` and check off the matching line in `PLAN.md` only after security's final verdict is `PASS` or `PASS WITH NOTES`.
