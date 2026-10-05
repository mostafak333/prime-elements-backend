# Backend Features Report — Frontend Integration Guide

> Prepared for the frontend team. All endpoints below are live on the backend.
> Base URL (dev): `http://127.0.0.1:8000` | Base URL (prod): `https://prime-elements-backend-production.up.railway.app`

---

## 1. Standard API Envelope

Every endpoint returns the same wrapper:

```json
{
  "success": true,
  "message": "Operation message",
  "data": { /* payload */ }
}
```

- `success`: `boolean` (`true`/`false`)
- `message`: `string`
- `data`: the actual payload (object, array, or `null`)
- Errors return HTTP 4xx/5xx with `{ "success": false, "message": "..." }` (validation errors from Laravel send a `422` with a `errors` object).

---

## 2. Policy & Legal Pages (T&C, Privacy, Return/Exchange)

**Purpose:** Fix the dead "Terms & Conditions" / "Privacy Policy" / "Return & Exchange" buttons by serving the editable legal content from the backend.

### Public endpoint — read content

```
GET {{BASE_URL}}/api/policies
```

No auth. Response `data` (both languages are ALWAYS returned; pick the one you need):

```json
{
  "terms_conditions_en": "<h1>Terms &amp; Conditions...</h1> ...",
  "terms_conditions_ar": "<h1 style=\"direction:rtl\">الشروط والأحكام...</h1> ...",
  "privacy_policy_en": "<h1>Privacy Policy...</h1> ...",
  "privacy_policy_ar": "<h1 style=\"direction:rtl\">سياسة الخصوصية...</h1> ...",
  "return_exchange_policy_en": "<h1>Return &amp; Exchange Policy...</h1> ...",
  "return_exchange_policy_ar": "<h1 style=\"direction:rtl\">سياسة الاسترجاع والاستبدال...</h1> ..."
}
```

- Content is **HTML** (full styled documents supported — headings, paragraphs, lists, bold, links). Render it as HTML, do not escape.
- Every policy field exists as `_en` and `_ar`; if a language is empty the field returns `null` — show a fallback in the UI.

### Admin endpoints — edit content

```
GET  {{BASE_URL}}/api/admin/settings        # full settings incl. policy content
PUT  {{BASE_URL}}/api/admin/settings        # update settings/policies
```

Auth: `Authorization: Bearer <admin-token>`.

`PUT` accepts **partial** updates (only send fields you want to change), all optional:

| Field                     | Type    | Notes                          |
| ------------------------- | ------- | ------------------------------ |
| `terms_conditions_en`     | string  | null allowed                   |
| `terms_conditions_ar`     | string  | null allowed                   |
| `privacy_policy_en`       | string  | null allowed                   |
| `privacy_policy_ar`       | string  | null allowed                   |
| `return_exchange_policy_en` | string | null allowed                  |
| `return_exchange_policy_ar` | string | null allowed                  |
| `delivery_fee`            | number  | >= 0                           |
| `vat_percentage`          | number  | 0–100                          |
| `vat_enabled`             | boolean |                                |

- Admin UI must provide separate editors for English and Arabic per policy (both required fields should be saved together).

---

## 2b. FAQs — Bilingual

```
GET  {{BASE_URL}}/api/faqs              # public, active only, sorted by sort_order
GET  {{BASE_URL}}/api/admin/faqs        # admin, paginated (?per_page=)
POST {{BASE_URL}}/api/admin/faqs        # create
PUT  {{BASE_URL}}/api/admin/faqs/{id}   # update
DELETE {{BASE_URL}}/api/admin/faqs/{id} # delete
```

Auth: `Authorization: Bearer <admin-token>` (except the public `GET /api/faqs`).

Both languages are ALWAYS returned on every side. Payload shape:

```json
{
  "id": 1,
  "question_en": "How do returns work?",
  "question_ar": "كيف تعمل عملية الاسترجاع؟",
  "answer_en": "You can request a return within 7 days.",
  "answer_ar": "يمكنك طلب الاسترجاع خلال 7 أيام.",
  "is_active": true,
  "sort_order": 1
}
```

