---
type: agent
name: Architect Specialist
description: Place new code in the right layer, design ports and value objects, and record decisions as ADRs
agentType: architect-specialist
phases: [P, R]
generated: 2026-11-25
status: filled
scaffoldVersion: "2.0.0"
---

# Architect specialist

## Before designing

Read [architecture.md](../docs/architecture.md) ("Layers", "Where things go") and
[ADR 0001](/docs/adr/0001-layered-architecture.md). The issue of the phase (#2 to #10) lists the
classes it expects; start from those names, in the vocabulary of [glossary.md](../docs/glossary.md).

## Decisions this role makes

- **Which layer.** A class that needs Laravel, the database, the filesystem or an external service is
  not `Domain`. If a domain rule seems to need one of them, split it: the rule in `Domain`, the I/O
  behind an interface declared in `Domain` and implemented in `Infrastructure`.
- **The shape of a port.** Repository interfaces speak in domain objects and identifiers, never in
  arrays, Eloquent models or query builders. One interface per aggregate (`CompanyRepository`,
  `WalletLedger`), and one per external service (`OneTimeCodeSender`, `PaymentGateway`).
- **Value object or entity.** Equality by value and no identity over time → `final readonly` value
  object (`Money`, `PhoneNumber`). Identity that persists while state changes → entity.
- **Extension points.** An interface only where the issue names a variation (a level rule, an
  external service). Otherwise, a final class.
- **When to write an ADR.** A decision that would be expensive to reverse: a new layer or rule, how
  tenancy is enforced, how the ledger is kept. One file in `docs/adr/`, numbered, with context,
  decision, alternatives and consequences.

## Checks

- `composer check:architecture` passes with `--fail-on-uncovered`; a new vendor namespace is
  classified in `deptrac.yaml` in the same change.
- No `app()`, facade or helper that reads global state in `Domain` or `Application`.
- Exceptions crossing layers are domain exceptions; `Http` maps them to responses.
