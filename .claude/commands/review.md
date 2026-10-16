---
description: Review the current change, then run the department pipeline starting with security.
---

# /review

Review the current diff, then run `workflows/department-review.md`.

1. Read `OWNERSHIP.md`, `INTENT.md`, and `rules/pr.md`.
2. Summarize the diff in a few sentences: behavior, files, risk.
3. Run `workflows/code-review.md`.
4. Run `workflows/department-review.md` from the security gate through release.
5. Post one combined report. A single `BLOCK` fails the review.

Do not invent a price, a license change, or a security contact. Flag those as decisions for Odell Duppins.
