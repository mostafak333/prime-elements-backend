# AGENTS.md

Instructions for AI agents and developers working in this repository.

## Start here

1. Read `docs/PROJECT_REFERENCE.md` before exploring the codebase.
2. Trust the implementation over the documentation. The reference explains
   where to look and what is already known; the code is the final authority.
3. If the reference and the code disagree, follow the code and update the
   reference.

## Scope your inspection

- Do not scan or read the entire repository for routine tasks.
- Start from the route, controller, request, service, model, migration, resource,
  and test files directly involved in the task.
- Follow dependencies only as far as needed (e.g. a service's related models and
  the tests that cover it).
- Use `docs/PROJECT_REFERENCE.md` to avoid re-discovering architecture, auth,
  routes, schema, and business rules.

## Project facts to keep in mind

- Laravel `^13.8`, PHP `^8.3`. The README's "Laravel 11" claim is outdated.
- Two JWT guards: `api-user` for customers and `api-admin` for admins.
- Customer routes: `routes/api_user.php`, mounted at `/api`.
- Admin routes: `routes/api_admin.php`, mounted at `/api/admin`.
- Protected customer routes use `auth:api-user` plus `user.active`.
- Admin routes currently use only `auth:api-admin`; RBAC is present but not
  enforced as route middleware.
- Business logic belongs in `app/Services`; controllers stay thin.
- Responses generally use `App\Traits\ApiResponse`
  (`{success, message, data}`).
- Validation belongs in FormRequests under `app/Http/Requests/{Admin,User}`.
- User-facing messages are localized keys in `lang/en/messages.php` and
  `lang/ar/messages.php`. Add both languages.
- Media is stored on the `public` disk via `MediaService`.
- Mail is queued through `SendEmailJob`.
- E-book availability comes from unused `ebook_access_codes`, not physical
  stock.
- Bundle links use a permanent token; editing a bundle must not change it.
- The `carts` table is unused and does not match `Cart`/`CartFactory`. Do not
  build new features on it without fixing the schema first.

## Conventions

- Match the surrounding code style, naming, directory structure, and response
  patterns.
- Prefer constructor injection and existing services over new ad-hoc logic in
  controllers.
- Use `ValidationException::withMessages(...)` for business-rule failures so
  clients receive the standard `422` shape.
- Preserve ownership checks and transaction boundaries around cart, order,
  stock, and e-book code changes.
- Do not change unrelated files, reformat broadly, or "fix" known schema drift
  unless the task requires it.
- Do not invent columns, endpoints, configuration, or behavior.

## Required verification

- Add or update feature tests for behavior changes.
- Run focused tests first, then the full suite:
  - `php artisan test --filter=SomeTest`
  - `composer test` or `php artisan test`
- Known pre-existing issue: 4 `EbookOrderFlowTest` failures reference
  `table carts has no column named user_id`. Report them separately; do not
  present them as regressions from your change.
- For schema or business-rule changes, compare the migration with the model and
  service before assuming a field exists at runtime.

## Documentation maintenance

Update `docs/PROJECT_REFERENCE.md` when you change any of the following:

- routes or endpoint prefixes
- authentication/authorization behavior
- database schema or relationships
- cart, order, stock, discount, bundle, or e-book rules
- queues, mail, storage, or scheduled tasks
- test commands, known failures, or deployment configuration

Keep the reference factual. Mark anything unverified as an assumption or
unknown instead of presenting it as fact.

## Working agreement

- Prefer small, focused changes.
- Explain what you verified and what remains uncertain.
- Never claim a feature works just because a comment, README, or docblock says
  so.
