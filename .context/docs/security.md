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
- Passwords and one-time codes are hashed; one-time codes expire and are rate-limited. A code is
  six digits from `random_int`, kept as an HMAC-SHA256 under the app key, valid for five minutes
  and five wrong guesses; only a phone's latest code works; a phone gets three codes in fifteen
  minutes and an address ten sign-in requests a minute. A refused code gets one answer whatever the
  reason ([ADR 0003](/docs/adr/0003-customers.md)).
- **The customer API reads tokens only.** Sanctum's personal access tokens, prefixed `loyalty_`,
  live sixty days and are revoked per device on sign-out and all at once when the account is
  deleted; a back-office session never opens the API.
- **Deleting an account erases the personal data** (phone, name, birth date, CPF) and keeps an
  anonymous row, so the ledger still adds up (LGPD).
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
| Code expiry, guesses, rate limits, one customer per token | `tests/Unit/Application/Customer/`, `tests/Feature/Api/CustomerApiTest.php` |

CodeQL has no PHP analyser; the PHP code is covered by the static analysis above.

## Reporting a vulnerability

Privately, through GitHub's private vulnerability reporting; see [SECURITY.md](/SECURITY.md).
