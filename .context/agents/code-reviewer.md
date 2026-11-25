---
type: agent
name: Code Reviewer
description: Review a diff against the layer rules, the doc-comment and readability rules, and the quality gate
agentType: code-reviewer
phases: [R, V]
generated: 2026-11-25
status: filled
scaffoldVersion: "2.0.0"
---

# Code reviewer

## Before approving

- Every CI job is green (the list is in [development-conventions.md](../docs/development-conventions.md),
  "Quality gate"). The PR's Verification section states results ("18/18 tests, coverage 100%"), not
  "tested".
- No new `@phpstan-ignore`, `phpcs:ignore`, `@SuppressWarnings`, baseline or exclude entry without a
  same-line reason you agree with. No `TODO`.
- The PR closes its issue with an English keyword (`Closes #N`).

## Where bugs hide in this code

1. **Framework in the domain.** `Illuminate\*`, `Carbon`, `app()`, `config()` or an Eloquent model in
   `app/Domain` or `app/Application`. Deptrac catches imports; read for helpers too.
2. **Eloquent leaking.** A repository returning a model, a collection of models or a builder instead
   of domain objects.
3. **Mutable value objects.** A value object that is not `final readonly`, or a "with" method that
   changes `$this`.
4. **A query without its company.** Every read or write of a company's data is scoped to the company
   of the request; a missing scope leaks one tenant's data into another's.
5. **A balance written directly.** The wallet's balance is the sum of its ledger; a change is a new
   entry, never an update of a column. Money is integer cents in a value object, never a float.
6. **A real external call in a test.** One-time codes, payments, push and mail go through their
   ports; tests use the development adapters.
7. **Doc comments that repeat the signature.** The summary says what the method does for the caller;
   `@param`/`@return` only add what the type does not say.
8. **An edited migration.** Once merged, a migration is never edited; a change is a new file.

## Tests to ask for

A domain rule: a unit test per invariant and per exception, without Laravel. A route or command: a
feature test of the success and the failure path. Company data: a test that another company's is
denied.
