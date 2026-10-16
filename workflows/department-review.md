# Department review

Run this after engineering finishes a change, and again before a release. The main session coordinates. Each department returns `PASS`, `PASS WITH NOTES`, or `BLOCK`.

Security runs on the original diff, after every later stage that edits files, and once more at the end. A doc-only diff still gets a security pass aimed at secrets, credentials, and ownership. It does not need a full application review.

## Order

| Step | Agent | Question |
| --- | --- | --- |
| 1 | `agents/security-reviewer.md` | Can this diff expose data, skip a check, or leak a secret? |
| 2 | `agents/qa-reviewer.md` | What evidence shows the behavior works? |
| 3 | `agents/product-reviewer.md` | Does this match `INTENT.md` without scope creep? |
| 4 | `agents/accessibility-reviewer.md` | Can a keyboard and a screen reader use what changed? |
| 5 | `agents/privacy-reviewer.md` | Did booking data, disclosure, or the updater change? |
| 6 | `agents/legal-reviewer.md` | Do license, copyright, notices, and disclaimers still match `OWNERSHIP.md`? |
| 7 | `agents/finance-reviewer.md` | Did anyone state or imply a price Odell Duppins did not set? |
| 8 | `agents/support-reviewer.md` | Can a site owner follow the docs after this change? |
| 9 | `agents/marketing-reviewer.md` | Do public claims match what the plugin actually does? |
| 10 | `agents/release-reviewer.md` | If this ships, do version, zip, and changelog agree? |
| 11 | `agents/security-reviewer.md` | Final pass on the whole diff, including department edits. |

After each of steps 2 through 10, if that agent edited files, run `agents/security-reviewer.md` on those edits before the next department.

## Stop rules

- Any `BLOCK` stops the pipeline. Fix the finding or get a written override from Odell Duppins.
- Legal and finance never invent license text or a price to clear their own gate.
- Skip a department only when the diff cannot affect it, and write one sentence saying why. Do not skip security.

## Record

Append a short entry to `memory/progress.md`: date, change, verdict per department, open blocks.
