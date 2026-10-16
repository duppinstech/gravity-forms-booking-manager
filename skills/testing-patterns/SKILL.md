---
name: testing-patterns
description: Defines how to test Gravity Forms Booking Manager slot rules, validation, and the form editor. Use when adding tests, writing a manual test pass, or checking a booking bugfix.
---

# Testing patterns

Read `rules/testing-standard.md`. The repo has no test runner yet. Prefer a failing example before a fix when you are changing slot math.

## Slot cases

Use a field with Monday 09:00–12:00, duration 30, and one block 10:00–11:00.

| Case | Expect |
| --- | --- |
| Monday | 09:00, 09:30, 11:00, 11:30 |
| Sunday | no slots |
| Blackout on that Monday | no slots |
| Today, with the site clock past 09:00 | those past slots omitted |
| Active entry at 11:00 | 11:00 omitted from `gfbm_get_slots` and rejected by validation |

## Validation cases

- Required field with only a date fails.
- A posted time that is not in `build_slots()` for that date fails.
- An optional field with both inputs empty passes.

## Where the code lives

- `GF_Booking_Manager::build_slots()`
- `GF_Booking_Manager::is_date_available()`
- `GF_Booking_Manager::validate_booking()`
- `GFBM_Helpers::normalize_business_hours()` and `normalize_blocked_times()`

Until PHPUnit is added, record these cases as a manual pass in the review. Do not claim an automated suite ran.