`POST` requires all four fields (`question_en`, `question_ar`, `answer_en`, `answer_ar`) plus optional `is_active`, `sort_order`. `PUT` is partial (`sometimes`).

---

## 2d. Contact Us — Public Message Form

```
POST {{BASE_URL}}/api/contact-us
```

No auth. Sends the customer message by email to the store's configured contact mailbox (set via `CONTACT_US_EMAIL`).

| Field       | Type   | Notes             |
| ----------- | ------ | ----------------- |
| `full_name` | string | required          |
| `email`     | string | required, valid   |
| `subject`   | string | required          |
| `message`   | string | required          |

Response is `{ success: true }` (v200). The store's reply-to is set to the sender's `email`.

---

## 2c. Home Banners — Bilingual

```
GET  {{BASE_URL}}/api/banners          # public
GET  {{BASE_URL}}/api/admin/landing-banners    # admin, paginated
POST {{BASE_URL}}/api/admin/landing-banners    # create
PUT  {{BASE_URL}}/api/admin/landing-banners/{id} # update
DELETE {{BASE_URL}}/api/admin/landing-banners/{id} # delete
```

Both languages are ALWAYS returned. User-side payload shape:

```json
{
  "id": 4,
  "title_en": "Bestsellers You Can't Put Down",
  "title_ar": "الأكثر مبيعًا التي لا تستطيع تركها",
  "description_en": "Discover the books everyone is reading right now.",
  "description_ar": "اكتشف الكتب التي يقرأها الجميع الآن.",
  "image_url": "https://api.pmelements.com/storage/banners/xxxx.png",
  "button": {
    "enabled": true,
    "name_en": "Shop Bestsellers",
    "name_ar": "تسوق الأكثر مبيعًا",
    "link": "https://pmelements.com/best-sellers"
  }
}
```

Admin resource returns the same fields flattened (`button_enabled`, `button_name_en`, `button_name_ar`, `button_link`). `POST` requires `title_en` (+ optional `title_ar`, `description_en/ar`, image upload); `button_name_en`/`button_link` required when `button_enabled` is `true`.

---

## 3. Registration — Mandatory Legal Agreement

Endpoint `POST /api/register` now **requires** the user to accept both agreements.

| Field                        | Type    | Rule                          |
| ---------------------------- | ------- | ----------------------------- |
| `name`                       | string  | required, max 255             |
| `email`                      | string  | required, email, unique       |
| `password`                   | string  | required, min 8               |
| `password_confirmation`      | string  | required, must match password |
| `terms_and_conditions_agreed` | boolean | **required, must be `true`**   |
| `privacy_policy_agreed`      | boolean | **required, must be `true`**   |

Sending `false`, or omitting them, returns `422` with messages like:
`"You must agree to the terms and conditions."`

Storefront rule: checkboxes must be checked (and linked to the `/api/policies` content) before the form submits.

---

## 4. Order Checkout — Mandatory Agreement + Discounts in Order

Endpoint: `POST /api/orders` (auth: user token). Full payload:

```json
{
  "address": {
    "full_name": "Test User",
    "phone": "01111111111",
    "address_line1": "23 Tahrir St",
    "address_line2": "Apt 5",
    "city": "Cairo",
    "state": "",
    "postal_code": "11511",
    "country": "EG"
  },
  "payment_method_id": 1,
  "delivery_method_id": 1,
  "notes": "optional",
  "terms_and_condition_agreed": true,
  "privacy_policy_agreed": true
}
```

- `terms_and_condition_agreed` and `privacy_policy_agreed` are **required and must be `true`** (same messaging as registration).
- `shipping`, `notes` optional.
- The order is created from the user's **current cart** with the discounts applied (see section 5).

Order response `data` now includes discount + agreement fields:

```json
{
  "id": 12,
  "order_number": "PE-20260913-0012",
  "status": "pending",
  "payment_status": "unpaid",
  "subtotal": 200.0,
  "shipping": 50.0,
  "discount_amount": 20.0,
  "discount_percentage": 10.0,
  "tax": 18.3,
  "total": 248.3,
  "terms_and_condition_agreed": true,
  "privacy_policy_agreed": true,
  "items": [
    {
      "id": 1,
      "quantity": 2,
      "price": 100.0,
      "discount_amount": 10.0,
      "discount_percentage": 10.0,
      "product": { ... }
    }
  ]
}
```

