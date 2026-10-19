@AGENTS.md

# GF Booking Manager

WordPress plugin that adds a Booking Manager field to a Gravity Forms form. The public name is GF Booking Manager. Duppins Technology is not affiliated with Rocketgenius. Version is `GFBM_VERSION` in `gravity-forms-booking-manager.php` (0.4.3).

Author and copyright owner: Odell Duppins / Duppins Technology (https://duppinstech.com). Read `OWNERSHIP.md` before any copyright, license, credit, or pricing edit.

## Stack

- PHP 7.4+, WordPress 6.0+, Gravity Forms
- Field, validation, and slots: `includes/`
- Front end: `assets/js/booking-manager.js`, `assets/css/booking-manager.css`
- Calendar library: Flatpickr 4.6.13 in `assets/vendor/flatpickr`
- Release zip: `.claude/scripts/build-release.ps1`
- Updates: GitHub Releases via `includes/class-gfbm-github-updater.php`
- Distribution license: GPL-2.0-or-later

## What to read

| File | When |
| --- | --- |
| `OWNERSHIP.md` | Any authorship, license, or credit change |
| `INTENT.md` | Deciding whether work belongs in this product |
| `PLAN.md` | Picking up outstanding work |
| `AGENTS.md` | Before delegating |
| `.claude/memory/project-context.md` | Architecture and constraints |
| `.claude/memory/decisions.md` | Choices already made |
| `.claude/memory/progress.md` | Where the last session stopped |
| `.claude/rules/` | The file that matches the edit |
| `.claude/workflows/department-review.md` | After implementation, before calling work done |
| `mcp.json` | Shared MCP servers only. No tokens. See `.claude/memory/mcp.md` |

## Delegation

The main session writes code. After the code change is finished, run `.claude/workflows/department-review.md`. Security reviews every stage. Department agents report; they edit files only when asked to apply a fix.

Agents, commands, skills, rules, workflows, hooks, reports, and scripts live only under `.claude/`. Edit those files there. Do not add a second copy at the repo root. `.claude/` is not part of the release zip.

## Hard rules

- Copyright and `Author` stay Odell Duppins / Duppins Technology.
- GPL-2.0-or-later stays the distribution license unless Odell Duppins changes it in writing.
- Do not invent a price, support term, or legal promise.
- Every PHP file starts with an `ABSPATH` guard.
- Escape output. Sanitize input. Check a nonce on AJAX. Re-check booking rules on the server.
- Personal overrides belong in `CLAUDE.local.md` and `.claude/settings.local.json`. Both are gitignored.
- Do not commit `.env`, credentials, `release/` zips, or `local/`. The release zip is built from `.distignore` into `release/`.
- Keep this file under 200 lines. Put detail in `.claude/rules/`, `.claude/workflows/`, and `.claude/memory/`. Memory stays in `.claude/memory/` and is not part of the release zip.

## Current behavior

Visitors pick a date and an open time. A line under the field states that selection. One active entry holds that slot for that form and field. Business hours, duration, blocked times, blackout dates, and layout are field settings. Date and time are entry inputs `{id}.1` and `{id}.2`.
