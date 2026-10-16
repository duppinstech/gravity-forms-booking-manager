# Department review — 0.4.0

Date: 2026-10-14. Coordinator: main session. No department edited the repository. Verdicts below are from the department agents.

No department returned `BLOCK`.

| Department | Agent | Verdict |
| --- | --- | --- |
| Security | [Security review](a5a0a169-82fa-4ae4-99b3-286488c0d7a8) | PASS WITH NOTES |
| Security delta | same agent, second pass | PASS WITH NOTES |
| Quality | [QA review](e7d89e33-ca68-4db5-836a-eaf1db7a32e6) | PASS WITH NOTES |
| Product | [Product review](9f581cc8-dd23-4c70-a72a-fa760c9f757f) | PASS WITH NOTES |
| Accessibility | [Accessibility review](819815ad-56aa-46c2-892f-4959b0a2a4d7) | PASS WITH NOTES |
| Privacy | [Privacy review](c05bbb54-125f-4231-a56b-80065db4e4eb) | PASS |
| Legal | [Legal review](a8e25080-88dc-4cf0-bb0c-1f744a4160f2) | PASS |
| Finance | [Finance review](bf6cb57d-447f-46a8-9389-53fac4d04873) | PASS WITH NOTES |
| Support | [Support review](fd101e9d-8cf5-46b1-b369-c168445ff3fe) | PASS WITH NOTES |
| Marketing | [Marketing review](1a10e90a-280f-4c35-b9f1-44f53ffea1c4) | PASS |
| Release | [Release review](6d97f408-3d8f-47a1-a2c0-cdf16596f7b2) | PASS |

## What was applied after the first security pass

- Cancel requires `gravityforms_delete_entries`, and the Cancel button is shown only to people with that capability.
- The updater keeps a zip only from `https://` on `github.com`, `objects.githubusercontent.com`, or `release-assets.githubusercontent.com`.
- README now says blocked times are a range for one day or every day, and that capacity stops at 20.

The security delta closed the delete-capability finding and the zip-host finding for newly fetched releases. A release URL already cached for up to 12 hours is not rechecked until that cache expires.

## Notes kept open

- Checkout locks expire after 120 seconds and cover the selected time. Two overlapping times that only conflict because of a buffer can still both pass if they are submitted together. Validation still runs before the entry is written.
- Booking counts stop after 10,000 matching entries for one date.
- Automated tests cover slot rules, not a live Gravity Forms submit. Gravity Forms is not in the Docker image.
- Editor controls for business hours, blocked times, and blackout dates do not all have programmatic names. The public slot buttons do.
- The public GitHub repository can still distribute the plugin beside a payment portal. See `reports/NEEDS-REVIEW.md`.

## Needs you

See `reports/NEEDS-REVIEW.md`.

1. Keep public GitHub Releases, or make the payment portal the only publish point.
2. Turn on GitHub private vulnerability reporting, and send an email if you want one listed.
3. Copy a licensed Gravity Forms plugin into the local site before the booking field can be clicked.
