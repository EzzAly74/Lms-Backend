# MVC → Angular + APIs Migration Rules

You are migrating an existing MVC project into:

- Backend APIs
- Angular Frontend

The goal is:

- Angular must become an EXACT visual and functional clone of the MVC project
- APIs must follow enterprise-level architecture and HTTP standards
- Performance must be optimized heavily
- Code must be reusable, scalable, maintainable, and production-ready

IMPORTANT:

- NEVER modify or break the original MVC project
- Build Angular in a completely separate folder/project
- Preserve ALL business logic and functionality from MVC
- Do NOT simplify UI/UX
- The Angular application must behave exactly like the MVC project

---

# PROJECT STRUCTURE RULES

## Backend

Use Clean Architecture principles.

Structure:

/Controllers
/Services
/Repositories
/DTOs
/Entities
/Interfaces
/Mappers
/Middleware
/Validators
/Localization
/Common
/Helpers

Rules:

- Controllers:
    - ONLY receive requests and return responses
    - NO business logic
    - NO database logic

- Services:
    - Contain ALL business logic

- Repositories:
    - Handle ALL database operations
    - No business logic

- DTOs:
    - Used for requests/responses only

- Validators:
    - Centralized validation layer

- Middleware:
    - Exception handling
    - Logging
    - Authentication
    - Localization
    - Request tracking

---

# ANGULAR RULES

## Angular Version

Use latest stable Angular version.

Use:

- Standalone components
- Lazy loading
- Route-level code splitting
- Signals when beneficial
- OnPush change detection
- TrackBy everywhere in loops
- Strict TypeScript mode

---

# ANGULAR GOAL

Angular MUST be:

- Pixel-perfect copy of MVC
- Same design
- Same layout
- Same spacing
- Same responsive behavior
- Same business flow
- Same validations
- Same user experience

DO NOT redesign anything unless explicitly requested.

---

# BEFORE IMPLEMENTING ANY PAGE

You MUST:

1. Analyze the MVC page completely
2. Understand:
    - Functionality
    - User flow
    - APIs
    - Validation rules
    - Loading states
    - Edge cases
    - Responsive behavior
    - Conditional rendering
    - Permissions
    - Localization
3. Replicate ALL behaviors in Angular

Never skip details.

---

# ANGULAR ARCHITECTURE

Use feature-based architecture.

Example:

/src/app/features/auth
/src/app/features/products
/src/app/features/cart
/src/app/features/orders

Shared reusable code:

/src/app/shared/components
/src/app/shared/directives
/src/app/shared/pipes
/src/app/shared/services
/src/app/shared/models
/src/app/shared/utils

Core:

/src/app/core/interceptors
/src/app/core/guards
/src/app/core/layout
/src/app/core/services

---

# REUSABLE COMPONENT RULES

Create reusable components aggressively.

Examples:

- tables
- dialogs
- forms
- buttons
- loaders
- skeletons
- dropdowns
- cards
- pagination
- filters
- modals
- empty states
- error states

Never duplicate UI code.

---

# PERFORMANCE RULES

Performance is CRITICAL.

You MUST optimize Angular heavily.

## Required Optimizations

- Use lazy loading everywhere possible
- Use standalone components
- Use OnPush change detection
- Avoid unnecessary subscriptions
- Use async pipe whenever possible
- Destroy subscriptions properly
- Use trackBy in all ngFor loops
- Avoid heavy template logic
- Move computations outside templates
- Memoize expensive operations when needed
- Avoid duplicate API calls
- Use caching strategies where appropriate
- Optimize bundle size
- Use dynamic imports
- Use image lazy loading
- Use skeleton loaders
- Avoid unnecessary rerenders
- Use pure pipes
- Use debouncing for searches
- Use virtual scrolling for large lists
- Use server-side pagination when needed

Target:

- Fast initial load
- Smooth rendering
- Minimal memory usage
- Excellent Lighthouse score

---

# UI/UX RULES

Must preserve:

