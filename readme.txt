=== Gravity Forms Booking Manager ===
Contributors: duppinstech
Tags: booking, appointments, calendar, scheduling
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 0.4.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds an inline booking calendar and available time slots field to Gravity Forms.

== Description ==

Visitors pick a date from an inline calendar and an open time. The booking is stored on the Gravity Forms entry and can be used in notifications.

The field checks the rules on the server when the form is submitted. One active entry holds that slot for that form and field. A checkout lock holds the last open seat for about two minutes while the form finishes saving, so two people submitting together cannot both take it.

Hours use the timezone in Settings → General. The field does not set a second timezone.

= What you can set on the field =

* Business hours for each day, open or closed, in 30-minute steps
* Appointment length, from 15 to 120 minutes
* Blocked times for one weekday or every day
* Blackout dates
* Layout: side by side, auto responsive, or stacked
* Time slot columns and list height
* Capacity per slot (default 1, maximum 20), buffer between appointments, minimum notice, and how far ahead a visitor may book

= Notifications =

Each booking field stores two inputs:

* Appointment Date, entry key `{fieldId}.1`
* Appointment Time, entry key `{fieldId}.2`

In Settings → Notifications, open the merge tag picker and expand the Booking Manager field. The parent field merge tag is the combined value, for example `2026-06-15 at 14:30`.

= Bookings =

In WordPress admin, open Forms → Bookings. The list shows upcoming appointments from active entries (up to 200). Editors can reschedule, which checks the same rules as the public form. Cancel moves the entry to the trash and frees the seat. Visitors do not get a separate cancel link.

= Support =

Support: [support@duppinstech.com](mailto:support@duppinstech.com). Made by [Duppins Technology](https://duppinstech.com).

== Installation ==

1. Download `gravity-forms-booking-manager.zip` from the latest release.
2. In wp-admin, go to Plugins → Add New → Upload Plugin, choose the zip, and click Install Now.
3. Activate the plugin. Gravity Forms must already be active.
4. Edit a form. Under Advanced Fields, add Booking Manager.
5. Open the field and set the hours, appointment length, and layout.

= Updates =

New releases appear under Dashboard → Updates.

== Frequently Asked Questions ==

= Does this plugin include Gravity Forms? =

No. Gravity Forms is a separate product. Install and activate it before this field can load.

= Where is the booking saved? =

On the Gravity Forms entry, as the date input and the time input for that field.

= Which timezone is used? =

The timezone in Settings → General.

= Can a visitor cancel from the form? =

No. Cancel and reschedule are on Forms → Bookings. Cancel sends the entry to the trash, which frees the slot.

= Is there a charge? =

There is no charge for this plugin today. The public GitHub repository and its releases are the distribution channel.

== Screenshots ==

== Changelog ==

= 0.4.1 =
* The calendar stays a compact month. The selected day is one cell, and the month and year sit together in the header.
* The year is a dropdown of the years the field allows. A short booking window still only lists those years.
* Time slots keep their own spacing and button style when a theme styles buttons.
* A line under the field states the date and time and says to click Submit.

= 0.4.0 =
* Slot checks page through matching entries instead of stopping at 1,000.
* A short checkout lock stops two people from taking the last seat in the same request.
* Field settings for capacity, buffer, minimum notice, and how far ahead a visitor can book.
* Business hours follow the WordPress site timezone.
* Bookings screen under Gravity Forms to reschedule or cancel. Cancel trashes the entry.
* Flatpickr 4.6.13 is included with the plugin.
* Update checks no longer send the site URL.
* Full GPL-2.0 license text, Flatpickr notice, privacy notes, and commercial terms.
* Slot buttons expose selected state to assistive tech.

= 0.3.7 =
* Booking Manager field, business hours, blocked times, blackout dates, layouts, merge tags, and GitHub updates.

== Upgrade Notice ==

= 0.4.1 =
The calendar month stays compact, the year list follows the booking window, and the field states the chosen date and time.
