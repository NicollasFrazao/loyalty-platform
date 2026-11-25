---
type: doc
name: project-overview
description: What the loyalty platform does, for whom, and the phases that build it
category: overview
generated: 2026-11-25
status: filled
scaffoldVersion: "2.0.0"
---

# Project overview

A **multi-tenant loyalty platform** for local businesses. Many companies run on one installation,
each with its own customers. The domain is the platform's core:

- customers **check in** at a company and climb **levels** that set their **cashback**;
- cashback goes into a **wallet** per customer and company, kept as an append-only ledger;
- companies issue **vouchers and promotional codes**, redeemed at the counter;
- companies run a paid **membership club** with benefits.

The project is a rewrite, from scratch, of a loyalty product built by a lean team. None of the
original code or data is used: this repository starts empty and keeps only the problem.

## Who it is for

- **Companies** (bars, restaurants, shops): their staff manage levels, vouchers, the club and the
  customers from a back office.
- **Customers**: they sign up with their phone in an app, follow the companies they visit, check in,
  and spend their cashback and vouchers.

## Phases

Each phase is an issue of the board, a sub-issue of the umbrella issue #11, and one pull request.

| Phase | Issue | Delivers |
| --- | --- | --- |
| L0 | #1 | Foundation: Laravel 13 skeleton, Docker, the quality gate and CI, the context base |
| L1 | #2 | Tenancy: companies, plans and the modules each enabled |
| L2 | #3 | Customers: phone sign-up, profile, following companies |
| L3 | #4 | Check-in and loyalty levels that set the cashback percentage |
| L4 | #5 | Wallet: an append-only cashback ledger per customer and company |
| L5 | #6 | Vouchers and promotional codes |
| L6 | #7 | Membership subscriptions |
| L7 | #8 | Notifications: push and e-mail, with opt-out |
| L8 | #9 | Back office for company staff |
| L9 | #10 | Release: documentation, ADRs and v1.0.0 |

Order: L0 → L1 → … → L9 (L5 and L6 may swap; L8 grows with each phase). Engagement, content,
sports, shop and gift cards are future work (#12 and its sub-issues).

## Current state

L0: the skeleton serves the welcome page, the local stack runs with `make up`, and every check of
the quality gate runs in CI as its own job. L1 adds tenancy: companies with a plan and the modules
they enabled, staff members with roles who sign in to a back office scoped to their own company, and
two demo companies.

## Principles

- **Money is never a mutable column.** Wallet balances are the sum of an append-only ledger; a
  correction is a new entry.
- **External services sit behind ports** (one-time codes, payments, push, mail), each with a
  development adapter, so the whole app runs and is tested without any account anywhere.
- **Tenancy is explicit.** Every query that reads a company's data says which company.
