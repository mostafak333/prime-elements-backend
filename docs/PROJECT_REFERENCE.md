# Prime Elements Backend - Project Reference

> Last verified: 2026-10-30
>
> This document describes how the backend actually behaves in code and configuration.
> Where the repository is inconsistent or a behavior could not be verified, it is
> explicitly marked. Treat this file as the starting point for future work, then
> confirm the specific code you are about to change.

---

## 1. How to use this document

- Read this file before exploring the repository.
- Verify behavior in the specific file when a detail matters; do not assume this
  document is more current than the code.
- Update this file whenever you change routes, schema, business rules, auth,
  queues, or other cross-cutting behavior.
- Do not copy README claims blindly. Several README statements are outdated
  (see [Known inconsistencies](#18-known-inconsistencies-and-gaps)).

Legend:

- **Verified** - confirmed directly from code, migrations, or config.
- **Assumption** - plausible but not proven from the repository.
- **Unknown** - no reliable information in the repository.

---

## 2. Stack (verified)

| Area | Technology / version |
| --- | --- |
| Language | PHP `^8.3` |
| Framework | Laravel `^13.8` |
| Auth | `tymon/jwt-auth` `^2.3` (JWT guards) |
| Authorization | `spatie/laravel-permission` `^8.0` |
| Social login | `laravel/socialite` |
| PDF invoices | `barryvdh/laravel-dompdf` `^3.1` |
| API docs | `dedoc/scramble` `^0.13.28` |
| Tests | PHPUnit `^12.5.12`, Laravel test helpers |
| Formatting | Laravel Pint `^1.27` |
| Excel import | PhpSpreadsheet `^5.2` |
| Dev tooling | Laravel Pail, Collision, Faker, Mockery, Tinker |

`composer.json` also references `laravel/pao`, which appears to be an internal or
custom package. Its behavior is **Unknown** and it is not used by application
code found during this review.

The README says Laravel 11 and PHP 8.2+. Both are outdated; use `composer.json`
as the source of truth.

---

## 3. Repository layout

```text
app/
  Console/Commands/         ExpireProductDiscounts (discounts:expire)
  Http/
    Controllers/
      AdminApi/             Admin-only endpoints under /api/admin
      UserApi/              Customer endpoints under /api
      OrderInvoiceController.php
    Middleware/             EnsureUserIsActive, SetLocale, HandleFormDataOnNonPostRequests
    Requests/               FormRequest validation, split Admin/ and User/
    Resources/              API transformers, split Admin/ and User/
  Jobs/                     SendEmailJob
  Mail/                     Mailables for user/admin/ebook/contact flows
  Models/                   Eloquent models
  Providers/                AppServiceProvider
  Services/                 Business logic (one service per domain)
  Traits/                   ApiResponse
bootstrap/app.php           Route registration, middleware, scheduler
config/                     auth, jwt, queue, mail, filesystems, etc.
database/
  factories/                Model factories
  migrations/               Schema
  seeders/                  DatabaseSeeder and seeders
lang/
  en/                       messages.php, validation.php
  ar/                       auth.php, messages.php, pagination.php, passwords.php, validation.php
routes/
  api_user.php              Customer/public routes, mounted at /api
  api_admin.php             Admin routes, mounted at /api/admin
  web.php                   Minimal web routes
  console.php               Console routes (inspire only)
tests/
  Feature/                  API and behavior tests
  Unit/                     Minimal unit tests
```

There is **no** `app/Observers` directory, no Dockerfile, and no CI workflow
file in the repository.

---

## 4. Local setup and common commands

Verified Composer scripts:

```bash
composer setup     # install, copy .env, key:generate, migrate, npm install/build
composer dev       # serve + queue:listen + pail + vite concurrently
composer test      # config:clear then php artisan test
```

Typical manual setup:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate
php artisan storage:link
php artisan serve
```

Operational commands:

```bash
php artisan queue:work            # required because mail is queued
php artisan schedule:work         # runs discounts:expire every five minutes
php artisan migrate:fresh --seed  # resets local database
```

Tests use SQLite in memory (`phpunit.xml`), `QUEUE_CONNECTION=sync`,
`MAIL_MAILER=array`, and `CACHE_STORE=array`.

---

## 5. Request lifecycle and route registration

`bootstrap/app.php` registers:

- `routes/web.php` as web routes.
- `routes/console.php` as console routes.
- `/up` health endpoint.
- `routes/api_user.php` under prefix `/api` with the `api` middleware group.
- `routes/api_admin.php` under prefix `/api/admin` with the `api` middleware group.

Global middleware appended:

- `SetLocale` - negotiates `en`/`ar` from `Accept-Language`.
- `HandleFormDataOnNonPostRequests` - re-parses `multipart/form-data` and
  `application/x-www-form-urlencoded` bodies for `PUT`, `PATCH`, and `DELETE`
  so form-data updates work on non-POST methods.

Alias:

- `user.active` => `EnsureUserIsActive`.

Scheduler:

- `discounts:expire` every five minutes.

No custom exception renderer is configured (`withExceptions` is empty), so
validation errors use Laravel's standard `422` JSON shape.

---

## 6. Authentication and authorization

### 6.1 Guards

`config/auth.php` defines:

| Guard | Driver | Provider | Model |
| --- | --- | --- | --- |
| `web` | session | users | `App\Models\User` |
| `api-user` | jwt | users | `App\Models\User` |
| `api-admin` | jwt | admins | `App\Models\Admin` |

Default guard: `api-user`.

JWT defaults (`config/jwt.php`):

- TTL: 60 minutes.
- Refresh TTL: 20160 minutes (14 days).
- Blacklist enabled.

Both `User` and `Admin` implement `JWTSubject`.

### 6.2 Customer authentication

Verified behavior:

- Register creates a `User`, hashes the password, assigns the `Customer` role,
  links any matching guest newsletter subscription, and queues a verification
  email (48-hour token).
- Login rejects blocked users (`403`) and unverified emails (`401`), otherwise
  returns `access_token`, `token_type`, and the user.
- Email verification uses `GET /api/verify-email/{token}`.
- Forgot/reset password uses 64-character tokens stored on the user row with a
  one-hour expiry. The reset email is queued.
- Change password requires the current password.
- Logout invalidates the JWT.

Protected customer routes use `auth:api-user` plus `user.active`.

`EnsureUserIsActive`:

- Rejects unauthenticated requests with `401`.
- Rejects `status = blocked` with `403` and logs the user out.
- Compares the JWT `token_version` claim with `users.token_version`; a mismatch
  returns `401` ("session ended"). Admin user-status updates increment
  `token_version`, so blocking or reactivating a user invalidates old tokens.

Note: some service methods also check blocked status directly and throw `403`
(e.g. order creation and cancellation).

### 6.3 Admin authentication

Verified behavior:

- Invited admins are created inactive with a 64-character invitation token
  (48-hour expiry) and an unusable random password. The invitation email is
  queued.
- `POST /api/admin/register-invitation/set-password` consumes the invitation,
  sets the password, and activates the account.
- Login rejects inactive admins with `403`, then returns a JWT and admin data.
- Forgot/reset password and change password work like the customer flow but use
  `Admin` records.
- Logout invalidates the JWT.

Protected admin routes use only `auth:api-admin`. There is **no**
`user.active`-style middleware for admins and no permission middleware attached
to the routes.

### 6.4 RBAC

- `spatie/laravel-permission` tables and `HasRoles` are present on both `User`
  and `Admin`.
- `AppServiceProvider` registers `Gate::before`: a user with the `SuperAdmin`
  role on the `api-admin` guard is allowed everything.
- Roles and permissions can be managed through admin endpoints, and admin roles
  can be synced.
- **Verified limitation:** the admin routes themselves are protected only by
  authentication, not by `permission:` middleware or policy checks. RBAC data
  exists, but route-level enforcement is not wired up.

---

## 7. API conventions

### 7.1 Response envelope

Most controllers use `App\Traits\ApiResponse`:

Success:

```json
{
  "success": true,
  "message": "Localized message",
  "data": {}
}
```

Error:

```json
{
  "success": false,
  "message": "Localized message"
}
```

- Default success message key: `messages.success`.
- Default error message key: `messages.error`.
- Validation failures are **not** wrapped by this trait; Laravel returns the
  standard `422` body with `message`, `errors`, etc.

### 7.2 HTTP codes

Observed conventions:

- `200` for reads and updates.
- `201` for registration, order creation, and admin bundle creation.
- `400` default for `ApiResponse::error` unless a code is supplied.
- `401` invalid credentials, unverified email, unauthenticated, stale token.
- `403` blocked/inactive accounts, ownership violations.
- `404` missing or inaccessible resources.
- `422` validation errors, including business-rule validation raised through
  `ValidationException`.
- `500` unhandled exceptions.

### 7.3 Pagination

Paginated endpoints generally return a `pagination` object with some or all of:
`total`, `per_page`, `current_page`, `last_page`. The exact keys vary by
controller; check the controller before relying on a field.

### 7.4 Resources

- `app/Http/Resources/User/*` for customer responses.
- `app/Http/Resources/Admin/*` for admin responses.
- Resource classes are explicit array mappers; they do not automatically hide
  sensitive fields, so avoid adding secrets to models/resources casually.

### 7.5 Validation

- FormRequests live under `app/Http/Requests/Admin` and
  `app/Http/Requests/User`.
- `authorize()` is generally `true`; authorization is handled by route
  middleware and service-level ownership checks.
- Business-rule failures use `ValidationException::withMessages(...)` with
  localized messages.

---

## 8. Localization

- Supported locales: `en`, `ar`.
- `SetLocale` parses `Accept-Language`, honors quality factors, and falls back
  to `en`.
- Message keys live in `lang/en/messages.php` and `lang/ar/messages.php`.
- Controllers/services return message **keys** (e.g. `messages.cart.item_added`),
  and `ApiResponse` translates them with `__()`.
- Arabic has additional framework files (`auth.php`, `pagination.php`,
  `passwords.php`, `validation.php`); English currently only has `messages.php`
  and `validation.php`.
- When adding a user-facing message, add both English and Arabic entries.

---

## 9. Domain model and relationships

### 9.1 Core entities

| Model | Table | Notes |
| --- | --- | --- |
| `User` | `users` | JWT customer, soft deletes, roles, `token_version`, status `active`/`blocked` |
| `Admin` | `admins` | JWT admin, soft deletes, roles, `is_super`, `is_active`, invitation/reset tokens |
| `Title` | `titles` | Bilingual title, has many categories |
| `Category` | `categories` | Self-referential tree, belongs to title, has many products |
| `Product` | `products` | Bilingual, price/discount/stock, physical or e-copy |
| `ProductDetail` | `product_details` | One-to-one extended metadata |
| `ProductImage` | `product_images` | Many images per product |
| `CartItem` | `cart_items` | Per-user cart line (there is no active `Cart` aggregate) |
| `Cart` | `carts` | Legacy/unused; schema mismatch, see limitations |
| `Wishlist` | `wishlists` | Unique `(user_id, product_id)` |
| `AddressDetail` | `address_details` | Shipping snapshot per order |
| `Order` | `orders` | Order header, totals, status, payment status |
| `OrderItem` | `order_items` | Product snapshot line |
| `PaymentMethod` | `payment_methods` | Bilingual name only in schema |
| `DeliveryMethod` | `delivery_methods` | Bilingual name only in schema |
| `EbookAccessCode` | `ebook_access_codes` | One code per e-book unit |
| `OrderEbookCode` | `order_ebook_codes` | Links order item to reserved code |
| `Review` | `reviews` | Rating 1-5, boolean status |
| `Setting` | `settings` | Single-row store settings (delivery fee, VAT, policies) |
| `LandingBanner` | `landing_banners` | Home banners |
| `Faq` | `faqs` | Bilingual FAQ |
| `EmailSubscription` | `email_subscriptions` | Newsletter subscription |
| `Bundle` | `bundles` | Shareable product bundle with token |
| `BundleItem` | `bundle_items` | Product + quantity in a bundle |

### 9.2 Key relationships

```text
Title 1---* Category
Category 1---* Category (parent/children)
Category 1---* Product
Product 1---1 ProductDetail
Product 1---* ProductImage
Product 1---* CartItem
Product 1---* Wishlist
Product 1---* OrderItem
Product 1---* EbookAccessCode
Product 1---* BundleItem

User 1---* CartItem
User 1---* Wishlist
User 1---* Order
User 1---* Review
Order 1---* OrderItem
Order 1---* OrderEbookCode
Order 1---* EbookAccessCode (current reservation)
OrderItem 1---* OrderEbookCode
EbookAccessCode 1---1 OrderEbookCode (per reservation link)

Bundle 1---* BundleItem
Admin 1---* Bundle (created_by / updated_by)
```

Notes:

- `Order` uses `address_details_id` (not `address_id`) for its address relation.
- `Product` has both `category_id` and a one-to-one `detail`.
- `Bundle` and `BundleItem` use soft deletes; admin item removal uses force
  delete during sync.
- Audit columns (`created_by`, `updated_by`) exist on many tables and point to
  admins, but some models map them to `User` instead (e.g. `CartItem`,
  `Review`, `Wishlist`). This is an existing inconsistency.

---

## 10. Database schema notes

Verified tables and notable columns:

### `users`

`id`, `name`, `email` (unique), `email_verified_at`, `password`,
`remember_token`, verification token + expiry, password reset token + expiry,
`phone`, `avatar`, `status` enum(`active`,`blocked`), `token_version`,
terms/privacy flags, social provider/id, country/city/street/apartment,
timestamps, soft deletes.

### `admins`

`id`, `name`, `email` (unique), `password`, `is_super`, `avatar`, `phone`,
`remember_token`, invitation token + expiry, password reset token + expiry,
`is_active`, timestamps, soft deletes.

### `products`

`id`, `category_id`, bilingual names and short descriptions, `price`,
`discount_percentage`, discount start/end timestamps, `stock`, `status`,
`is_new_arrival`, `is_best_seller`, `is_e_copy`, `ebook_url` (added later),
`publisher` (NOT NULL), audit ids, timestamps, soft deletes.

There is no `title_*` or `description_*` column on `products`; those live in
`product_details`. Customer `ProductResource` exposes title/description fields
that come from the loaded `detail`, so keep the relation loaded.

### `orders`

`id`, `user_id`, `order_number` (unique), `address_details_id`,
`payment_method_id`, `delivery_method_id`, `subtotal`, `shipping`, `discount`,
`tax`, `total`, `status`, `payment_status`, terms/privacy flags,
`estimated_delivery_date`, `user_full_name`, `email`, `phone_to_number`,
`notes`, timestamps, soft deletes.

Status values are free-form strings at the database level. Application logic
uses: `pending`, `confirmed`, `out_for_delivery`, `delivered`, `cancelled`.

### `order_items`

`id`, `order_id`, `product_id`, `quantity`, `price`, `discount`,
`download_token` (unique, nullable), `download_url`, `download_expire_at`,
timestamps, soft deletes.

The download columns exist but the current e-book delivery flow uses access
codes and `ebook_url`, not these columns.

### `cart_items`

`id`, `user_id`, `product_id`, `quantity` (default 1), timestamps, soft
deletes, index on `(user_id, product_id)`.

There is no unique constraint on `(user_id, product_id)`; the service merges
duplicates in application code.

### `carts`

Only `id`, timestamps, soft deletes. This table is effectively unused and does
**not** match `App\Models\Cart` or `CartFactory`, which expect `user_id`.

### `ebook_access_codes`

`id`, `product_id`, `code` (unique), `used_at`, `order_id`, `order_item_id`,
timestamps, index on `(product_id, used_at)`.

### `order_ebook_codes`

`id`, `order_id`, `order_item_id`, `ebook_access_code_id`, timestamps, unique
`(order_item_id, ebook_access_code_id)`.

### `bundles`

`id`, bilingual name/description, `token` (64 chars, unique), `status`,
audit ids, timestamps, soft deletes, index on `status`.

### `bundle_items`

`id`, `bundle_id`, `product_id`, `quantity`, audit ids, timestamps, soft
deletes, unique `(bundle_id, product_id)`, index on `product_id`.

### Settings, banners, FAQs, subscriptions

- `settings`: delivery fee, VAT percentage/enabled, bilingual terms/privacy/
  return-policy text, `updated_by`.
- `landing_banners`: bilingual title/description, image, button fields,
  status, sort order.
- `faqs`: bilingual question/answer, `is_active`, sort order.
- `email_subscriptions`: unique email, optional user, active flag,
  subscribed/unsubscribed timestamps.

---

## 11. Business rules

### 11.1 Products and discounts

- Products are soft-deleted and can be active/inactive.
- Customer catalog only lists active products (`scopeActive`).
- `discount_percentage` is stored as a percentage (0-100).
- The `discount` accessor computes the **per-unit discount amount** and returns
  `0.00` when the discount window is not active.
- `has_offer` reflects whether the window is currently active.
- A discount is active when percentage > 0, `discount_start_at` is null or in
  the past, and `discount_end_at` is null or in the future.
- `discounts:expire` zeroes percentages whose `discount_end_at` has passed.
  It runs every five minutes and is also called at the start of the admin
  product index.
- Admin product create forces e-book stock to `0`; e-book stock is represented
  by unused access codes.
- Turning a product inactive removes it from all carts and wishlists
  (force delete).
- Deleting a product is blocked if it is referenced by any order item; admins
  are told to deactivate it instead.
- Product deletion removes images from storage, deletes detail/images, removes
  cart/wishlist references, then soft-deletes the product.
- Product image uploads accept `jpg`, `jpeg`, `png`, `webp` up to 5 MB.
- E-book products require `ebook_url` and reject non-zero stock.
- Product detail titles use the existing `product_details.title_en` and
  `product_details.title_ar` fields. Bulk import treats both as optional text;
  neither is related to the `titles` table.
- Admin book import endpoints are `GET /api/admin/products/import/template`
  and `POST /api/admin/products/import`; both use the existing `auth:api-admin`
  middleware.
- The generated XLSX template has these columns, in order:
  `Category*`, `Title (English)`, `Title (Arabic)`, `Name (English)*`, `Name (Arabic)*`,
  `Short Description (English)*`, `Short Description (Arabic)*`, `Price*`,
  `Discount Percentage`, `Discount Start At`, `Discount End At`, `Stock`,
  `Status*`, `New Arrival*`, `Best Seller*`, `E-Book*`, `E-Book URL`,
  `Publisher*`, `Author`, `Detail Publisher`, `Language`, `Pages`, `ISBN`,
  `Publication Date`, `Description (English)`, and `Description (Arabic)`.
- Required fields are the starred columns; `E-Book URL` is additionally required
  when `E-Book` is true. The template includes an instructions sheet and accepts
  up to 5000 populated book rows. Images are not spreadsheet-import fields.
- `Category` is the only related database record on a product. Its dropdown is
  generated from existing category names and allows one selection; it is a
  standard Excel validation list and is not searchable. Authors and publishers
  are plain text fields, not database relationships.
- `Status`, `New Arrival`, `Best Seller`, and `E-Book` use a hidden-list dropdown
  with `Enabled` and `Disabled`; imports convert these to boolean values. Discount
  start/end cells allow blank dates in `YYYY-MM-DD` format and imports reject
  invalid dates or an end date earlier than the start date. Excel date validation
  restricts values but does not guarantee a calendar popup.
- Import validates each row using product creation rules and creates each valid
  product through `ProductService`'s transaction. Row errors include the Excel
  row number. The response includes total rows, successful imports, failed-row
  count, and detailed failed rows. Exact duplicate ISBNs on non-deleted books
  are rejected when an ISBN is supplied; the project had no earlier product
  uniqueness rule.

Catalog filters (`ProductService::getAllForUser`):

- `categories[]` - category IDs.
- `formats[]` - `printed`, `ebook`, `both` (both means no format filter).
- `min_price`, `max_price`.
- `availability` - `in_stock` / `out_of_stock` based on `stock`.
- `filter` - `new_arrival`, `best_seller`, `has_offer`.
- `search` - names, short descriptions, detail author/title.
- `sort_by` - `price_asc`, `price_desc`, `name_asc`, `name_desc`, `newest`,
  `best_seller`; default `name_asc`.
- `per_page`, `page`.

### 11.2 E-book access codes

- Every e-book unit needs one unused `EbookAccessCode`.
- Available stock = count of codes with `used_at = null` for the product.
- Codes are globally unique.
- Empty/whitespace codes are skipped when adding a batch; duplicates abort the
  transaction with a validation error.
- Used codes cannot be deleted.
- Product delete cascades codes.
- Reserving codes marks `used_at`, sets `order_id`, and updates the product
  stock to the new available-code count (compatibility sync).
- Codes are attached to the order item and also recorded in
  `order_ebook_codes`.
- Cancelling an order releases its codes (clears `used_at`, `order_id`,
  `order_item_id`) and resyncs product stock.
- When an admin moves an order to `confirmed`, `EbookDeliveryService` queues one
  delivery email per e-book order item. The email includes the product name,
  `ebook_url`, and the reserved codes. Order status is not changed by delivery.

### 11.3 Bundles

- Admin creates a bundle with bilingual name/description, optional status, and
  a non-empty list of `items` (`product_id`, `quantity`), where products are
  distinct and not soft-deleted.
- A 64-character token is generated on create and **never changes on update**,
  so existing share links keep working.
- `share_url` = `FRONTEND_URL` + `/bundles/{token}`.
- Bundle list supports `status` and `search` (name_en, name_ar, token).
- Bundle update syncs items: removes missing products (force delete), updates
  quantities for existing products, creates new items.
- Bundle delete soft-deletes items and the bundle.
- Status toggle flips `status` through the normal update path.
- **Stock validation on create/update:** every item is checked before writing.
  - Physical product: requested quantity must be <= current `stock`.
  - E-book: requested quantity must be <= unused access-code count.
  - Failures return `422` with `items.{index}.quantity`.
- **Public preview:** `GET /api/bundles/{token}` is public but returns `404`
  when the bundle is missing or inactive.
- **Add to cart:** requires a customer JWT. The whole bundle is validated
  before any write:
  - Bundle must exist and be active.
  - Bundle must contain at least one item.
  - Every product must exist and be active.
  - Existing cart quantity + bundle quantity must not exceed physical stock or
    unused e-book codes.
  - All additions happen inside one database transaction, so a failure leaves
    no partial cart.
  - Response returns the bundle, updated cart items, cart summary, and a
    frontend `redirect_url` (`FRONTEND_URL` + `/cart`).
- Bundle usage does **not** decrement stock or reserve e-book codes; stock is
  consumed only when an order is placed.

### 11.4 Cart

- Cart lines are stored in `cart_items` by `user_id`; there is no separate active
  cart row.
- `GET /api/cart` first force-deletes lines whose product is missing or
  inactive, then returns items with product images/detail.
- Adding a product:
  - Product must exist.
  - Quantity defaults to 1.
  - For e-books: existing quantity + new quantity <= unused codes.
  - For physical: existing quantity + new quantity <= stock.
  - Existing line is incremented; otherwise a new line is created.
- Updating quantity:
  - Ownership is enforced.
  - Quantity < 1 deletes the line.
  - Otherwise stock/code availability is re-checked.
- Removing a line enforces ownership.
- Clearing deletes all lines for the authenticated user.
- Summary calculation:
  - `subtotal` = sum(price * quantity).
  - `discount_amount` = sum(per-unit active discount * quantity).
  - `discount_percentage` = discount / subtotal * 100.
  - `shipping` = settings `delivery_fee`.
  - `tax_percentage` = settings VAT if enabled, else 0.
  - `tax` = (subtotal - discount) * tax percentage.
  - `total` = subtotal - discount + shipping + tax.
  - All money rounded to 2 decimals.

### 11.5 Orders

Order creation (`POST /api/orders`):

1. User must be authenticated and not blocked.
2. Cart must not be empty.
3. Stock/code availability is validated for every line.
4. E-book availability is re-checked with `lockForUpdate` inside the transaction.
5. Address snapshot is created.
6. Line subtotal and active per-unit discount are snapshotted.
7. Shipping defaults to `settings.delivery_fee` unless the request supplies it.
8. VAT applies only when `settings.vat_enabled`; rate is `vat_percentage`.
9. Total = (subtotal - discount) + shipping + tax.
10. Order is created with `status = pending`, `payment_status = unpaid`, and a
    generated `ORD-YYYYMMDDHHMMSS-XXXX` number.
11. Order items are created.
12. E-book codes are reserved and attached to the matching order item.
13. Physical stock is decremented.
14. Cart items are deleted.

Request address fields: `full_name`, `phone`, `address_line1`, optional
`address_line2`, `city`, optional `state`, `postal_code`, `country`.
Also required: `payment_method_id`, `delivery_method_id`,
`terms_and_condition_agreed`; optional: `shipping`, `notes`,
`privacy_policy_agreed`.

Status flow (admin):

```text
pending -> confirmed -> out_for_delivery -> delivered
```

- Cancellation is allowed only from `pending` (admin and customer).
- `confirmed` and `out_for_delivery` cannot be cancelled.
- `delivered` and `cancelled` are terminal.
- Any other transition is rejected with a localized validation error.
- Admin cancellation releases e-book codes.
- Moving to `confirmed` triggers e-book delivery emails when e-book items exist.
- Payment status starts as `unpaid`; no payment gateway integration was found.

Customer order access is scoped to the authenticated user. Viewing another
user's order returns `404` from the controller.

### 11.6 Reviews

- Any visitor can list reviews through `GET /api/reviews`; filters include
  `product_id`, `user_id`, and `status`.
- Creating a review requires authentication and a **delivered** order that
  contains the product.
- Rating must be 1-5; comment is required (max 5000).
- New reviews are created with `status = true` (visible immediately). Admin can
  toggle status.
- There is no database uniqueness constraint preventing multiple reviews by the
  same user for the same product.

### 11.7 Wishlist

- Requires customer auth.
- Listing removes wishlist rows whose product is missing/inactive.
- Adding does not check stock.
- Deleting enforces ownership and force-deletes the row.
- Database unique constraint: one row per `(user_id, product_id)`.

### 11.8 Newsletter and contact

- `POST /api/subscribe` works for guests and authenticated users.
- Emails are normalized (trim + lowercase), unique.
- Re-subscribing reactivates and refreshes timestamps; a guest subscription can
  be linked to a user later.
- Registration links a matching guest subscription to the new user.
- `POST /api/unsubscribe` deactivates the subscription.
- `POST /api/contact-us` queues a `ContactUsMail` to `CONTACT_US_EMAIL`.

### 11.9 Settings, policies, banners, FAQs

- Settings are effectively a single row; `getSettings` returns the first row or
  a new unsaved `Setting`.
- If no settings row exists, delivery fee is treated as `0`, VAT disabled
  behavior depends on the caller (`CartService` uses null-safe defaults;
  `Setting` static helpers have their own defaults: VAT 14%, enabled true).
- Public policy endpoint returns settings-based bilingual policy text.
- Public home endpoint returns active banners ordered by `sort_order`.
- Public FAQs return active FAQs ordered by `sort_order`, then `id`.
- Admin CRUD exists for banners, FAQs, titles, categories, products, payment
  methods, delivery methods, and bundles.

### 11.10 Categories

- Categories form an arbitrary-depth tree via `parent_id`.
- Admin list returns root categories with three levels of children eager-loaded.
- Customer list returns active root categories with three levels of active
  children, ordered by `name_en`.
- Subcategories endpoint exists for authenticated customers:
  `GET /api/categories/{category}/subcategories`.
- `is_filter` marks categories intended for filter UIs.
- Deleting a category is blocked if it has children or products.

---

## 12. Services

| Service | Responsibility |
| --- | --- |
| `AdminService` | Admin invitations, listing/filtering, status, profile updates, role sync |
| `AuthService` | Admin set/reset/change password |
| `BundleService` | Bundle CRUD, token generation, availability validation, add-to-cart |
| `CartService` | Cart cleanup, add/update/remove/clear, summary math |
| `CategoryService` | Category tree CRUD, image handling, deletion guards |
| `DeliveryMethodService` | Delivery method CRUD |
| `EbookAccessCodeService` | Code inventory, reservation, release, order-item linking |
| `EbookDeliveryService` | Queues e-book delivery emails on order confirmation |
| `EmailSubscriptionService` | Normalize/subscribe/unsubscribe/link guest subscriptions |
| `FaqService` | FAQ CRUD and active ordering |
| `LandingBannerService` | Banner CRUD, image handling, active banners |
| `MediaService` | Store/replace/delete files on the `public` disk with UUID names |
| `OrderService` | Order listing/creation/cancellation, stock, codes, totals, status flow |
| `PaymentMethodService` | Payment method CRUD |
| `PermissionService` | Permission CRUD |
| `ProductService` | Catalog filters, product CRUD, images/detail, deletion guards |
| `ProductImportService` | Dynamic XLSX template and row-level admin book import |
| `ReviewEligibilityService` | Delivered-order eligibility check |
| `ReviewService` | Admin review listing/status |
| `RoleService` | Role CRUD |
| `SettingsService` | Read/update the single settings row |
| `TitleService` | Title CRUD |
| `UserAuthService` | Customer registration/login support, verification, passwords |
| `UserProfileService` | Profile read/update, avatar, order/wishlist statistics |
| `UserReviewService` | Customer review creation/listing |
| `UserService` | Admin user listing, status changes, token invalidation |
| `UserSocialAuthService` | Social login provider handling |
| `WishlistService` | Wishlist listing/add/delete with ownership |

Services are generally constructor-injected. `OrderService` has a nullable
`EbookAccessCodeService` constructor argument and resolves it from the container
when omitted, which keeps older tests/manual instantiation working.

---

## 13. Route map

### 13.1 Customer/public: `/api`

Public:

| Method | Path | Purpose |
| --- | --- | --- |
| POST | `/register` | Customer registration |
| POST | `/login` | Customer login |
| GET | `/verify-email/{token}` | Verify email |
| POST | `/forgot-password` | Send reset email |
| POST | `/reset-password` | Reset password |
| GET | `/titles` | List titles |
| GET | `/categories` | Active category tree |
| GET | `/products` | Product catalog with filters |
| GET | `/products/{product}` | Product detail |
| GET | `/reviews` | List reviews |
| GET | `/faqs` | Active FAQs |
| GET | `/bundles/{token}` | Public bundle preview |
| GET | `/policies` | Terms/privacy/return policies |
| POST | `/subscribe` | Newsletter subscribe |
| POST | `/unsubscribe` | Newsletter unsubscribe |
| POST | `/contact-us` | Contact form |
| POST | `/social/{provider}/login` | Social login |
| GET | `/home` | Active banners |

Protected (`auth:api-user` + `user.active`):

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/categories/{category}/subcategories` | Subcategories |
| POST | `/logout` | Logout |
| POST | `/change-password` | Change password |
| GET | `/profile` | Profile + statistics |
| PUT | `/profile` | Update profile/avatar |
| GET | `/wishlist` | List wishlist |
| POST | `/wishlist` | Add to wishlist |
| DELETE | `/wishlist/{wishlist}` | Remove wishlist item |
| GET | `/cart` | Cart + summary |
| POST | `/cart` | Add cart item |
| PUT | `/cart/{cart_item}` | Update quantity |
| DELETE | `/cart` | Clear cart |
| DELETE | `/cart/{cart_item}` | Remove cart item |
| POST | `/bundles/{token}/add-to-cart` | Add all bundle products atomically |
| GET | `/orders` | List own orders |
| POST | `/orders` | Place order |
| GET | `/orders/{order}` | Own order detail |
| PATCH | `/orders/{order}/cancel` | Cancel pending order |
| GET | `/orders/{order}/invoice` | Download invoice PDF |
| GET | `/orders/{order}/invoice/view` | View invoice PDF |
| POST | `/reviews` | Create review |

Invoice routes use `OrderInvoiceController` and are registered inside the
customer middleware group but point at `/orders/{order}`; ownership is enforced
by the controller.

### 13.2 Admin: `/api/admin`

Public:

| Method | Path | Purpose |
| --- | --- | --- |
| POST | `/login` | Admin login |
| POST | `/register-invitation/set-password` | Accept invitation |
| POST | `/forgot-password` | Send reset email |
| POST | `/reset-password` | Reset password |

Protected (`auth:api-admin`):

| Method | Path | Purpose |
| --- | --- | --- |
| POST | `/logout` | Logout |
| POST | `/change-password` | Change password |
| GET/PUT | `/settings` | Read/update settings |
| apiResource | `/titles` | Title CRUD |
| GET | `/categories/select` | Category select options |
| apiResource | `/categories` | Category CRUD |
| apiResource | `/products` | Product CRUD |
| apiResource | `/delivery-methods` | Delivery method CRUD |
| apiResource | `/payment-methods` | Payment method CRUD |
| GET/POST | `/roles` | List/create roles |
| GET/PUT/DELETE | `/roles/{role}` | Role detail/update/delete |
| GET/POST | `/permissions` | List/create permissions |
| DELETE | `/permissions/{permission}` | Delete permission |
| GET/PUT | `/admins/{admin}/roles` | Show/sync admin roles |
| DELETE | `/admins/{admin}/roles/{role}` | Remove role from admin |
| GET | `/landing-banners` | List banners |
| GET | `/landing-banners/{landing_banner}` | Banner detail |
| POST | `/landing-banners` | Create banner |
| PUT | `/landing-banners/{landing_banner}` | Update banner |
| DELETE | `/landing-banners/{landing_banner}` | Delete banner |
| GET | `/users` | List users |
| GET | `/users/{id}` | User detail |
| PUT | `/users/{user}/status` | Activate/block user (increments token version) |
| POST | `/admins` | Invite admin |
| GET | `/admins` | List admins |
| GET | `/admins/{admin}` | Admin detail |
| PUT | `/admins/{id}/status` | Activate/deactivate admin |
| PUT | `/admins/{admin}` | Update admin |
| GET | `/reviews` | List reviews |
| GET | `/reviews/{id}` | Review detail |
| PUT | `/reviews/{review}/status` | Activate/deactivate review |
| GET | `/orders` | List orders |
| GET | `/orders/{id}` | Order detail |
| PUT | `/orders/{order}/status` | Change status (enforces flow) |
| GET | `/orders/{order}/invoice` | Download invoice |
| GET | `/orders/{order}/invoice/view` | View invoice |
| apiResource | `/bundles` | Bundle CRUD |
| PUT | `/bundles/{bundle}/status` | Toggle bundle status |
| apiResource | `/faqs` | FAQ CRUD |
| PUT | `/faqs/{faq}/status` | Toggle FAQ status |
| GET | `/products/{product}/access-codes` | List e-book codes |
| POST | `/products/{product}/access-codes` | Add e-book codes |
| DELETE | `/access-codes/{ebookAccessCode}` | Delete unused code |

`apiResource` includes index, store, show, update, and destroy.

---

## 14. Media and storage

- `MediaService` stores files on the `public` disk under:
  - `products/`
  - `categories/`
  - `banners/`
  - `admins/`
  - `users/`
- Filenames are UUID + original extension.
- `replace()` deletes the old file after storing the new one (verify the exact
  method in `MediaService` before changing it).
- `storage:link` is required for public URLs.
- Production `.env.PROD` sets `FILESYSTEM_DISK=local`, but media explicitly uses
  the `public` disk, so the public link still matters.
- Product/category/banner/user/admin deletion paths attempt to remove managed
  files. Direct deletion of the underlying row elsewhere would leave orphaned
  files.

---

## 15. Queue, mail, and notifications

- Default queue connection: `database` (`QUEUE_CONNECTION=database`).
- `SendEmailJob` implements `ShouldQueue`:
  - 3 tries.
  - Backoff 60 / 300 / 900 seconds.
  - Logs failures and rethrows.
- All application mail is sent through `SendEmailJob`:
  - `UserVerificationMail`
  - `UserPasswordResetMail`
  - `AdminInvitationMail`
  - `AdminPasswordResetMail`
  - `EbookDeliveryMail`
  - `ContactUsMail`
- Mailables use frontend URLs from `config('app.frontend_url')` and
  `config('app.frontend_admin_url')`.
- Production `.env.PROD` sets `MAIL_MAILER=log`; verify this is intentional
  before expecting real email delivery.
- A queue worker must run for queued mail to leave the application.
- There is no broadcasting, SMS, or push-notification implementation found.

---

## 16. Scheduled tasks and console commands

| Command | Schedule | Purpose |
| --- | --- | --- |
| `discounts:expire` | Every 5 minutes | Zero expired product discount percentages |

The command is scheduled in `bootstrap/app.php` through
`withSchedule(...)`. It requires `php artisan schedule:work` or a cron entry in
production.

`routes/console.php` only contains the default `inspire` command.

---

## 17. Testing

- Test suite lives in `tests/Feature` and `tests/Unit`.
- Feature tests use `RefreshDatabase` where they touch the database.
- JWT tokens in tests are generated with `JWTAuth::fromUser(...)`.
- Current coverage includes:
  - Bundle admin CRUD, public preview, add-to-cart, stock/e-book validation.
  - E-book access codes and full e-book order flow.
  - E-book URL validation.
  - Admin form-data updates, role updates, category select, image upload,
    product/category deletion.
  - Bilingual FAQs, localization.
  - Blocked users, contact us, discount windows, newsletter, policies and
    agreements, user order cancellation.
- Last observed full run: **124/128 passing**.
- The 4 failures are in `EbookOrderFlowTest` and reference
  `table carts has no column named user_id`. They are caused by the `carts`
  schema mismatch described below and are unrelated to the bundle feature.
- `BundleTest` was passing independently (13 tests, 95 assertions) at the time
  of writing.

Run:

```bash
composer test
# or
php artisan test
# focused
php artisan test --filter=BundleTest
```

---

## 18. Known inconsistencies and gaps

These are verified issues or uncertainties. Do not silently "fix" them as part
of unrelated work; confirm intent first.

### Schema drift

- `carts` has no `user_id`, but `Cart`, `CartFactory`, and some tests expect it.
  This causes the 4 `EbookOrderFlowTest` failures.
- `payment_methods` and `delivery_methods` migrations only define bilingual
  names plus audit/timestamps/soft deletes, while factories/tests may set
  `status`, `price`, and description fields. Do not assume those columns exist
  in a freshly migrated database.
- `cart_items`, `reviews`, `images`, and `product_images` migrations do not
  define all audit columns that some models/resources reference.
- `products.publisher` is NOT NULL in the migration, but `ProductService`
  writes `null` when the value is omitted. The admin request requires it, so
  normal API usage is safe; direct service calls are not.

### Authorization

- RBAC roles/permissions exist, but admin routes are enforced only by JWT
  authentication. There is no `permission:` middleware on admin routes.
- `SuperAdmin` bypass exists via `Gate::before`, but only matters if gates or
  policies are actually invoked.

### Orders and payments

- No payment gateway, webhook, refund, or payment-status transition logic was
  found. Orders start `unpaid` and stay there unless something outside this
  repository changes them.
- Order status values are strings; there is no database enum/check constraint.
- `estimated_delivery_date` exists but is not populated by order creation.

### E-books

- `order_items.download_token/download_url/download_expire_at` exist but are not
  used by the current delivery flow.
- E-book delivery depends on `products.ebook_url` and reserved codes; if either
  is missing, the email content is incomplete.
- Reserve/release keeps `products.stock` in sync with unused code count for
  compatibility, so e-book stock is derived data, not an independent quantity.

### Bundles

- Bundle tokens are permanent; there is no rotation endpoint.
- Disabling a bundle does not remove already-added cart items.
- Bundle preview is public, but adding to cart requires a customer account.
- Bundle item quantities are validated against current stock/codes at admin save
  time and again at add-to-cart time, but stock can change in between.

### Repository / deployment

- No Dockerfile, CI pipeline, or deployment manifest exists.
- Deployment configuration is limited to `.env.PROD`, `composer.json` scripts,
  and framework defaults.
- README is outdated (Laravel 11, PHP 8.2+).
- `.env.PROD` uses `MAIL_MAILER=log`, `FILESYSTEM_DISK=local`,
  `CACHE_STORE=database`, `QUEUE_CONNECTION=database`, `SESSION_DRIVER=database`.
- The repository name/description in `composer.json` is still the Laravel
  skeleton; do not infer the app identity from it.
- Bundle feature files are currently uncommitted/untracked in the working tree.

---

## 19. Deployment notes

Verified from `.env.PROD` and config:

- `APP_ENV=production`, `APP_DEBUG=false`.
- `APP_URL=https://prime-elements-backend-production.up.railway.app`.
- `FRONTEND_URL=https://pmelements.com`.
- `FRONTEND_ADMIN_URL=https://admin.pmelements.com`.
- Timezone: `Africa/Cairo`.
- Database: MySQL.
- Cache/session/queue: database-backed.
- Log channel: `stack`, level `debug`.
- Mail: `log` (no real delivery unless changed).

Operational requirements:

1. Run `php artisan migrate --force`.
2. Run `php artisan storage:link`.
3. Run a queue worker (`php artisan queue:work`).
4. Run the scheduler (`php artisan schedule:work` or cron for
   `schedule:run`).
5. Ensure writable `storage/` and `bootstrap/cache/`.
6. Set `JWT_SECRET` via `php artisan jwt:secret` if not already present.

No deployment scripts or health checks beyond `/up` were found. Railway is
implied by the production URL, but no Railway config file exists in the repo.

---

## 20. Change checklist for future work

Before changing behavior:

- [ ] Read this reference and the relevant service/controller/request.
- [ ] Check whether the route is customer, admin, or public.
- [ ] Confirm the guard and ownership checks.
- [ ] Check stock/e-book code implications if products or orders are involved.
- [ ] Add/update English and Arabic message keys.
- [ ] Keep the `{success, message, data}` envelope for controller responses.
- [ ] Re-check schema against migrations; do not trust model fillable fields
      alone where schema drift exists.
- [ ] Add/adjust feature tests.
- [ ] Run the focused tests, then the full suite.
- [ ] Update this document if routes, schema, rules, or cross-cutting behavior
      changed.

When reporting results:

- State what was verified and what remains uncertain.
- Do not claim a behavior works merely because the README or a docblock says so.
- Mention pre-existing failures separately from new ones.
