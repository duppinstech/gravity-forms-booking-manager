# Decisions

Settled choices. Change one only by editing this file in the same change that changes the code.

## Ownership and license

- Copyright owner is Odell Duppins, personally. Business name on the plugin is Duppins Technology.
- This repository has no outside client. Licensees under GPL-2.0-or-later are not owners.
- Distribution license is GPL-2.0-or-later. The full GPL-2.0 text ships in `LICENSE`.
- Flatpickr 4.6.13 ships under `assets/vendor/flatpickr/` with its MIT license.

## Commercial

- Decided by Odell Duppins on 2026-10-15. The plugin is not charged for today.
- The GitHub repository stays public. A payment portal, when one is added, is an optional checkout. It does not replace public releases.
- A future portal would sell a copy of this plugin. The buyer receives the plugin itself under GPL-2.0-or-later.
- No dollar amount is published in the repository.

## Product

- Decided by Odell Duppins on 2026-10-19. The public product name is GF Booking Manager. The folder, text domain `gravity-forms-booking-manager`, and GitHub repository stay unchanged. Gravity Forms is a trademark of Rocketgenius, Inc. Duppins Technology is not affiliated with Rocketgenius. The name is used only to say which form product the field runs on.
- The bookable unit is one Gravity Forms field of type `booking_manager`.
- Date and time are stored on the entry as inputs `.1` and `.2`, not in a custom table.
- Default capacity is 1. Capacity, buffer, minimum notice, and booking horizon are field settings.
- Hours use the WordPress site timezone from `current_time()`. The field does not have its own timezone.
- Availability is calculated in PHP. JavaScript displays slots from `admin-ajax.php`.
- Checkout locks use options named `gfbm_lock_*` and expire after 120 seconds.
- Cancel and reschedule for staff are on **Forms → Bookings**. Cancel trashes the entry. Visitors are not given a self-service cancel page.
- Entry data survives uninstall. Uninstall deletes lock options only.
- Updates ship as a zip asset on GitHub Releases, repo `duppinstech/gravity-forms-booking-manager`.
- Update requests identify the plugin folder. They do not send the site address or the WordPress version.
- Security reports go to support@duppinstech.com and to GitHub private vulnerability reporting.

## Testing

- `php tests/run.php` covers slot rules without WordPress.
- `docker-compose.yml` boots WordPress on port 8080. The licensed Gravity Forms package for local tests is `local/gravityforms-2.10.2.zip`. `local/` and `*.zip` are gitignored. Install that zip only into the local site, never into git or the release zip.
