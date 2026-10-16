---
description: Add a rule, command, skill, agent, or workflow using this repo's Claude layout.
---

# /scaffold

Add one Claude project file. Follow the tree in `CLAUDE.md`. Do not create a second copy by hand. Edit the repo-root folder, then run `scripts/sync-claude-config.ps1`.

## Pick one

| Kind | Path | Shape |
| --- | --- | --- |
| Rule | `rules/<name>.md` | Frontmatter `paths`, then short rules for that kind of edit |
| Command | `commands/<name>.md` | Frontmatter `description`, then steps for a slash command |
| Skill | `skills/<name>/SKILL.md` | Frontmatter `name` and `description`, under 500 lines |
| Agent | `agents/<name>.md` | Frontmatter `name` and `description`, verdict format below |
| Workflow | `workflows/<name>.md` | Ordered steps and which agent runs each one |
| Memory | `memory/<name>.md` | Facts the next session must not rediscover |

## New department agent

Use this section order:

1. Ownership reminder: read `OWNERSHIP.md`. Author is Odell Duppins / Duppins Technology.
2. When the agent runs (after development, and where it sits in `workflows/department-review.md`).
3. What to inspect.
4. What it must not invent (price, license, legal promise, exploit steps).
5. Verdict: `PASS`, `PASS WITH NOTES`, or `BLOCK`.

Register the agent in `AGENTS.md` and in the department workflow. Security stays the gate after every stage.

## After adding the file

Run `scripts/sync-claude-config.ps1` so `.claude/agents`, `.claude/commands`, `.claude/skills`, and `.claude/rules` match the root folders.
