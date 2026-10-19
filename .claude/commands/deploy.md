---
description: Build the release zip and list the GitHub release steps. Does not push or publish.
---

# /deploy

Prepare a release. Do not push, tag, or publish unless the user explicitly says to.

1. Run `.claude/agents/release-reviewer.md` on the tree. Stop on `BLOCK`.
2. Confirm the plugin header version and `GFBM_VERSION` match.
3. Confirm `.distignore` leaves out `.claude/`, `local/`, and `OWNERSHIP.md`, and that the built zip still contains the main plugin file, `includes/`, and `assets/`.
4. From the repo root in PowerShell, run `.\.claude/scripts/build-release.ps1`.
5. Report the zip path `release/gravity-forms-booking-manager.zip` and the remaining human steps:
   - Commit and push only if the user asked.
   - Tag `vX.Y.Z` to match `GFBM_VERSION`.
   - Create a GitHub Release and attach that zip as an asset. The updater ignores a release that has no `.zip` asset.
6. Remind the user that a successful update check is cached for 6 hours.

Author on the release stays Duppins Technology. See `OWNERSHIP.md`.
