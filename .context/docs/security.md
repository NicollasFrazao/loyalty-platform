---
type: doc
name: security
description: The rules every phase follows, the checks that guard them, and how to report a vulnerability
category: security
generated: 2026-11-25
status: filled
scaffoldVersion: "2.0.0"
---

# Security

## Rules

- **Tenancy is explicit.** Every query that reads or writes a company's data is scoped to the company
  of the request; a company's id never comes from a field the client can choose freely without an
  authorisation check. Each phase tests access to another company's data being denied.
- **The customer comes from the session or the token**, never from an id in the request.
- **Money is an append-only ledger.** A wallet's balance is never written directly; refunds and
  corrections are new entries, so every movement can be audited.
- **Secrets live only in `.env`** (git-ignored). `.env.example` holds development values for the
  local Docker stack and nothing else.
- **External services sit behind ports with development adapters**: no real SMS, payment or push
  account is needed to run or test the app.
- Passwords and one-time codes are hashed; one-time codes expire and are rate-limited.
- CSRF protection on every state-changing web route; rate limits on sign-in and code requests.
- Every query goes through Eloquent or the query builder with bindings; no SQL is built from strings.

## Checks

| Check | Where |
| --- | --- |
| Known vulnerabilities in Composer packages | `audit` CI job (`composer audit`) |
| Known high or critical vulnerabilities in runtime npm packages | `npm-audit` CI job (`--omit=dev`) |
| Static analysis of the JavaScript and the workflows | CodeQL (`.github/workflows/codeql.yml`) |
| Dangerous PHP (`eval`, `exit`, debug output) | PHP_CodeSniffer `Generic.PHP.ForbiddenFunctions`, PHPMD |
| Type confusion, loose comparisons | PHPStan max + `phpstan-strict-rules` |
| Dependency updates | Dependabot (Composer, npm, GitHub Actions, Docker), weekly |
| Secrets pushed by mistake | GitHub secret scanning and push protection |

CodeQL has no PHP analyser; the PHP code is covered by the static analysis above.

## Reporting a vulnerability

Privately, through GitHub's private vulnerability reporting; see [SECURITY.md](/SECURITY.md).
