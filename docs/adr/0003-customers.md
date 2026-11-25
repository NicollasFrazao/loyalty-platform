# 0003. Customers sign in by phone with a one-time code, and the API acts only on the token's customer

- Status: accepted
- Date: 2026-11-25
- Issue: [#3](https://github.com/NicollasFrazao/loyalty-platform/issues/3)

## Context

Customers use a mobile app, not the back office. They have no password: they sign up and in with a
code sent to their phone. A code is a short secret, so it must expire, survive only a few guesses,
and cost the platform little when someone asks for codes in a loop (each one is an SMS). Customers
follow the companies they visit, and the LGPD gives them the right to delete their account, while
the wallet ledger of later phases (L4) must still add up afterwards.

## Decision

- **The domain** (`app/Domain/Customer/`):
  - `PhoneNumber` (international form, separators ignored), `Document` (a CPF with its check
    digits), `CustomerProfile` (name, birth date and CPF, all optional) and `Customer`;
  - `CodePolicy`: a code lives five minutes and survives five wrong guesses; a phone receives at most
    three codes in fifteen minutes; only the latest code of a phone works;
  - ports: `CustomerRepository`, `CodeChallenges` (the codes sent), `OneTimeCodes` (make and check
    a code), `OtpSender` (deliver it), `Following`, `AccessTokens`, and `Shared\Clock`.
- **Codes are never stored.** `HmacOneTimeCodes` draws six digits from `random_int` and keeps an
  HMAC-SHA256 under the application key; checking compares hashes with `hash_equals`. A wrong,
  expired or used code gets the same answer (`CodeRefused` → 422 on `code`), so a guess learns
  nothing, and a wrong guess counts against the code.
- **Two rate limits.** The policy's three codes per phone in fifteen minutes (`TooManyCodes` → 429
  with `Retry-After`), and `throttle:10,1` per address on the two sign-in routes, so one client
  cannot spread requests over many phones.
- **`OtpSender` has a development adapter only** (`LogOtpSender` writes the code to the log); an SMS
  gateway is a new adapter and one binding (TD-06).
- **Tokens are Laravel Sanctum's personal access tokens**, behind the `AccessTokens` port, one per
  device, with the prefix `loyalty_` (secret scanners recognise a leaked one) and sixty days of life.
  The Sanctum guard reads tokens only (`guard: []`): a back-office session never opens the API.
- **The token is the scope.** `CustomerApiServiceProvider` resolves a `CustomerId` from the token's
  customer, and every `/api/v1/me…` controller takes it injected. No route names a customer.
- **Following a company** writes a `company_customers` row: that is what makes the person the
  company's customer. Following again changes nothing; a suspended or unknown company is not found.
- **Deleting an account anonymises it.** The phone, name, birth date, CPF and verification are
  erased, the row is marked `deleted_at`, every company is unfollowed and every token revoked. The
  row and its id stay, so a ledger that references the customer still adds up; the deleted account is
  never found again, and the phone can sign up as a new customer.
- **The API is described in `docs/api/openapi.json`**, and a test fails when a route under
  `api/v1` is missing from it, when it describes a route that does not exist, or when the bearer
  token it declares does not match the route's `auth:sanctum`.

## Alternatives considered

- **Laravel's `RateLimiter` alone.** It keys on the cache, which a restart or another node forgets;
  the per-phone limit is a business rule, so it is counted from the stored codes and tested without
  the framework. The HTTP throttle stays as the cheap outer guard.
- **Storing codes with `password_hash`.** Slow by design, which buys nothing for a code that lives
  five minutes and five guesses, and costs CPU on every attempt. A keyed HMAC keeps a leaked table
  useless without the key.
- **Soft deletes (`SoftDeletes`) keeping the data.** Restorable, but it keeps the personal data the
  customer asked to delete.
- **JWTs.** Stateless tokens cannot be revoked one device at a time without a deny list, which is
  the table Sanctum already keeps.

## Consequences

- The customer use cases run on in-memory ports in unit tests; the adapters keep contract tests.
- A deleted customer's ledger rows (L4) keep pointing at an anonymous id.
- Customers are not staff: `CustomerRecord` is its own authenticatable model, and the session guard
  of the back office never sees it.
- Production needs an SMS gateway adapter (TD-06).
