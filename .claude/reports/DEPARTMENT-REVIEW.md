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
- The public GitHub repository can still distribute the plugin beside a payment portal. See `.claude/reports/NEEDS-REVIEW.md`.

## Needs you

See `.claude/reports/NEEDS-REVIEW.md`.

1. Keep public GitHub Releases, or make the payment portal the only publish point.
2. Turn on GitHub private vulnerability reporting, and send an email if you want one listed.
3. Copy a licensed Gravity Forms plugin into the local site before the booking field can be clicked.

## Department review — 0.4.2

Date: 2026-10-18. Coordinator: main session. Diff: `3e80ffd..64617c6`, published as GitHub release `v0.4.2`. No department edited the repository. The privacy sentence below was applied after the verdicts.

No department returned `BLOCK`.

| Department | Agent | Verdict |
| --- | --- | --- |
| Security | [Security review](599b4a23-30b1-4e95-b070-f6942faf5f54) | PASS WITH NOTES |
| Quality | [QA review](7a2012fc-48b0-49f9-a8f9-3aaef7158c4c) | PASS WITH NOTES |
| Product | [Product review](98bd91d7-fbbd-4665-99c8-f7e600335a51) | PASS |
| Accessibility | [Accessibility review](73bf469e-27ff-4d34-ac02-e855c485fea2) | PASS WITH NOTES |
| Interface | [Interface review](b7590e3a-dfd9-4ade-859f-e0930453db3c) | PASS |
| Privacy | [Privacy review](8e56fc3d-822a-4696-9fee-3e6c6dc2570d) | PASS |
| Legal | [Legal review](56fd1c13-db72-4d9d-8e1b-20b384310dbb) | PASS |
| Finance | [Finance review](b09269b2-b4de-483b-b0d1-8ddabd4d0add) | PASS WITH NOTES |
| Support | [Support review](a1b02724-a5e9-474e-84d1-c45eb1c0c485) | PASS |
| Marketing | [Marketing review](f89d8809-57e1-4092-a04e-a7794ad44bd6) | PASS |
| Release | [Release review](8504d94a-e8b1-4611-99fa-0bbff44a7a63) | PASS |
| Security, final | [Security review](599b4a23-30b1-4e95-b070-f6942faf5f54) | PASS WITH NOTES |

## Notes kept open

- Readme links inside View details use `target="_blank"` without `rel="noopener noreferrer"`. That text comes from the plugin readme. The copied plugin-info class was not changed.
- The form preview did not draw the calendar in the local check, so layouts and merge tags were not re-checked in the browser. Slot files were not in this diff. Slot tests passed.
- Uninstall of `gfbm_github_release` and an update from Dashboard → Updates were not exercised.
- There are no screenshot images. WordPress 6.7.2 draws the banner in View details and does not draw the icon in that header. The API response includes both icon files.
- Booking limits from the 0.4.0 review are unchanged: the checkout lock does not cover every buffer overlap, and booking counts stop after 10,000 matching entries for one date.
