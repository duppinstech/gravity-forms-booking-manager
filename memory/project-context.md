# Project context

Gravity Forms Booking Manager, version 0.4.0. Owner: Odell Duppins / Duppins Technology (https://duppinstech.com). Repository: https://github.com/oduppinsjr/gravity-forms-booking-manager. No outside client. See `OWNERSHIP.md`.

## Runtime

- Bootstrap: `gravity-forms-booking-manager.php` loads on `gform_loaded`.
- Field class: `includes/class-gf-booking-manager-field.php`.
- Slots, AJAX, validation: `includes/class-gf-booking-manager.php`.
- Settings normalization: `includes/class-gfbm-helpers.php`.
- Form editor UI: `includes/class-gfbm-form-editor.php` and `assets/js/booking-manager-editor.js`.
- Public UI: `assets/js/booking-manager.js` and `assets/css/booking-manager.css`.
- Updates: `includes/class-gfbm-updater.php`.

## Booking model

One active Gravity Forms entry occupies a date and time for that form and field. Hours default to Monday–Friday 09:00–17:00. Duration options are 15, 30, 45, 60, 90, and 120 minutes. The site timezone comes from WordPress `current_time()`.

Flatpickr is loaded from jsDelivr. The public scripts pin 4.6.13. The form editor does not pin a version.

## Distribution

GPL-2.0-or-later. Release zip is built by `scripts/build-release.ps1` and attached to a GitHub Release. The updater caches the latest release for 12 hours.

## SDLC

Claude project files at the repo root are the source of truth. `.claude/` holds settings and the synced copies Claude Code loads. Personal notes stay in `CLAUDE.local.md`.
