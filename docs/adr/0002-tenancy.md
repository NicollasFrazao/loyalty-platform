# 0002. Tenancy by construction: the acting staff member is the scope, and every company query takes the company

- Status: accepted
- Date: 2026-11-25
- Issue: [#2](https://github.com/NicollasFrazao/loyalty-platform/issues/2)

## Context

Many companies share one installation and one database. L1's rule is that a staff member of company
A can never read or change company B's data. Each company also enables the modules its plan allows,
and a module's endpoints must answer only while it is on.

The usual Laravel answers rely on discipline:

- a global scope on every model, which a query can drop (`withoutGlobalScopes`, a raw query) and
  which hides the company from the reader;
- a `company_id` parameter in the routes, checked by a policy each time.

## Decision

- **The domain** (`app/Domain/Tenancy/`):
  - `Company`: its `Subscription` (a `Plan` and a `CompanyStatus`) and the `Module`s it enabled;
    `ensureServes(module)` is the one check a module's use cases and routes run.
  - `Plan`: the modules a company may enable and the size of its staff.
  - `StaffMember`, with a `Role`: an owner manages the staff, an owner or manager configures the
    modules, an operator serves customers.
- **The acting staff member is the scope.**
  - Every back-office use case takes an `ActingStaff`: the signed-in staff member with their company.
  - `BackOfficeServiceProvider` builds it from the session guard alone, and controllers and
    middleware receive it injected.
  - No route names a company. A company id sent in a form or a query is ignored, because nothing
    reads it.
- **Every repository method on a company's data takes the company.**
  - `StaffRepository::get(company, id)`, `remove(company, id)`, `forCompany(company)`: a staff
    member of another company is **not found** (404), not forbidden, so a company cannot learn who
    works elsewhere.
  - The only lookups outside a company are the two that must be: the credentials of an email
    address at sign-in, and the staff member the session names.
- **Roles are checked in the use cases** (`ActingStaff::ensureCanManageStaff()` and
  `ensureCanConfigure()`, raising `NotAllowed` → 403), not in Laravel policy classes, so the rule
  is tested without the framework.
- **Modules gate their routes** with the `module:<name>` middleware (`EnsureModuleEnabled`), which
  answers 403 while the company has not enabled the module or is suspended.
- **Staff are the framework's `users`**, with `company_id` and `role`, so the session guard works
  unchanged. Customers (L2) are a separate table and guard, since they sign in by phone.
- **Demo seed:** two companies, Corner Bar (Growth) and Harbour Café (Complete), each with an owner,
  a manager and an operator (`owner@corner-bar.example` … password `password`).

## Alternatives considered

- **Global scopes on Eloquent models.** They protect Eloquent queries only, and silently: a reader
  of a repository cannot see the company in the query. Here the company is an argument everyone can
  see, and the contract tests prove each adapter honours it.
- **One database per company.** The strongest isolation, at the cost of migrations and connections
  per company; out of proportion for a portfolio platform.
- **The company in the URL** (`/companies/{company}/staff`). It invites the cross-tenant mistake
  that a policy then has to catch on every route.

## Consequences

- A use case without an `ActingStaff` cannot reach a company's data; a new repository for company
  data must take the `CompanyId` in every method, and its contract test must prove another company's
  rows are not found.
- Staff have no password recovery or e-mail change yet (TD-05).
- Later phases put their routes behind `module:<name>` and their use cases call
  `ensureServes(module)`.
