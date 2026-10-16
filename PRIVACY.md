# Privacy notes for site owners

Gravity Forms Booking Manager stores the appointment date and time on the Gravity Forms entry (inputs `.1` and `.2`). It does not create a separate booking table. Deleting or exporting the entry in Gravity Forms deletes or exports the appointment.

The public form asks WordPress which times are still open. The answer is the list of open times. Someone who can use the form can infer which times are already full. The response does not include names, email addresses, or entry IDs.

Update checks call the GitHub API for this plugin's latest release. The request identifies the plugin and the WordPress version. It does not include the site address.

Business hours use the timezone in WordPress **Settings → General**.

Suggested paragraph for a site privacy policy:

> When you book a time on our form, we store the appointment date and time with your form entry. We use that information to keep the schedule and to send the notifications configured for the form. The booking tool does not keep a separate copy of your entry.

Uninstalling the plugin removes its temporary checkout locks. It does not delete Gravity Forms entries.
