# INTENT.md

What this product is for, and what "complete" means. Task order lives in `PLAN.md`.

Owner: Odell Duppins / Duppins Technology. See `OWNERSHIP.md`.

## Product

GF Booking Manager lets a site owner drop one field on a Gravity Forms form so a visitor can book a date and an available time. Duppins Technology is not affiliated with Rocketgenius. The booking is stored on the entry and can be used in notifications.

The plugin is distributed under GPL-2.0-or-later from GitHub. Installing it does not transfer ownership.

## Done today (0.3.7)

- A Booking Manager field in the Advanced Fields group.
- Per-day open and close hours, appointment length, blocked times, and blackout dates.
- Layout, slot columns, and slot list height.
- Server-side checks for required values, closed days, blackouts, past times, and slots already stored on active entries.
- Merge tags for date, time, and the combined value.
- A GitHub release updater and `.claude/scripts/build-release.ps1`.
- `README.md`, `SECURITY.md`, and a GPL license notice.

## Complete means

A site owner can trust the slot they see is still free when the form submits. They can express the scheduling rules a small business actually uses. They can find bookings, understand the license, and know what the plugin costs. A visitor can book with a keyboard. Duppins Technology can ship a release without guessing which review was skipped.

## Outstanding outcomes

1. **A booking sticks.** Two people cannot take the same slot, and a busy day cannot hide bookings past the current entry query limit.
2. **Scheduling rules match the business.** Capacity, buffers, minimum notice, how far ahead someone may book, and the site timezone are explicit settings, or they are consciously left out.
3. **Operators can see the book.** Bookings are findable from the field, not only by searching entries by hand. Cancel and reschedule are either supported or documented as out of scope.
4. **The field is usable.** Calendar and time slots work from the keyboard and expose their state to assistive tech.
5. **Trust paperwork is true.** Tests cover slot rules. Flatpickr is pinned and attributed. `SECURITY.md` has a real contact. The GPL text shipped with the plugin matches the header. Privacy guidance exists for site owners. No public page states a price Odell Duppins has not set.
6. **Releases are repeatable.** Version, changelog, zip, and GitHub asset stay in lockstep. Department review in `.claude/workflows/department-review.md` has run on the release diff.

## Out of scope unless `INTENT.md` changes

Payments, deposits, staff calendars, multiple locations, SMS, and a marketplace listing. Gravity Forms remains a separate product. This plugin does not claim partnership with Gravity Forms.

## Decision rights

Odell Duppins decides license changes, a price, a security contact address, and any wording that creates a legal or financial promise. Agents may flag a gap. They may not fill it with an invented term.
