---
type: doc
name: glossary
description: The vocabulary of the loyalty domain and the abbreviations allowed in code
category: glossary
generated: 2026-11-25
status: filled
scaffoldVersion: "2.0.0"
---

# Glossary

Use these words in code, tests, logs and docs, with this meaning. A phase that introduces a concept
adds its row here in the same pull request.

## Domain

| Term | Meaning |
| --- | --- |
| **company** | A business on the platform (the tenant): its customers, levels, vouchers and club are its own. Never "client", "store" or "tenant" in code. |
| **plan** | What a company subscribes to on the platform; it says which modules the company may enable. |
| **module** | A feature a company turns on (check-in, wallet, vouchers, club...). |
| **customer** | A person with an account in the app. Never "user" for them in the domain: "user" is the framework's word for whoever signs in. |
| **staff member** | A person who works for a company and uses the back office. |
| **follow** | A customer's link to a company they visit; a customer sees and earns only at companies they follow. |
| **check-in** | A customer's recorded visit to a company. |
| **level** | A rank a customer reaches at a company by checking in; it sets the cashback percentage. |
| **cashback** | The share of a purchase credited back to the customer's wallet. |
| **wallet** | A customer's balance at one company, the sum of its ledger. |
| **ledger entry** | One credit or debit of a wallet, never changed after it is written. |
| **voucher** | A benefit a company grants, redeemed once at the counter. |
| **promotional code** | A code a customer types to receive a voucher. |
| **membership** | A customer's paid subscription to a company's club, with its benefits. |
| **redeem** | Spend a voucher or wallet balance at the counter. |

## Abbreviations

Allowed in identifiers: `id`, `url`, `sms`, `otp` (one-time password), `json`. Anything else is
spelled out.
