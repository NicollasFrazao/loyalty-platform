# Loyalty Platform: context index

The knowledge base of this repository, for people and for coding agents. Start with the project
overview, then open the guide the task needs.

## Mandatory for anyone editing this repository

**Before editing any file, read [`development-conventions.md`](/.context/docs/development-conventions.md).**
It is the normative file: the quality gate to run before every commit, the layer rules, the
documentation and readability rules the linters enforce, commits and branches, and the language
everything is written in. Its rules are commands, not background reading.

Two more readings, each with the moment it applies:

- **before adding a class, or an import between `app/` folders** —
  [`architecture.md`](/.context/docs/architecture.md), section "Layers", and
  [ADR 0001](/docs/adr/0001-layered-architecture.md);
- **before naming anything in the domain** — [`glossary.md`](/.context/docs/glossary.md): the
  word to use for each concept of the loyalty domain, and the ones to avoid.

> **This file is the source of the agent surfaces.** `AGENTS.md`, `.claude/CLAUDE.md` and
> `.github/copilot-instructions.md` are byte-for-byte copies of `.context/docs/README.md`, written by
> `php artisan context:export` and verified by `php artisan context:check` (the `context` CI job).
> Edit this file, never a copy. The rules themselves are not copied into the surfaces: they live in
> `development-conventions.md`, one hop away, so there is a single place to change them.

## Guides

| Guide | What it answers |
| --- | --- |
| [Project overview](/.context/docs/project-overview.md) | What the platform does, for whom, and in which phases it is built |
| [Architecture](/.context/docs/architecture.md) | Layers and their dependency rules, where each kind of class goes, the composition root |
| [Development conventions](/.context/docs/development-conventions.md) | **Normative.** Quality gate, layer rules, doc comments, readability, commits, branches, PRs, language |
| [Development workflow](/.context/docs/development-workflow.md) | Docker setup, day-to-day `make` targets, recipes |
| [Testing strategy](/.context/docs/testing-strategy.md) | Unit and feature tests with Pest, the coverage gate, what each phase must test |
| [Glossary](/.context/docs/glossary.md) | Domain vocabulary and allowed abbreviations |
| [Security](/.context/docs/security.md) | The rules every phase follows, the checks that guard them, how to report a vulnerability |
| [Tooling](/.context/docs/tooling.md) | Every Composer and npm script, hook and CI job, and what each one catches |
| [Technical debt](/.context/docs/tech-debt.md) | Known gaps, with the reason they are still open |
| [Decision records](/docs/adr/README.md) | Why the code is organised the way it is |

## Agents and skills

Playbooks tailored to this repository, for agents that support them:

- [`agents/architect-specialist.md`](/.context/agents/architect-specialist.md), [`agents/code-reviewer.md`](/.context/agents/code-reviewer.md), [`agents/test-writer.md`](/.context/agents/test-writer.md)
- [`skills/code-review`](/.context/skills/code-review/SKILL.md), [`skills/commit-message`](/.context/skills/commit-message/SKILL.md), [`skills/test-generation`](/.context/skills/test-generation/SKILL.md)

## Repository map

- `app/Domain/` — entities, value objects, domain services, repository interfaces, domain errors; imports nothing outside itself
- `app/Application/` — use cases and DTOs; imports only `Domain`
- `app/Infrastructure/` — Eloquent models and repositories, queues, the adapters of external services (one-time codes, payments, push, mail)
- `app/Http/` — controllers, form requests, resources
- `app/Console/` — developer tooling (`php artisan context:export` / `context:check`)
- `app/Providers/` — the composition root
- `tests/Unit/`, `tests/Feature/` — Pest; unit tests never boot Laravel
- `docker/`, `docker-compose.yml`, `Makefile` — the local stack (PHP 8.4 on FrankenPHP, a queue worker, MySQL 8.4, Mailpit)
- `docs/adr/` — decision records
- `phpcs/Loyalty/` — local PHP_CodeSniffer sniffs (`TODO` with an issue)
- `.github/` — CI, CodeQL, Dependabot, PR and issue templates, CODEOWNERS
