@AGENTS.md

# Gravity Forms Booking Manager

WordPress plugin that adds a Booking Manager field to Gravity Forms. Version is `GFBM_VERSION` in `gravity-forms-booking-manager.php` (0.4.2).

Author and copyright owner: Odell Duppins / Duppins Technology (https://duppinstech.com). Read `OWNERSHIP.md` before any copyright, license, credit, or pricing edit.

## Stack

- PHP 7.4+, WordPress 6.0+, Gravity Forms
- Field, validation, and slots: `includes/`
- Front end: `assets/js/booking-manager.js`, `assets/css/booking-manager.css`
- Calendar library: Flatpickr 4.6.13 in `assets/vendor/flatpickr`
- Release zip: `scripts/build-release.ps1`
- Updates: GitHub Releases via `includes/class-gfbm-updater.php`
- Distribution license: GPL-2.0-or-later

## What to read

| File | When |
| --- | --- |
| `OWNERSHIP.md` | Any authorship, license, or credit change |
| `INTENT.md` | Deciding whether work belongs in this product |
| `PLAN.md` | Picking up outstanding work |
| `AGENTS.md` | Before delegating |
| `memory/project-context.md` | Architecture and constraints |
| `memory/decisions.md` | Choices already made |
| `memory/progress.md` | Where the last session stopped |
| `rules/` | The file that matches the edit |
| `workflows/department-review.md` | After implementation, before calling work done |
| `mcp.json` | Shared MCP servers only. No tokens. See `memory/mcp.md` |

## Delegation

The main session writes code. After the code change is finished, run `workflows/department-review.md`. Security reviews every stage. Department agents report; they edit files only when asked to apply a fix.

Claude Code loads copies under `.claude/agents`, `.claude/commands`, `.claude/skills`, and `.claude/rules`. Edit the matching folder at the repo root, then run `scripts/sync-claude-config.ps1`.

## Hard rules

- Copyright and `Author` stay Odell Duppins / Duppins Technology.
- GPL-2.0-or-later stays the distribution license unless Odell Duppins changes it in writing.
- Do not invent a price, support term, or legal promise.
- Every PHP file starts with an `ABSPATH` guard.
- Escape output. Sanitize input. Check a nonce on AJAX. Re-check booking rules on the server.
- Personal overrides belong in `CLAUDE.local.md` and `.claude/settings.local.json`. Both are gitignored.
- Do not commit `.env`, credentials, or `release/` zips.
- Keep this file under 200 lines. Put detail in `rules/`, `workflows/`, and `memory/`.

## Current behavior

Visitors pick a date and an open time. A line under the field states that selection. One active entry holds that slot for that form and field. Business hours, duration, blocked times, blackout dates, and layout are field settings. Date and time are entry inputs `{id}.1` and `{id}.2`.
