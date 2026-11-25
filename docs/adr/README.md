# Architecture decision records

One file per decision that shaped the code and would be expensive to reverse. Each says what was
decided, what else was considered, and what it costs. A superseded record stays, marked as such.

| # | Decision | Status |
| --- | --- | --- |
| [0001](0001-layered-architecture.md) | Layered architecture: Domain, Application, Infrastructure, Http, enforced by Deptrac | Accepted |
| [0002](0002-tenancy.md) | Tenancy by construction: the acting staff member is the scope, and every company query takes the company | Accepted |
| [0003](0003-customers.md) | Customers sign in by phone with a one-time code, and the API acts only on the token's customer | Accepted |
