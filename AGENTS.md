# AGENTS.md

Team roster for Gravity Forms Booking Manager. The main session is engineering. It reads this file before delegating. Subagents run with isolated context. Invoke them by name.

Ownership for every agent: Odell Duppins / Duppins Technology. Read `OWNERSHIP.md`. Never credit a client, employer, or licensee as author or owner.

## Handoff

Give every agent the change summary, files touched, and the outcome from `INTENT.md` the change serves. Each agent returns a verdict: `PASS`, `PASS WITH NOTES`, or `BLOCK`, plus findings. A `BLOCK` stops the pipeline until it is fixed or Odell Duppins overrides it in writing. Agents do not edit the repo unless the user asked them to apply fixes.

## Engineering

| Agent | File | Job |
| --- | --- | --- |
| research | `agents/research.md` | Investigate the code and constraints before a change. |
| test-writer | `agents/test-writer.md` | Add or specify tests for the change. |

## Departments

Run these after development, in this order, using `workflows/department-review.md`. Security runs after every stage that edits files, and again at the end.

| Order | Department | File | Job |
| --- | --- | --- | --- |
| Gate | Security | `agents/security-reviewer.md` | Vulnerabilities, secrets, auth, data exposure. |
| 1 | Quality | `agents/qa-reviewer.md` | Behavior, regressions, test evidence. |
| 2 | Product | `agents/product-reviewer.md` | Fit with `INTENT.md` and `PLAN.md`. |
| 3 | Accessibility | `agents/accessibility-reviewer.md` | Keyboard, names, contrast, announcements. |
| 4 | Interface | `agents/ui-layout-reviewer.md` | Calendar, time slots, spacing, and the selection line. |
| 5 | Privacy | `agents/privacy-reviewer.md` | Booking data, disclosure, retention. |
| 6 | Legal | `agents/legal-reviewer.md` | License, copyright, disclaimers, notices. |
| 7 | Finance | `agents/finance-reviewer.md` | Pricing, packaging, paid vs free. |
| 8 | Support | `agents/support-reviewer.md` | Docs and admin copy a site owner can follow. |
| 9 | Marketing | `agents/marketing-reviewer.md` | Public claims and release-note language. |
| 10 | Release | `agents/release-reviewer.md` | Version, zip, updater, changelog. |

## How to invoke

- Feature or bugfix: `workflows/feature-build.md` or `workflows/bug-fix.md`, then the department workflow.
- Review only: `workflows/code-review.md`, then security at minimum.
- Slash commands: `/review`, `/deploy`, `/scaffold` (see `commands/`).

## Commands

No Composer or npm install. From the repository root:

- Local WordPress: `docker compose up -d`
- Lint: `docker run --rm -v "${PWD}:/app" -w /app php:7.4-cli sh -c "find . -name '*.php' -not -path './release/*' -print0 | xargs -0 -n1 php -l"`
- Test: `docker run --rm -v "${PWD}:/app" -w /app php:7.4-cli php tests/run.php`
- Build: `.\scripts\build-release.ps1`

CI runs the same lint and `php tests/run.php` after `shivammathur/setup-php`.

## Conventions

PHP 7.4. Four-space indent, with the opening brace on the next line for classes and methods. CSS files use two spaces. See `rules/code-style.md`. No formatter is installed. Do not reformat a file as a side effect of another edit.

## Do not touch

- `assets/vendor/flatpickr/`
- `release/` and any `*.zip`
- `.env`, `.env.*`, `CLAUDE.local.md`, `.claude/settings.local.json`
