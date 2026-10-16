# Progress

## 2026-10-16

- Added `agents/ui-layout-reviewer.md` to the department order after accessibility.
- Field layout 0.4.1 is on main. It is not a GitHub Release yet, so the updater still serves 0.4.0. Calendar stays a compact month, the year dropdown sits beside the month, time slots keep their own style, and a line under the field states the date and time.
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
- Department review is in `reports/DEPARTMENT-REVIEW.md`. No `BLOCK`.
- Still waiting on Odell: public Releases versus a payment portal, and a security contact email. See `reports/NEEDS-REVIEW.md`.
