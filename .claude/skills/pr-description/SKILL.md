---
name: pr-description
description: Writes pull request titles and bodies for Gravity Forms Booking Manager. Use when opening a PR, drafting a PR description, or summarizing a branch for review.
---

# PR description

Read `rules/pr.md` and `OWNERSHIP.md`.

## Title

`fix:`, `add:`, or `update:` plus the visitor or site-owner outcome. Example: `fix: reject a slot that was booked before the entry saved`.

## Body

```markdown
## Summary
- <one to three bullets on behavior>

## Ownership
Author remains Odell Duppins / Duppins Technology. License remains GPL-2.0-or-later.

## Test plan
- [ ] <manual or automated check>
- [ ] Department review: security, and any other department the diff affects

## Decisions needed
- <price, license text, or security contact — or "none">
```

Leave "Decisions needed" as `none` when the diff does not touch money, license, or disclosure. Do not write a price into the PR.
