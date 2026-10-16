# PLAN.md

Outstanding work to complete Gravity Forms Booking Manager. Outcomes are defined in `INTENT.md`. Ownership rules are in `OWNERSHIP.md`.

Phases that do not block each other can proceed together. Docker is available on this machine for PHP and for a local WordPress site.

Status key: `open`, `in progress`, `done`, `needs review`.

## Phase 0 — SDLC scaffolding

Status: done.

- [x] Claude project tree (`CLAUDE.md`, `AGENTS.md`, `rules/`, `agents/`, `workflows/`, `hooks/`).
- [x] `CLAUDE.local.md` notes Docker PHP and `http://localhost:8080`.
- [x] `scripts/sync-claude-config.ps1` has been run.
- [ ] Department review of the release diff is the report in `reports/`.

## Phase 1 — A booking sticks

Status: done, except the security email in **Needs review**.

- [x] `php tests/run.php` covers slot generation, blackout, past times, notice, horizon, buffer, capacity, and the checkout lock. Run it with Docker image `php:7.4-cli`.
- [x] Checkout lock between validation and entry creation (`includes/class-gfbm-slot-lock.php`).
- [x] `get_booking_counts()` pages 200 entries at a time, up to 10,000 entries for one date.
- [x] Flatpickr 4.6.13 is vendored in `assets/vendor/flatpickr/`.
- [x] Email address in `SECURITY.md`: support@duppinstech.com. GitHub private advisories remain available.

## Phase 2 — Scheduling rules

Status: done.

- [x] Capacity per slot, default 1.
- [x] Buffer after each appointment, default none.
- [x] Minimum notice and a maximum days-ahead window, both default off so existing forms stay the same.
- [x] Business hours use the WordPress site timezone. The field does not have its own timezone.
- [x] **Forms → Bookings** lists upcoming appointments.
- [x] Staff reschedule and cancel on that screen. Cancel trashes the entry. Visitors do not get a separate cancel page.

## Phase 3 — Usable and shippable

Status: done.

- [x] Slot buttons use `aria-pressed`, a focus outline, and weight as well as color. Messages use `textContent`.
- [x] `languages/gravity-forms-booking-manager.pot` from `scripts/make-pot.php`.
- [x] `uninstall.php` deletes `gfbm_lock_*` options and leaves Gravity Forms entries in place.
- [x] `CHANGELOG.md` matches `GFBM_VERSION` 0.4.0.
- [x] `.github/workflows/ci.yml` runs `php -l` and `php tests/run.php` on PHP 7.4 and 8.2.

## Phase 4 — Legal, privacy, and finance

Status: done. Decided 2026-10-15: no charge today, public repository stays, a future portal is an optional checkout.

- [x] Full GPL-2.0 text is in `LICENSE`. Copyright stays Duppins Technology.
- [x] `THIRD-PARTY-NOTICES.md` and the Flatpickr MIT file.
- [x] `PRIVACY.md` documents open times and the updater. The updater no longer sends the site URL.
- [x] Commercial model is in `COMMERCIAL.md`: no charge today. A later portal is an optional checkout for a copy of the plugin. No price is in the repo.
- [x] Public GitHub Releases stay available. The portal does not replace them.

## Phase 5 — Release of 0.4.0

Status: done. Published 2026-10-16 as GitHub release `v0.4.0` on `duppinstech/gravity-forms-booking-manager`.

- [x] Version `0.4.0` in the plugin header and `GFBM_VERSION`.
- [x] `scripts/build-release.ps1` adds the runtime docs and `uninstall.php`. SDLC files and `OWNERSHIP.md` stay out of the zip.
- [x] The installable zip is attached to the `v0.4.0` GitHub Release.
- [x] Department verdicts recorded in `reports/DEPARTMENT-REVIEW.md` and `memory/progress.md`.

## Field layout 0.4.1

Status: on main. Not a GitHub Release yet, so the updater still serves 0.4.0.

- [x] Compact calendar, year dropdown beside the month, slot spacing, and a selection line.
- [ ] GitHub release `v0.4.1` when asked.

## Testing with Docker

- Slot tests: `docker run --rm -v "${PWD}:/app" -w /app php:7.4-cli php tests/run.php`
- WordPress on port 8080: `docker compose up -d`. Gravity Forms is not in the image. Copy a licensed copy into the site before the field will load.
- Stop WordPress with `docker compose down`.

## Working agreement

A change that touches license, price, or personal data updates `memory/decisions.md`. Security reviews the diff after implementation and after any department that edits files. Do not publish a price or an email that Odell Duppins has not supplied.
