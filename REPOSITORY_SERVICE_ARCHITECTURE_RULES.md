# Repository & Service Layer Architecture Rules

This document defines the mandatory backend architecture standards for the entire Laravel project.

These rules are REQUIRED and must be followed consistently across all modules, APIs, and features.

---

# Core Architecture Philosophy

The application must follow a layered architecture with strict separation of concerns.

Controllers must remain thin and lightweight.

Business logic must NOT exist inside controllers.

Database access logic must NOT exist inside controllers or services directly.

The architecture layers are:

Controller
→ Service
→ Repository
→ Model

---

# Controllers Responsibilities

Controllers are responsible ONLY for:

- Receiving HTTP requests
- Calling validation layers (Form Requests)
- Passing validated data to services
- Returning API responses
- Returning API Resources/Transformers
- Handling HTTP-level concerns

Controllers MUST NOT contain:

- Business logic
- Complex conditions
- Database queries
- Eloquent query chains
- Direct model manipulation
- Reusable business rules

Controllers should remain extremely small and clean.

Preferred controller flow:

```php
return $this->successResponse(
    $this->productService->store($request->validated())
);
```

---

# Services Responsibilities

Services are responsible for:

- Business logic
- Application workflows
- Data processing
- Orchestration between repositories
- Transactions
- Domain rules
- Validation beyond HTTP validation if needed
- Integration logic
- Event dispatching
- Queue dispatching

Services MUST:

- Be reusable
- Be testable
- Be framework-light where possible

Services MUST NOT:

- Contain raw DB queries
- Contain large Eloquent query chains
- Return HTTP responses directly

Services should communicate with repositories only.

---

# Repositories Responsibilities

Repositories are responsible for ALL database interactions.

Repositories MUST contain:

- Eloquent queries
- Query builders
- Filters
- Search logic
- Pagination queries
- Data retrieval
- Persistence logic

Repositories MUST:

- Encapsulate database access
- Centralize query logic
- Prevent query duplication
- Be reusable

Repositories MUST NOT:

- Contain business logic
- Handle HTTP concerns
- Return API responses

---

# Repository Structure

Each primary model MUST have:

- Repository Interface
- Repository Implementation
- Service Class
- Service Interface (when beneficial)

Example:

app/Repositories/Product/

- ProductRepositoryInterface.php
- ProductRepository.php

app/Services/Product/

- ProductService.php

---

# Dependency Injection Rules

Always inject:

- interfaces instead of concrete implementations whenever possible

Use Laravel service container bindings.

Example:

```php
$this->app->bind(
    ProductRepositoryInterface::class,
    ProductRepository::class
);
```

---

# Query Rules

DO:

- centralize reusable queries
- use eager loading properly
- optimize queries
- abstract complex filters

DO NOT:

- duplicate query logic
- place queries inside controllers
- place queries directly inside services unless absolutely necessary

---

# Transactions

Database transactions should be handled inside services.

Example responsibilities:

- create order
- create order items
- update inventory
- dispatch events

These belong inside service layer transactions.

---

# Validation Rules

Use:

- Form Requests for HTTP validation
- Service-level validation only for business/domain rules

---

# API Response Rules

Controllers should return:

- API Resources
- standardized JSON responses

Services and repositories MUST NOT return HTTP responses.

---

# Scalability Rules

Architecture must support:

- future microservice extraction
- API versioning
- caching
- queues
- testing
- modularization

---

# Performance Rules

Repositories must:

- avoid N+1 queries
- optimize eager loading
- paginate large datasets
- use query scopes where beneficial

---

# Testing Rules

Repositories:

- integration/database tests

Services:

- business logic unit tests

Controllers:

- API/feature tests

---

# Refactoring Rules

When refactoring legacy code:

1. Move DB logic to repositories
2. Move business logic to services
3. Simplify controllers
4. Preserve existing functionality
5. Refactor incrementally
6. Avoid large unsafe rewrites

---

# Final Mandatory Rule

ALL:

- database access
- query logic
- Eloquent interactions

MUST exist inside repositories.

ALL:

- business rules
- workflows
- domain logic

MUST exist inside services.

Controllers must remain thin request/response layers only.
