# 🦷 DentalLink Backend

This is the **backend API** for the **DentalLink** application, built with [Laravel](https://laravel.com). It manages all core business logic and database operations for the platform, including user management, product listings, order processing, and more.

---

## 📁 Project Structure

This backend is organized around key modules, each owned by a specific team member.

---

### 👩‍💻 Engy's Responsibilities

| Feature   | Description |
|-----------|-------------|
| **Banner** | API for managing homepage or promotional banners (CRUD). |
| **Order** | Handles order creation, status tracking, and user order history. |
| **Cart** | Manage cart items per user, update quantities, remove/add products. |
| **Checkout** | Process final order submission and payment logic (if applicable). |
| **Wishlist** | Manage user wishlist items — add/remove/view favorites. |

---

### 👩‍💻 Zeinab's Responsibilities

| Feature   | Description |
|-----------|-------------|
| **User** | API for managing user registration, login, and basic info. |
| **Profile** | Allows users to update their personal data, including password changes. |
| **Category** | Handles product categorization — CRUD operations for product types. |
| **Product** | Full CRUD for products, including price, image, description, and category. |
| **Rating** | Users can rate and review products. Includes average rating calculations. |
| **Coupon** | Manage discount coupons — validate, apply, and track usage. |

---

## 🚀 Getting Started

### Requirements

- PHP 8.x
- Composer
- Laravel 10+
- MySQL or other supported DB
- Node.js (optional, if using front-end scaffolding)

### Installation

```bash
git clone https://github.com/ZeinabAbdelghafar/dentallink.git
cd dentallink
composer install
cp .env.example .env
php artisan key:generate
# Set DB credentials in .env
php artisan migrate
php artisan serve
```

## 📬 API Documentation

The API is RESTful and uses standard Laravel routing. You can use tools like **Postman** or **Insomnia** to test the endpoints.

_Optionally, you can document the routes with [Laravel Swagger](https://github.com/DarkaOnLine/L5-Swagger) or [Scribe](https://scribe.knuckles.wtf)._

---

## 🛡️ Security

- Passwords are hashed using Laravel’s built-in `Hash` facade.
- **Sanctum** or **Passport** can be used for API authentication.
- Validation is performed on all input data to ensure consistency and security.

---

## 📌 Contributing

This is a team project. For new features or bug fixes, please work on your own branch and submit a **pull request** for review.

---

## 👥 Authors

- **Engy** – Cart, Orders, Checkout, Wishlist, Banners  
- **Zeinab** – Users, Profiles, Categories, Products, Ratings, Coupons


## API Testing Overview

| Endpoint               | Method | Description                | Auth Required | Sample Request Body                          | Expected Status | Notes                              |
|------------------------|--------|----------------------------|---------------|---------------------------------------------|-----------------|----------------------------------|
| `/auth/signup`         | POST   | User signup                | No            | `{ "email": "...", "username": "...", "password": "...", "gender": "..." }` | 200             | Returns JWT token on success     |
| `/auth/login`          | POST   | User login                 | No            | `{ "email": "...", "password": "..." }`    | 200             | Returns JWT token                 |
| `/auth/verify/{id}/{uuid}` | GET    | Verify email              | No            | N/A                                         | 302 (redirect)  | Use verification link from email |
| `/profile`             | GET    | Get user profile           | Yes           | N/A                                         | 200             | Requires Bearer token            |
| `/profile`             | PUT    | Update user profile        | Yes           | `{ "full_name": "...", "birth_date": "..." }` | 201             | Requires Bearer token            |
| `/user/ResetPassword`  | POST   | Request password reset link| No            | `{ "email": "..." }`                         | 200             | Sends reset link email           |
| `/user/ResetPassword/{id}/` | PUT    | Reset password           | No            | `{ "password": "..." }`                      | 200             | Reset user password              |
| `/user`                | GET    | Get all users (admin)      | Yes           | N/A                                         | 200             | Admin only                      |
| `/user/{id}`           | DELETE | Delete a user (admin)      | Yes           | N/A                                         | 200             | Admin only                      |

