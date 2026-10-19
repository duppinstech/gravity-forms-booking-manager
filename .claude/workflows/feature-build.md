# Feature build

Use this when adding behavior. Bug fixes use `.claude/workflows/bug-fix.md`.

1. Read `OWNERSHIP.md`, `INTENT.md`, `PLAN.md`, and `.claude/memory/decisions.md`.
2. If the idea is outside `INTENT.md`, stop and ask Odell Duppins before writing code.
3. Invoke `.claude/agents/research.md` when the change touches slot math, Gravity Forms storage, or the updater.
4. Implement the smallest change that meets the outcome. Follow `.claude/rules/code-style.md` and `.claude/rules/api-conventions.md`.
5. Invoke `.claude/agents/test-writer.md`, or write the manual evidence in `.claude/rules/testing-standard.md`.
6. Run `.claude/workflows/department-review.md`.
7. Update `.claude/memory/progress.md` and check off the matching line in `PLAN.md` only after security's final verdict is `PASS` or `PASS WITH NOTES`.
