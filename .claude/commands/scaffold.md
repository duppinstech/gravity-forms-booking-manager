---
description: Add a rule, command, skill, agent, or workflow using this repo's Claude layout.
---

# /scaffold

Add one Claude project file under `.claude/`. Follow the tree in `CLAUDE.md`. Do not add a second copy at the repo root.

## Pick one

| Kind | Path | Shape |
| --- | --- | --- |
| Rule | `.claude/rules/<name>.md` | Frontmatter `paths`, then short rules for that kind of edit |
| Command | `.claude/commands/<name>.md` | Frontmatter `description`, then steps for a slash command |
| Skill | `.claude/skills/<name>/SKILL.md` | Frontmatter `name` and `description`, under 500 lines |
| Agent | `.claude/agents/<name>.md` | Frontmatter `name` and `description`, verdict format below |
| Workflow | `.claude/workflows/<name>.md` | Ordered steps and which agent runs each one |
| Memory | `.claude/memory/<name>.md` | Facts the next session must not rediscover |

## New department agent

Use this section order:

1. Ownership reminder: read `OWNERSHIP.md`. Author is Odell Duppins / Duppins Technology.
2. When the agent runs (after development, and where it sits in `.claude/workflows/department-review.md`).
3. What to inspect.
4. What it must not invent (price, license, legal promise, exploit steps).
5. Verdict: `PASS`, `PASS WITH NOTES`, or `BLOCK`.

Register the agent in `AGENTS.md` and in `.claude/workflows/department-review.md`. Security stays the gate after every stage. There is no second copy to sync.