Order list/detail: `GET /api/orders`, `GET /api/orders/{order}` — same shape. Admin order views (`GET /api/admin/orders`, `GET /api/admin/orders/{id}`) include the same discount/agreement fields plus a `user` sub-object.

---

## 5. Product Discounts (Percentage + Time Window)

The big one. Products now support a **percentage discount** with an optional **active time window**.

### How it works (important for UI)

- `discount_percentage` is **stored only** (0–100, decimals allowed). There is **no stored money amount** — `discount_amount` is **computed** server-side as `price × percentage / 100`.
- Both `discount_start_at` and `discount_end_at` are optional (`null` = no boundary). Active shop window = **`start <= now < end`**.
- When a product is inside the window → `has_offer: true` and percentages/amounts are real.
- When **outside** the window:
  - `has_offer: false`
  - `discount_percentage` → `"0.00"` (customer view)
  - `discount_amount` → `"0.00"`
  - `price_after_discount` → full price
  - The backend **auto-zeroes `discount_percentage` to 0 in the database** as soon as the window ends (no cron needed). Upcoming offers (start not reached) keep their value so they stay scheduled.
- All money fields are strings with 2 decimals (`"5.00"`, `"100.00"`); discount percentages in responses are also 2-decimal strings in product payloads and **floats** in cart/order summaries. Cast before use.

### Customer product payload — `GET /api/products` (public)

Query params (all optional, server-validated):

| Param              | Type                       | Notes                                        |
| ------------------ | -------------------------- | -------------------------------------------- |
| `page` / `per_page`| integer                    | `per_page` max 100                           |
| `categories[]`     | integer[]                  | filter by category ids                       |
| `formats[]`        | `printed` `ebook` `both`   | format filter                                |
| `min_price`        | number                     | >= 0                                         |
| `max_price`        | number                     | >= `min_price`                               |
| `availability`     | `in_stock` `out_of_stock`  |                                              |
| `sort_by`          | `price_asc` `price_desc` `name_asc` `name_desc` `newest` `best_seller` | |
| `search`           | string                     | name/description/author/title (EN & AR)      |
| `filter`           | `new_arrival` `best_seller` `has_offer` | combine filters by OR |

`GET /api/products/{product}` returns the same single object.

```json
{
  "id": 1,
  "category_id": 2,
  "name_en": "The Silent Patient",
  "price": "100.00",
  "price_after_discount": "90.00",
  "discount_percentage": "10.00",
  "discount_amount": "10.00",
  "has_offer": true,
  "discount_start_at": "2026-09-13 00:00:00",
  "discount_end_at": "2026-09-30 23:59:59",
  "stock": 5,
  "...": "..."
}
```

| Field                  | Type             | Notes                                                     |
| ---------------------- | ---------------- | --------------------------------------------------------- |
| `price`                | string (decimal) | base price (also `price` on item = unit base price)        |
| `price_after_discount` | string (decimal) | `0.00` when discount inactive → equals `price`             |
| `discount_percentage`  | string (decimal) | **effective** — `"0.00"` when window inactive              |
| `discount_amount`      | string (decimal) | **computed** per unit — `"0.00"` when inactive             |
| `has_offer`            | boolean          | `true` only inside the window                              |
| `discount_start_at`    | string \| null   | `"Y-m-d H:i:s"` (local store time)                         |
| `discount_end_at`      | string \| null   | `"Y-m-d H:i:s"` (local store time)                         |

### Admin product payload — `GET /api/admin/products` (auth)

Same fields, except `discount_percentage` is the **raw stored value** (kept for product management). `has_offer`, `discount_amount`, `price_after_discount` still reflect the window.

### Creating/updating a discount (admin)

`POST /api/admin/products` / `PUT /api/admin/products/{product}` — partial allowed:

