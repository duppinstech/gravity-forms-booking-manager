# GF Booking Manager

WordPress plugin that adds a **Booking Manager** field to [Gravity Forms](https://www.gravityforms.com/). Visitors pick a date from an inline calendar and an available time slot; bookings are stored on the entry and can be used in notifications.

## Requirements

- WordPress 6.0+
- PHP 7.4+
- Gravity Forms (active license not required for development, but GF must be installed)

## Installation

### From GitHub (development)

1. Clone this repository into `wp-content/plugins/gravity-forms-booking-manager/`.
2. Activate **GF Booking Manager** under **Plugins**.

### From a release ZIP (production)

1. Download the `.zip` from [GitHub Releases](https://github.com/duppinstech/gravity-forms-booking-manager/releases).
2. In WordPress: **Plugins → Add New → Upload Plugin**, choose the zip, install, and activate.

The zip must contain a single top-level folder named `gravity-forms-booking-manager` (use the build script below).

## Form builder setup

1. Edit a form in Gravity Forms.
2. Under **Advanced Fields**, add **Booking Manager**.
3. Click the field and configure settings in the sidebar (General tab):
   - **Business hours** — per day: Open/Closed, with 30-minute open/close dropdowns. Use **Apply Monday to** for Tue–Fri or all days.
   - **Appointment length** — slot interval (15–120 minutes)
   - **Blocked times** — collapsible; add a time range for one weekday or for every day
   - **Blackout dates** — collapsible; pick whole dates on the calendar
   - **Layout** — Side by Side (50/50), Auto Responsive, or Stacked
   - **Time slot columns** and max height

Under the **Appearance** tab you can hide the field label or change label placement. The default label is **Schedule Appointment** (editable under General).

## Notifications and merge tags

Each booking field stores two inputs:

| Input | Entry key | Example merge tag |
|-------|-----------|-------------------|
| Appointment Date | `{fieldId}.1` | `{Appointment Date:3.1}` |
| Appointment Time | `{fieldId}.2` | `{Appointment Time:3.2}` |

Replace `3` with your field ID. You can also use the parent field merge tag for the combined value (e.g. `2026-06-15 at 14:30`).

In **Settings → Notifications**, use the merge tag picker and expand the Booking Manager field to insert date and time separately.

## Layout

- **Side by Side (50/50)** — calendar and slots share one row with equal columns (default).
- **Auto Responsive** — 50/50 until the field is narrow, then stacks.
- **Stacked** — calendar above slots.

After a date and time are chosen, a line under the field states that selection and says to click Submit. It does not submit the form by itself.

## Scheduling rules

On the field, under the appointment length:

- **Capacity per slot** — how many active entries may share one time. Default 1. Maximum 20.
- **Buffer** — minutes required between the end of one appointment and the start of the next. Default none.
- **Minimum notice** — how soon a visitor may book, measured from now. Default none.
- **How far ahead** — the last day someone may book. Default no limit. The year dropdown lists only the years inside that window. With no limit, it lists the first bookable year and the five years after it.

Hours use the timezone in **Settings → General**. The field does not set a second timezone.

A checkout lock holds the last open seat for about two minutes while the form finishes saving, so two people submitting together cannot both take it.

## Bookings

In WordPress admin, open **Forms → Bookings**. The list shows upcoming appointments from active entries (up to 200). Editors can reschedule, which checks the same rules as the public form, or cancel, which moves the entry to the trash and frees the seat. Visitors do not get a separate cancel link. They use whatever process the site owner already has for Gravity Forms entries.

## Privacy and terms

- [PRIVACY.md](PRIVACY.md) — what is stored, and a paragraph you can paste into a site policy.
- [COMMERCIAL.md](COMMERCIAL.md) — GPL rights and how a paid download works.
- [THIRD-PARTY-NOTICES.md](THIRD-PARTY-NOTICES.md) — Flatpickr.
- [CHANGELOG.md](CHANGELOG.md)

## Updates from GitHub

The plugin checks `https://github.com/duppinstech/gravity-forms-booking-manager/releases/latest`. A successful check is cached for 6 hours.

1. Create a [GitHub Release](https://docs.github.com/en/repositories/releasing-projects-on-github/managing-releases-in-a-repository) with a tag (e.g. `v0.2.0`).
2. Attach a zip built with the script below as a release **asset** (required — WordPress cannot use the auto-generated source zip reliably).

WordPress will show an update under **Dashboard → Plugins** when the release version is newer than the installed version.

## Building a release zip

From the repository root (PowerShell):

```powershell
.\.claude/scripts/build-release.ps1
```

Output: `release/gravity-forms-booking-manager.zip` (gitignored). Upload that file to the GitHub release.

## Development

```bash
git clone https://github.com/duppinstech/gravity-forms-booking-manager.git
cd gravity-forms-booking-manager
```

Slot rules (no WordPress required), from PowerShell in the repo root:

```powershell
docker run --rm -v "${PWD}:/app" -w /app php:7.4-cli php tests/run.php
```

Local WordPress, without Gravity Forms, from the repo root:

```powershell
docker compose up -d
```

Then open `http://localhost:8080`. Copy a licensed Gravity Forms plugin into the site before the booking field can load. Stop the stack with `docker compose down`.

Push to `main`; tag releases for production updates.

## Security

See [SECURITY.md](SECURITY.md) for reporting vulnerabilities.

## License

GPL-2.0-or-later — see [LICENSE](LICENSE).
