---
paths:
  - "includes/**/*.php"
  - "assets/js/**/*.js"
  - "gravity-forms-booking-manager.php"
---

# API conventions

Gravity Forms is the form runtime. This plugin adds a field, an AJAX slot lookup, and validation. It does not replace Gravity Forms storage.

## Field

- Type: `booking_manager`.
- Settings live on the field object: `gfbmBusinessHours`, `gfbmDuration`, `gfbmBlockedTimes`, `gfbmBlackoutDates`, `gfbmLayout`, `gfbmSlotColumns`, `gfbmSlotMaxHeight`.
- JSON settings are stored as strings and normalized through `GFBM_Helpers`.
- Entry inputs: `{fieldId}.1` appointment date (`Y-m-d`), `{fieldId}.2` appointment time (`H:i`).

## AJAX

- Action: `gfbm_get_slots` (logged-in and `nopriv`).
- Nonce action: `gfbm_nonce`, sent as `nonce`.
- Required POST fields: `formId`, `fieldId`, `date`.
- Read slots with `check_ajax_referer`. Sanitize integers with `absint` and the date with `sanitize_text_field`, then validate the date before using it.
- Availability is decided in PHP (`is_date_available`, `build_slots`, `get_booked_slots`). The browser only displays the result.

## New endpoints

A new AJAX or REST route needs a nonce or capability check, sanitized input, and the same business rule enforced again when the entry is saved. Do not trust a slot list that was rendered earlier. Admin-only actions check `current_user_can` for the Gravity Forms capability that matches the screen.

## Updates

`GFBM_Updater` reads `https://api.github.com/repos/oduppinsjr/gravity-forms-booking-manager/releases/latest`. The installable package is a `.zip` release asset. Do not point the updater at the GitHub auto-generated source zip.
