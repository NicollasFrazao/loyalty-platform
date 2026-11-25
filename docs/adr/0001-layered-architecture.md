# 0001. Layered architecture: Domain, Application, Infrastructure, Http

- Status: accepted
- Date: 2026-11-25
- Issue: [#1](https://github.com/NicollasFrazao/loyalty-platform/issues/1)

## Context

A loyalty platform is mostly rules: which company a piece of data belongs to, which level a check-in
reaches, how much cashback it earns, what a wallet holds, whether a voucher can still be redeemed.
Those rules must be right with money involved, and they must be tested in milliseconds, without a
database, an SMS provider or a payment gateway.

Laravel's default layout puts those rules in models and controllers, next to the queries and the
HTTP; nothing stops a rule from calling Eloquent, and then it can only be tested with a database.

## Decision

Organise `app/` in four layers, with dependencies pointing inwards only:

| Layer | Holds | May depend on |
| --- | --- | --- |
| `app/Domain` | entities, value objects, domain services, ports (repository and external service interfaces), domain exceptions | nothing outside itself (PHP's own classes allowed) |
| `app/Application` | use cases and their DTOs | `Domain` |
| `app/Infrastructure` | Eloquent models and repositories, jobs, adapters of external services | `Domain`, `Application`, Laravel and other packages |
| `app/Http` | controllers, form requests, resources | `Domain`, `Application`, Laravel |

`app/Providers` is the composition root and may see every layer; `app/Console` holds developer
tooling and depends only on the framework. `Http` and `Infrastructure` never depend on each other.

The rules are enforced, not documented only: `deptrac.yaml` declares the layers, a `Vendor` layer
holds every third-party namespace, and the `deptrac` CI job fails on any violation and on any
dependency Deptrac cannot classify (`--fail-on-uncovered`).

## Alternatives considered

- **Laravel's default layout** (models, controllers, services in `app/`): the fastest start, but the
  money rules would end up in models, testable only through the database.
- **Modules per feature** (`app/Wallet/{Domain,Http,...}`): attractive for a platform with optional
  modules, but the core (tenancy, customers, levels, wallet) is highly connected; the modules of the
  product are a runtime switch per company (L1), not a code boundary. Revisit if a module grows
  apart.
- **A separate Composer package for the domain**: the strongest isolation, at the cost of a second
  package, its own autoloading and versioning, for one application.

## Consequences

- Domain and use-case tests run without the framework (`tests/Unit/` does not boot Laravel).
- Every persistence need costs an interface in `Domain` and a mapping in `Infrastructure`, more code
  than using Eloquent directly. Accepted: it keeps the ledger and the tenancy rules in plain PHP.
- A new package used from `app/` must be added to the `Vendor` layer before Deptrac passes; the
  friction is deliberate, it makes each new dependency visible in review.
- Laravel features that assume models in `app/Models` (factory discovery, policies by convention)
  need explicit configuration, as `#[UseFactory]` on the user model already shows.
