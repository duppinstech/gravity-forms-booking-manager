# Progress

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
