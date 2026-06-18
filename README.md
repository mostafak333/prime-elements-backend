# Prime Elements Backend (`clinic_system`)

A scalable, clean-architecture **e-commerce and clinic management platform** built using Laravel 11.
This backend features multi-guard JWT authentication, RBAC authorization, and automated OpenAPI documentation.

---

## 🚀 Tech Stack & Core Architecture

* **Framework:** Laravel 11 (Stateless API Mode)
* **Authentication:** JSON Web Tokens (JWT) via `tymon/jwt-auth`
* **Multi-Guard / Multi-Table Setup:**

  * **Retail Customers / Users**

    * Table: `users`
    * Guard: `api-user`
  * **Administrators**

    * Table: `admins`
    * Guard: `api-admin`
* **Authorization:** RBAC using `spatie/laravel-permission` (guard-isolated)
* **Code Standards:** Modern PHP using Eloquent Attributes
  (`#[Fillable]`, `#[Hidden]`)
* **API Documentation:** `dedoc/scramble` (OpenAPI → APIDog ready)

---

## 🛠️ Getting Started

### 1. Prerequisites

* PHP 8.2+
* Composer
* MySQL / PostgreSQL

---

### 2. Installation

```bash
# Install dependencies
composer install

# Setup environment
cp .env.example .env

# Generate app key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret
```

---

### 3. Database Setup

Update `.env` with your DB credentials, then run:

```bash
php artisan migrate:fresh --seed
```

---

### 4. Clear Cache (Important)

```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

---

### 5. Run Server

```bash
php artisan serve
```

---

## 🗺️ Route Structure & Separation

Routes are **modularized** (NOT using default `api.php`):

### 👥 Customer API — `routes/api_user.php`

Base prefix: `/api`


---

### 🛡️ Admin API — `routes/api_admin.php`

Base prefix: `/api/admin`


---

## 🔒 Security & Exception Handling

### ✅ Global JSON Response

* All API errors return JSON (no redirects)
* Unauthorized access returns:

```json
{
  "message": "Unauthenticated."
}
```

Handled centrally in:

```
bootstrap/app.php
```

---

### 🔑 SuperAdmin Bypass

* Defined in `AppServiceProvider`
* Any user with role **SuperAdmin (api-admin)**:

  * bypasses all permission checks automatically

---

## 📝 API Documentation (Scramble + APIDog)

This project uses **dedoc/scramble** for automatic OpenAPI generation.

---

### 🔐 Global Headers (Auto-injected)

All requests include:

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {{token}}
```

---

### 📄 OpenAPI JSON

Access the generated spec:

```
http://127.0.0.1:8000/docs/api.json
```

---

## 🧠 Architecture Highlights

* Stateless API (no sessions)
* Multi-guard authentication (Admin vs User)
* Clean separation of routes
* DTO + Service Layer ready
* Localization-ready responses
* Fully APIDog compatible

---

## 📌 Notes

* Always use `Authorization: Bearer <token>`
* Ensure correct guard:

  * `api-user` for customers
  * `api-admin` for admins
* All endpoints are JSON-only

---

## 🚀 Future Extensions

* Category & Product modules
* Order management system
* Payment gateway integration
* Multi-language API responses
* Redis caching layer

---
