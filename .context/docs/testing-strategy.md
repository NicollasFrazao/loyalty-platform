---
type: doc
name: testing-strategy
description: Unit and feature tests with Pest, the coverage gate, and what each phase must test
category: testing
generated: 2026-11-25
status: filled
scaffoldVersion: "2.0.0"
---

# Testing strategy

Tests are written with [Pest](https://pestphp.com) 4 on PHPUnit 12. `composer test:coverage` (the
`test` CI job) runs them all with pcov and fails under **80% line coverage of `app/`**.

## Layers

| Suite | Folder | Boots Laravel | For |
| --- | --- | --- | --- |
| Unit | `tests/Unit/` | **no** | Domain and application classes, and any class that takes its collaborators by constructor |
| Feature | `tests/Feature/` | yes (`Tests\TestCase`) | Routes, Artisan commands, repositories, the wiring of the provider |

`tests/Pest.php` binds `Tests\TestCase` to `Feature` only. Keep it that way: a domain test that
needs the container is a sign the class reaches outside the domain.

## Database

Feature tests run on **MySQL 8.4**, the image of `docker-compose.yml`, because the queries and the
constraints that repeat the domain rules are MySQL's ([ADR 0005](/docs/adr/0005-persistence.md)).
`phpunit.xml` sets `DB_CONNECTION=mysql` and `DB_DATABASE=loyalty_testing`, a database of its own, so a
test run never wipes the app's data; the host and the credentials come from `.env` (the compose
service). MySQL creates `loyalty_testing` when its volume is new (`docker/mysql/initdb/`);  In CI the `test` job runs the same image as a
service and points `DB_HOST` at it. Tests that touch the database use `RefreshDatabase`.

Cache, session and mail use array drivers. The other variables of `phpunit.xml` win over `.env`:
Laravel's environment loader never overwrites a variable that is already set. `phpunit.xml` also
sets a test-only `APP_KEY`, so the tests need no generated key; CI copies `.env.example` to `.env`
before running them, as a developer's checkout has one.

Unit tests never touch the database, so `tests/Unit/` runs without the stack.

## Conventions

- One behaviour per test, named as a sentence: `it('reports a surface edited by hand and a missing
  one')`.
- Arrange, act, assert, separated by blank lines when the test is longer than three lines.
- No `$this->property` state in Pest closures: PHPStan cannot type it. Use a typed helper instead
  (`tests/Support/ScratchRepository.php` is the model) and the namespaced Laravel functions
  (`Pest\Laravel\get`, the `command()` helper in `tests/Pest.php`).
- Files the test writes go under the system temp directory, through a helper that deletes them after
  each test (`ScratchRepository::deleteAll()` in `tests/Pest.php`).
- A port (repository interface) is tested by a **contract**: one test file per port in
  `tests/Feature/Contracts/`, its tests run `->with()` a dataset of adapters. The in-memory adapter in
  `tests/Support/Domain/` is the reference; the Eloquent adapter joins the dataset and runs on
  MySQL. What only one adapter does (its storage format, refusing a row it did not write, a save that
  is all or nothing) is tested next to it, in `tests/Feature/Infrastructure/Persistence/`.

- Literal numbers and strings are fine in tests: they are the readable form of a case. Tests are
  exempt from the magic-number check, not from PHPStan or PHP_CodeSniffer.

## What each phase must test

| Phase | Tests |
| --- | --- |
| L0 foundation | The welcome page and the health route; the `context:*` commands; the local sniffs on their fixtures |
| L1 tenancy | A company's data is never read through another company; plans limit the modules |
| L2 customers | Phone sign-up with a one-time code (expiry, rate limit, the development adapter); following a company |
| L3 check-in | Levels reached at their thresholds; the cashback percentage each level sets |
| L4 wallet | The balance is the sum of the ledger; no entry is ever changed; concurrent debits cannot overdraw |
| L5 vouchers | One redemption per voucher; codes that expire or run out |
| L6 memberships | Subscriptions through the payment port's development adapter; benefits while active only |
| L7 notifications | Opt-out respected per channel; the development adapters record what was sent |
| L8 back office | Staff reach only their company; every action authorised |

## Today

The welcome page and the health route, the `context:*` commands and their classes, and the local
PHP_CodeSniffer sniff on its fixtures.
