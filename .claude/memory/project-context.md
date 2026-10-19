# Project context

GF Booking Manager, version 0.4.3. Public name is GF Booking Manager. Folder and text domain stay `gravity-forms-booking-manager`. Owner: Odell Duppins / Duppins Technology (https://duppinstech.com). Repository: https://github.com/duppinstech/gravity-forms-booking-manager. No outside client. See `OWNERSHIP.md`. Duppins Technology is not affiliated with Rocketgenius.

## Runtime

- Bootstrap: `gravity-forms-booking-manager.php` loads on `gform_loaded`.
- Field class: `includes/class-gf-booking-manager-field.php`.
- Slots, AJAX, validation: `includes/class-gf-booking-manager.php`.
- Settings normalization: `includes/class-gfbm-helpers.php`.
- Form editor UI: `includes/class-gfbm-form-editor.php` and `assets/js/booking-manager-editor.js`.
- Public UI: `assets/js/booking-manager.js` and `assets/css/booking-manager.css`.
- Updates: `includes/class-gfbm-github-updater.php`.

## Booking model

One active Gravity Forms entry occupies a date and time for that form and field. Hours default to Monday–Friday 09:00–17:00. Duration options are 15, 30, 45, 60, 90, and 120 minutes. The site timezone comes from WordPress `current_time()`.

Flatpickr 4.6.13 is the vendored copy in `assets/vendor/flatpickr/`. The public scripts and the form editor both enqueue those files.

## Distribution

GPL-2.0-or-later. Release zip is built by `.claude/scripts/build-release.ps1` and attached to a GitHub Release. The updater caches a successful release check for 6 hours.

## SDLC

Agents, commands, skills, rules, workflows, hooks, reports, scripts, and memory live only under `.claude/`. Personal notes stay in `CLAUDE.local.md`. `.distignore` is what the release build leaves out. `.claude/` and `local/` are on that list.
