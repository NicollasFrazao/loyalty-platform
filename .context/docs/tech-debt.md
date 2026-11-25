---
type: doc
name: tech-debt
description: Known gaps, with the reason each is still open and what closes it
category: debt
generated: 2026-11-25
status: filled
scaffoldVersion: "2.0.0"
---

# Technical debt

Known gaps, each a decision with a reason. When one is picked up, it becomes an issue and its number
goes here. A `TODO` in the code names its issue (`TODO(#12): ...`); this table holds the gaps that are
decisions rather than tasks.

| Id | Gap | Why it is open | What closes it |
| --- | --- | --- | --- |
| TD-01 | No mutation testing | Coverage is the gate for now; mutation testing pays off once the wallet ledger exists | Infection on `app/Domain` after L4 (#5) |
| TD-02 | The Docker image is for development only (bind mount, Composer inside, no build stage) | Nothing is deployed | A production stage, at the release (L9, #10), if the app is hosted |
| TD-03 | The welcome page is Laravel's | The interface arrives with the back office and the customer flows | L8 (#9) |
| TD-04 | `minimatch` is forced to 11 through `overrides` in `package.json` | ESLint 10 and `eslint-plugin-sonarjs` ask for `^10`, and GHSA-g3hv-75wg-m249 is fixed only in 11.0.1 | Remove the override once both accept `minimatch` 11 |