- Original animations
- Responsive behavior
- Mobile experience
- Desktop behavior
- Accessibility
- Keyboard navigation
- Form validations
- Error messages
- Loading states

---

# API RULES

APIs must be RESTful and enterprise-grade.

## General Rules

- Use proper HTTP verbs
- Use proper status codes
- Return consistent response structures
- Use centralized exception handling
- Never expose stack traces
- Return meaningful error messages
- Validate all inputs
- Use pagination properly
- Use filtering/sorting standards

---

# API RESPONSE STRUCTURE

## Success Response

```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {},
    "errors": [],
    "statusCode": 200
}
{
  "success": false,
  "message": "Validation failed",
  "data": null,
  "errors": [
    "Name is required"
  ],
  "statusCode": 400
}
```

HTTP STATUS CODE RULES
200 OK

Use when:

Request succeeded
Data retrieved successfully
Update succeeded
201 Created

Use when:

New resource created successfully
204 No Content

Use when:

Delete succeeded
No response body needed
400 Bad Request

Use when:

Malformed request
Invalid request format
Missing required fields
Invalid query parameters

DO NOT use 400 for business validation failures.

401 Unauthorized

Use when:

User is not authenticated
403 Forbidden

Use when:

User authenticated but lacks permission
404 Not Found

Use when:

Resource does not exist
Product/SKU not found
Entity ID not found

Example:
SKU does not exist → 404

409 Conflict

Use when:

Duplicate data
Business conflict
State conflict

Examples:

Email already exists
Order already processed
422 Unprocessable Entity

Use when:

Request format is valid
But business validation fails

Examples:

Quantity exceeds stock
Invalid business rule
Coupon expired
Out of stock
Pickup branch unavailable

Example:
SKU exists but no stock in any branch → 422

429 Too Many Requests

Use when:

Rate limit exceeded
500 Internal Server Error

Use ONLY for:

Unexpected server exceptions

Never intentionally return 500 for validation/business cases.

VALIDATION RULES
Frontend validation AND backend validation are both required
Never trust frontend validation alone
Validation messages must be user-friendly
Validation must be centralized
LOCALIZATION RULES

Localization must depend on request header.

Example:
Accept-Language: ar
→ Return Arabic values

Accept-Language: en
→ Return English values

DO NOT return:

{
"name": {
"en": "Laptop",
"ar": "لاب توب"
}
}

Instead return:

Arabic request:

{
"name": "لاب توب"
}

English request:

{
"name": "Laptop"
}
SECURITY RULES
Validate all inputs
Sanitize inputs
Prevent XSS
Prevent SQL Injection
Use authorization properly
Never trust client-side data
Secure sensitive endpoints
Hide internal implementation details
API DOCUMENTATION

Generate:

Swagger/OpenAPI documentation
Clear request/response examples
Error examples
Validation examples
CODE QUALITY RULES

Code MUST be:

Clean
Readable
Modular
Reusable
Scalable
Production-ready

Avoid:

Spaghetti code
Duplicated logic
Hardcoded values
Massive components
Massive services
ANGULAR COMPONENT RULES

Components should:

Have single responsibility
Be small and reusable
Avoid business logic in templates
Use strongly typed models
Use smart/dumb component pattern when useful
STATE MANAGEMENT

Use lightweight scalable approach.

Preferred:

Signals
RxJS state services

Only introduce NgRx if complexity truly requires it.

Avoid overengineering.

STYLING RULES
Use scalable structure
Avoid duplicated styles
Use variables/tokens
Keep responsiveness clean
Preserve exact MVC appearance
IMPORTANT MIGRATION RULES
NEVER break existing functionality
NEVER remove business logic
NEVER simplify flows without approval
NEVER create fake implementations
NEVER ignore edge cases
NEVER ignore loading/error states
NEVER skip responsive behavior

Always compare Angular output against MVC output.

Angular should feel like the original system, not a redesign.

FINAL GOAL

Deliver:

Enterprise-grade APIs
Correct HTTP status codes
Clean architecture
High-performance Angular app
Reusable architecture
Pixel-perfect MVC clone
Production-ready scalable system
