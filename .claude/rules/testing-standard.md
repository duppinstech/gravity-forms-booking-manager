---
paths:
  - "**/*.php"
  - "**/*.js"
  - "tests/**/*"
---

# Testing standard

There is no PHPUnit or JavaScript test suite in this repo yet. Until Phase 1 of `PLAN.md` adds one, every behavior change still needs evidence.

## Minimum on every PHP change

- `php -l` on each touched PHP file. `.claude/hooks/validate-code.sh` does this when `php` is on the path.
- Say which booking rule you exercised: open day, closed day, blackout, blocked range, past time today, required field empty, slot already on an active entry.

## When automated tests exist

Cover at least:

- Slot generation inside open hours and duration, excluding blocked ranges.
- Dates in the past, blackout dates, and closed weekdays.
- Overlap: a slot that starts before a block and ends inside it is unavailable.
- Validation rejects a time that was not in `build_slots()` for that date.
- Booked times returned by `get_booked_slots()` are removed from the public list.

## Manual pass for UI changes

- Side by side, auto, and stacked layouts.
- A required Booking Manager field blocks submit until both date and time are set.
- Merge tags `{Field Label:ID.1}` and `{Field Label:ID.2}` resolve in a notification.
- Form editor saves hours, blocks, and blackouts and reloads them.

Record the pass or the gap in the PR using `.claude/commands/review.md`. A missing suite is a QA note, not a reason to skip the manual pass.
