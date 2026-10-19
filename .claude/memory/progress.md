# Progress

## 2026-10-19 — release zip and local Gravity Forms

- The stale plugin zip at the repo root is deleted. The build writes only `release/gravity-forms-booking-manager.zip`.
- `gravityforms-2.10.2.zip` moved to `local/`. `local/` is gitignored and listed in `.distignore`.
- `.claude/scripts/build-release.ps1` now packs from `.distignore` instead of a separate allow-list. Security PASS WITH NOTES. `.env` is on `.distignore` so a local env file cannot be packed.

## 2026-10-19 — one Claude tree

- Agents, commands, skills, rules, workflows, hooks, reports, and scripts now live only under `.claude/`. The root copies and `sync-claude-config.ps1` are gone. The release zip allow-list still leaves `.claude/` out.

## 2026-10-19 — name and memory

- Odell Duppins set the public name to GF Booking Manager. The folder and text domain stay `gravity-forms-booking-manager`. Recorded in `.claude/memory/decisions.md`.
- Session notes moved from `memory/` to `.claude/memory/`. The release allow-list does not copy `.claude/`.
- Department review, no BLOCK. Security PASS WITH NOTES. Quality PASS WITH NOTES. Product PASS. Accessibility PASS WITH NOTES. Interface PASS WITH NOTES. Privacy PASS. Legal PASS WITH NOTES. Finance PASS WITH NOTES. Support PASS. Marketing PASS. Release PASS WITH NOTES. Final security PASS WITH NOTES.
- `LICENSE` program title is GF Booking Manager. Version 0.4.3 is the release that ships that name.

## 2026-10-19

- Department meeting on `64617c6..HEAD` and on the product title. No department edited files. No BLOCK.
- Legal PASS WITH NOTES: the title names Gravity Forms as the separate host product and does not claim a partnership or an official add-on. Whether `Plugin Name: Gravity Forms Booking Manager` stays is Odell Duppins's decision. `OWNERSHIP.md` has two disagreeing sentences about who `Author` headers name.
- Security PASS. Quality PASS. Product PASS WITH NOTES. Accessibility PASS. Interface PASS. Privacy PASS. Finance PASS WITH NOTES. Support PASS. Marketing PASS. Release PASS WITH NOTES.

## 2026-10-18

- Department review of the published v0.4.2 diff (`3e80ffd..64617c6`) is in `.claude/reports/DEPARTMENT-REVIEW.md`. No BLOCK.
- Security PASS WITH NOTES. Quality PASS WITH NOTES. Product PASS. Accessibility PASS WITH NOTES. Interface PASS. Privacy PASS. Legal PASS. Finance PASS WITH NOTES. Support PASS. Marketing PASS. Release PASS. Final security PASS WITH NOTES.
- `PRIVACY.md` now says the update request identifies the plugin folder and does not send the site address or the WordPress version.
- `OWNERSHIP.md` names Odell Duppins Jr as the copyright holder. The plugin Author header stays Duppins Technology.

## 2026-10-17

- SDLC baseline on `chore/sdlc-baseline`. Docs, GitHub config, and Claude settings only. No application code.
- Lint: `php -l` passed on every PHP file. Slot tests: 11 passed on PHP 7.4 in Docker.
- Security PASS WITH NOTES: no secrets, auth, or booking paths in the diff. `release.yml` can create a GitHub release when a `v*` tag is pushed. It does not deploy WordPress.
- Quality skipped: booking behavior did not change. The Docker lint and slot tests above are the check.
- Product skipped: no change to a booking outcome in `INTENT.md`.
- Accessibility skipped: no markup or behavior change.
- Interface skipped: `.editorconfig` records the indent already in the files and does not reindent them.
- Privacy skipped: entries, AJAX slot data, and the updater were not touched.
- Legal PASS WITH NOTES: `LICENSE` and copyright headers were not edited. `CONTRIBUTING.md` repeats the existing ownership rule and adds no terms.
- Finance skipped: no price.
- Support PASS: the bug template asks for field settings and versions. The feature template asks for an outcome. No support term was added.
- Marketing skipped: changelog bullets were not rewritten.
- Release PASS WITH NOTES: no version bump. The new workflow builds `.claude/scripts/build-release.ps1` on a version tag.
- Final security PASS. No BLOCK.

## 2026-10-16

- Added `.claude/agents/ui-layout-reviewer.md` to the department order after accessibility.
- Field layout 0.4.1 is published as GitHub release `v0.4.1` with `gravity-forms-booking-manager.zip`. Calendar stays a compact month, the year dropdown sits beside the month, time slots keep their own style, and a line under the field states the date and time.
- Local check on http://localhost:8080/?page_id=4: selected day 39 by 39 px, November previous arrow does not cover the month, slots sit in the right column, summary states Monday, November 2, 2026 at 9:00 AM.
- Verdicts: security PASS, quality covered by that browser check, product PASS WITH NOTES, accessibility PASS WITH NOTES, interface PASS, privacy skipped (no new data), legal skipped (no license change), finance skipped (no price), support PASS WITH NOTES, marketing skipped (changelog matches the field), release PASS WITH NOTES. No BLOCK. The selection line is `aria-live="polite"`. With no booking limit, the year list is six years, and the README says so.

## 2026-10-15

- Commercial decision: no charge today. The repository stays public. A future payment portal is an optional checkout.
- Security contact is support@duppinstech.com.
- Gravity Forms 2.10.2 is installed only in the local Docker site from a gitignored zip in the repo root. The booking field rendered on http://localhost:8080/?page_id=4 and Monday slots loaded.

## 2026-10-14

- Shipped plugin version 0.4.0 in the working tree. Not tagged or published.
- Slot tests: 11 passed on PHP 7.4 in Docker (`php tests/run.php`). `php -l` passed on every PHP file.
- WordPress 6.7 is running at http://localhost:8080 via Docker Compose. The plugin activated as 0.4.0. Gravity Forms is not installed, so the field does not load yet. Stop the stack with `docker compose down`.
- Department review is in `.claude/reports/DEPARTMENT-REVIEW.md`. No `BLOCK`.
- Still waiting on Odell: public Releases versus a payment portal, and a security contact email. See `.claude/reports/NEEDS-REVIEW.md`.