| Field                   | Type           | Rules |
| ----------------------- | -------------- | ----- |
| `discount_percentage`   | number         | nullable, 0–100 |
| `discount_start_at`     | string `"Y-m-d H:i:s"` or `"Y-m-d"` | nullable, date |
| `discount_end_at`       | string `"Y-m-d H:i:s"` or `"Y-m-d"` | nullable, date |

- Use `null` to clear.
- TIMES NOTE: send the times in local store time (Africa/Cairo). The backend interprets them as store wall-clock time.
- Example to schedule: `discount_percentage: 10`, `discount_start_at: "2026-09-13 00:00:00"`, `discount_end_at: "2026-09-30 23:59:59"`.

### Cart summary (auth) — `GET /api/cart`

```json
{
  "items": [ { "id": 1, "quantity": 2, "product": { ...see product payload... } } ],
  "summary": {
    "total_items": 2,
    "subtotal": 200.0,
    "discount_amount": 20.0,
    "discount_percentage": 10.0,
    "shipping": 50.0,
    "tax_percentage": 10.0,
    "tax": 18.3,
    "total": 248.3
  }
}
```

Money math (per item): `price` (unit) × `quantity`; discount uses the **effective** per-unit amount (0 if expired). `subtotal = Σ price×qty`, `discount_amount = Σ effectiveDiscount×qty`, `discount_percentage = discount_amount/subtotal×100`, then shipping and VAT applied. `discount_percentage`/amounts here are **numbers** (floats).

**Cart/wishlist cleanup:** items whose product was deleted/deactivated are automatically **removed** server-side when the cart/wishlist is fetched. `items[].product` is never `null` — treat the list as authoritative; the summary only counts remaining valid items.

### Filtering — show only discounted products

```
GET /api/products?filter=has_offer
```

Use the `filter` param with one of `new_arrival`, `best_seller`, `has_offer` (multiple values are OR-combined).

---

## 6. Time & Formatting Summary (read me first)

- **Timezone:** `Africa/Cairo` (configurable via `APP_TIMEZONE`). All `*_at` and `discount_start_at`/`discount_end_at` dates in responses are **local store time**, format `Y-m-d H:i:s`. `estimated_delivery_date` is `Y-m-d`.
- `created_at`/`updated_at` are also returned in local store time.
- Money strings: `"100.00"` (2dp, dot). Parse with your money helper; render with your currency.
- Discount money value is exposed ONLY as `discount_amount` (accompanied by `discount_percentage`). There is **no bare `discount` field** and **no `discount_amount` stored in the database** (orders keep an internal `discount` snapshot to recompute it, but it is not exposed).

---

## 7. What the Frontend Team Should Wire Up

1. **Policy pages** — link Terms, Privacy, Return/Exchange to `GET /api/policies`, render the `_en` or `_ar` variant as HTML (add a language toggle).
2. **FAQ page** — use `GET /api/faqs`; render `question_ar`/`answer_ar` or `_en` depending on the active language.
3. **Settings/FAQs (admin)** — provide English + Arabic editors for each FAQ and each policy; send both `_en` and `_ar` keys on save.
2. **Registration form** — add T&C + Privacy checkboxes (required, must link to policy pages), send `terms_and_conditions_agreed: true` and `privacy_policy_agreed: true`.
3. **Checkout** — add the two agreements, pass `terms_and_condition_agreed`/`privacy_policy_agreed` (must be `true`); show the new `discount_amount`/`discount_percentage` on the order summary.
4. **Product cards / detail** — show `price_after_discount` and an offer badge when `has_offer === true`; use `discount_percentage` for the "10% OFF" label and `discount_amount` for the saved-money value.
5. **Cart page** — use the `summary` block for subtotal/discount_amount/shipping/VAT/total; highlight per-item discounts.
6. **Admin panel** —
   - Product create/edit: percentage + start/end date-time pickers (send local store times).
   - Settings page: editable fields (English + Arabic) for T&C, Privacy, Return/Exchange, delivery fee, VAT.
   - FAQs page: list/create/edit/delete with English + Arabic fields.
   - Order detail: show discount % and amount per item and in summary.

   add
   