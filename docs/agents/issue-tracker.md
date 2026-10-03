# Issue tracker: GitHub

Issues and specs for this repo live in GitHub Issues for `Sunji007/student-discipline-system`. Use the `gh` CLI for all operations, from this repository checkout.

## Conventions

- **Create an issue**: `gh issue create --repo Sunji007/student-discipline-system --title "..." --body-file <path>`.
- **Read an issue**: `gh issue view <number> --repo Sunji007/student-discipline-system --comments`. For structured output, use `--json number,title,body,labels,comments`.
- **List issues**: `gh issue list --repo Sunji007/student-discipline-system --state open --json number,title,body,labels,comments`, with appropriate `--label` and `--state` filters.
- **Comment on an issue**: `gh issue comment <number> --repo Sunji007/student-discipline-system --body-file <path>`.
- **Apply / remove labels**: `gh issue edit <number> --repo Sunji007/student-discipline-system --add-label "..."` / `--remove-label "..."`.
- **Close**: `gh issue close <number> --repo Sunji007/student-discipline-system`. If a closing explanation is needed, post it as a comment using `--body-file` first.
- For multiline bodies and comments, write the exact Markdown to a temporary UTF-8 file and pass `--body-file`, preserving newlines.
- Use the label strings defined in `docs/agents/triage-labels.md`.

Infer the repo from `git remote -v`; `gh` does this automatically when run inside a clone. The explicit repository arguments above ensure the intended repository is used.

## Pull requests as a triage surface

**PRs as a request surface: no.** Set to `yes` if this repo treats external PRs as feature requests; `/triage` reads this flag.

When set to `yes`, PRs run through the same labels and states as issues, using the `gh pr` equivalents:

- **Read a PR**: `gh pr view <number> --comments` and `gh pr diff <number>`.
- **List external PRs for triage**: `gh pr list --state open --json number,title,body,labels,author,authorAssociation,comments`; keep only authors with `CONTRIBUTOR`, `FIRST_TIME_CONTRIBUTOR`, or `NONE` association.
- **Comment / label / close**: `gh pr comment --body-file <path>`, `gh pr edit --add-label` / `--remove-label`, and `gh pr close`.

GitHub shares one number space across issues and PRs. A bare `#42` may be either: resolve with `gh pr view 42` and fall back to `gh issue view 42`.

## When a skill says "publish to the issue tracker"

Create a GitHub issue in `Sunji007/student-discipline-system`.

## When a skill says "fetch the relevant ticket"

Run `gh issue view <number> --repo Sunji007/student-discipline-system --comments`.

## Wayfinding operations

Used by `/wayfinder`. The map is a single issue with child issues as tickets.

- **Map**: a single issue labelled `wayfinder:map`, holding the Notes / Decisions-so-far / Fog body.
- **Child ticket**: link the child to the map as a GitHub sub-issue via `gh api`. Where sub-issues are unavailable, add the child to a task list in the map body and put `Part of #<map>` at the top of the child body. Labels: `wayfinder:<type>` (`research`, `prototype`, `grilling`, `task`).
- **Blocking**: use native issue dependencies where available. Add an edge with `gh api --method POST repos/Sunji007/student-discipline-system/issues/<child>/dependencies/blocked_by -F issue_id=<blocker-db-id>`. Use the blocker's numeric database ID from `gh api repos/Sunji007/student-discipline-system/issues/<number> --jq .id`, not its issue number or node ID. Where dependencies are unavailable, use a `Blocked by: #<number>` line in the child body.
- **Frontier query**: list the map's open children; omit any child with an open blocker or assignee. First in map order wins.
- **Claim**: assign the ticket to the driving developer with `gh issue edit <number> --add-assignee @me`.
- **Resolve**: comment with the answer using `--body-file`, close the ticket, then append a context pointer and link to the map's Decisions-so-far.
