---
type: doc
name: architecture
description: Layers and their dependency rules, where each kind of class goes, and the composition root
category: architecture
generated: 2026-11-25
status: filled
scaffoldVersion: "2.0.0"
---

# Architecture

One Laravel 13 application on PHP 8.4 and MySQL 8.4. Laravel lives at the edges: HTTP, persistence,
queues, configuration. The rules of the problem (tenancy, levels, cashback, the wallet ledger,
vouchers, memberships) are plain PHP that can be read and tested without the framework. The decision
and its trade-offs are in [ADR 0001](/docs/adr/0001-layered-architecture.md).

## Layers

```
app/
  Domain/          entities, value objects, domain services,         depends on nothing outside Domain
                   repository interfaces, domain exceptions          (PHP's own classes allowed)
  Application/     use cases (command handlers), DTOs                Domain
  Infrastructure/  Eloquent models and repositories, queued jobs,    Domain, Application, Laravel,
                   adapters of external services                     database/ factories
  Http/            controllers, form requests, resources, views      Domain, Application, Laravel
  Console/         developer tooling (context:export, context:check) Laravel
  Providers/       composition root: binds ports to adapters         everything
```

### Layer rules

Checked by `composer check:architecture` (Deptrac, `deptrac.yaml`, run with `--fail-on-uncovered`):

| Rule | Why |
| --- | --- |
| `Domain` depends on nothing outside `Domain` | Levels, cashback and the ledger are tested in milliseconds, and cannot reach I/O or the framework by accident. |
| `Application` depends only on `Domain` | A use case talks to storage and to external services through interfaces declared in `Domain`; swapping an adapter touches `Infrastructure` and the provider only. |
| `Infrastructure` and `Http` never depend on each other | A controller reaches the database or a service only through a use case. |
| Only `Providers` sees every layer | Bindings live in one place, so the wiring is readable in one file. |
| Every third-party class belongs to the `Vendor` layer | A dependency on a namespace Deptrac does not know is reported as uncovered and fails the job: a new package has to be classified before it is used. |

### Where things go

| Kind of class | Folder |
| --- | --- |
| Entity, aggregate root | `app/Domain/<Context>/` |
| Value object (immutable, `final readonly`) | `app/Domain/<Context>/` |
| Domain service | `app/Domain/<Context>/` |
| Port: repository or external service interface | `app/Domain/<Context>/` |
| Domain exception | `app/Domain/<Context>/` |
| Use case and its input/output DTOs | `app/Application/<Context>/` |
| Repository implementation (Eloquent adapter of a port) | `app/Infrastructure/Persistence/` |
| Eloquent model, named `*Record` (the framework's `User` aside) | `app/Infrastructure/Persistence/Eloquent/` |
| Adapter of an external service (one-time codes, payments, push, mail), with its development double | `app/Infrastructure/<Service>/` |
| Controller, form request, resource | `app/Http/` |
| Binding of an interface to its implementation | `app/Providers/` |

`<Context>` is a folder per part of the domain, created when its first class arrives (planned:
`Tenancy`, `Customer`, `CheckIn`, `Wallet`, `Voucher`, `Membership`, `Notification`, and `Shared`
for identifiers, money and the `DomainError` base).

### Design rules inside the layers

- Constructor injection only; no service location (`app()`, facades) outside `Http`,
  `Infrastructure` and `Providers`.
- Composition over inheritance. Classes are `final` unless they are designed for extension; value
  objects are `final readonly`.
- Variation points are interfaces in `Domain` with one implementation each until a second one is
  needed.
- Domain errors are dedicated exception classes extending a `DomainError` base, named for what went
  wrong, created by named constructors.
- Money is an integer number of cents in a value object, never a float.

## What exists today

- `app/Domain/Shared/` — identifiers, `Name`, the `DomainError` base, the `Clock` port.
- `app/Domain/Tenancy/` — L1 (#2): `Company` (a `Subscription` of a `Plan` and a `CompanyStatus`,
  and its `Module`s), `StaffMember` with a `Role`, the credentials (`EmailAddress`, `PlainPassword`,
  `HashedPassword`), the `CompanyRepository`, `StaffRepository` and `PasswordHasher` ports, and their
  errors ([ADR 0002](/docs/adr/0002-tenancy.md)).
- `app/Domain/Customer/` — L2 (#3): `Customer` with its `PhoneNumber` and `CustomerProfile`
  (`Document` is the CPF), the `CodePolicy` of one-time codes, the ports `CustomerRepository`,
  `CodeChallenges`, `OneTimeCodes`, `OtpSender`, `Following` and `AccessTokens`, and their errors
  ([ADR 0003](/docs/adr/0003-customers.md)).
- `app/Application/Tenancy/` — `ActingStaff` (the scope of every back-office use case),
  `IdentifyStaff`, `SignIn`, `ConfigureModules`, `ListStaff`, `AddStaffMember`, `RemoveStaffMember`.
- `app/Application/Customer/` — `RequestCode`, `VerifyCode`, `CustomerSession`, `ManageProfile`,
  `FollowCompanies`, `DeleteAccount`.
- `app/Infrastructure/` — `Persistence/EloquentCompanyRepository` and `EloquentStaffRepository`
  (with `CompanyRows` and `StaffRows`), `Tenancy/FrameworkPasswordHasher`;
  `Persistence/EloquentCustomerRepository` (with `CustomerRows`), `EloquentCodeChallenges` and
  `EloquentFollowing`; `Customer/HmacOneTimeCodes`, `LogOtpSender` (the development double of an
  SMS gateway) and `SanctumAccessTokens`; `Shared/SystemClock`.
- `app/Http/` — the back office (`Controllers/BackOffice/`: sign-in, modules, staff) and the
  `module:<name>` middleware (`Middleware/EnsureModuleEnabled`); the customer API
  (`Controllers/Api/`, `Requests/Api/`, `Resources/CustomerJson`), described in
  [`docs/api/openapi.json`](/docs/api/openapi.json).
- `app/Console/` — `ContextSurfaces` and the two `context:*` commands (see [tooling.md](tooling.md)).
- `app/Infrastructure/Persistence/Eloquent/User.php` — the framework's user model, moved out of
  `app/Models` so Eloquent stays in Infrastructure.
- `app/Providers/` — `TenancyServiceProvider` binds the tenancy ports; `BackOfficeServiceProvider`
  builds the `ActingStaff` from the session; `CustomerServiceProvider` binds the customers' storage
  and the clock, `OneTimeCodeServiceProvider` the codes and their sender, and
  `CustomerApiServiceProvider` the tokens and the `CustomerId` of the request's token.
- `routes/web.php` — the welcome page and the back office (`/back-office`, no route names a company);
  `/up` is Laravel's health route, used by the container's health check.
- `routes/api.php` — the customer API under `/api/v1` (no route names a customer): `auth/code`,
  `auth/token`, `me` and `me/companies`.

## Runtime

- `docker compose up`:
  - `app`: FrankenPHP, Caddy and PHP 8.4 in one process, serving `public/` on port 8000;
  - `queue`: the queue worker;
  - `mysql`: MySQL 8.4;
  - `mailpit`: catches every e-mail, at http://localhost:8025.
- The app's entrypoint installs the Composer dependencies on a fresh clone, creates `.env`, generates
  the key and runs the migrations and the seeders.
- Sessions, cache and queue use the database drivers (Laravel's defaults). The tests use the
  `loyalty_testing` database on the same MySQL, and array drivers (`phpunit.xml`).
