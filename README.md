# Loyalty Platform

A multi-tenant loyalty platform for local businesses: customers check in, climb levels that set
their cashback, keep a wallet per company, redeem vouchers and join paid membership clubs.

> **Being built phase by phase** in Laravel 13 with a layered architecture; the plan and its phases
> are in [#11](https://github.com/NicollasFrazao/loyalty-platform/issues/11).

## Status

L0, the foundation: a Laravel 13 (PHP 8.4) skeleton on Docker (app, queue worker, MySQL 8.4,
Mailpit) with the full quality gate in CI and the context base for people and coding agents. L1,
tenancy: companies with a plan and modules, and a back office where each company's staff (owner,
manager, operator) see and change only their own company. Demo: `owner@corner-bar.example`,
password `password`.

## Running locally

Requirements: Docker with Compose v2; Node.js 22 for the git hooks and the JavaScript checks.

```bash
make up        # app on http://localhost:8000, Mailpit on http://localhost:8025; installs, migrates and seeds on first run
make install   # Composer and npm dependencies and the git hooks
make gate      # every check CI runs
```

`make help` lists the other targets (`test`, `coverage`, `lint`, `analyse`, `sh`, `down`...).

## Architecture

`app/` is split into `Domain` (no framework), `Application` (use cases), `Infrastructure` (Eloquent,
queues, the adapters of external services) and `Http`, with the dependency rules enforced by Deptrac
in CI. See [ADR 0001](docs/adr/0001-layered-architecture.md) and
[`.context/docs/architecture.md`](.context/docs/architecture.md).

## Quality gate

Every check is its own CI job and a required status check:

- Pint and PHP_CodeSniffer (doc comments and readability);
- PHPStan at max (Larastan, strict rules, cognitive complexity ≤ 15, dead code), PHPMD and magic
  numbers;
- Rector (dry run), Deptrac and composer-unused;
- Pest with 80% coverage, and `composer audit`;
- ESLint (with the Sonar rules), knip and `npm audit`;
- a Docker smoke test, the context surfaces check, commitlint and CodeQL.

Details in [`.context/docs/tooling.md`](.context/docs/tooling.md).

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). The knowledge base for people and coding agents starts at
[`.context/docs/README.md`](.context/docs/README.md).

## License

[MIT](LICENSE).
