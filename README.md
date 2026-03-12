# ProductHub

A product management web application built with Laravel 11 and Laravel Breeze.
Regular users manage their own products. Admins manage users and oversee all products.

---

## Test Accounts

| Role          | Email                 | Password  | Notes                        |
|---------------|-----------------------|-----------|------------------------------|
| Super Admin   | admin@example.com     | password  | Full admin access            |
| Regular User  | user@example.com      | password  | Has 3 seeded products        |
| Regular User  | jane@example.com      | password  | Has 3 seeded products        |
| Blocked User  | blocked@example.com   | password  | Login will be rejected       |

---

## Features

**Authentication (Laravel Breeze)**
- Register, login, logout
- Blocked users are rejected at login — they cannot authenticate even with correct credentials

**Regular Users**
- View, create, edit and delete their own products
- Cannot view, edit or delete products belonging to other users (403 if attempted)

**Admin**
- View all users, block and unblock them, create new users
- View and delete any product on the platform
- Cannot access regular user product routes

---

## Running Locally (Without Docker)

**Requirements:** PHP 8.2+, Composer, MySQL, Node.js
```bash
# 1. Clone the repo
git clone <your-repo-url>
cd laravel-assessment

# 2. Install dependencies
composer install

# 3. Set up environment
cp .env.example .env
php artisan key:generate

# 4. Configure your database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_assessment
DB_USERNAME=root
DB_PASSWORD=

# 5. Run migrations and seed
php artisan migrate --seed

# 6. Start the server
php artisan serve
```

Visit http://localhost:8000

---

## Running with Docker

**Requirements:** Docker and Docker Compose installed.
```bash
# 1. Clone the repo
git clone <your-repo-url>
cd laravel-assessment

# 2. Start all containers
docker-compose up -d
```

That's it. The app handles everything else automatically on startup:
- Copies `.env.example` to `.env`
- Generates the app key
- Waits for the database to be ready
- Runs migrations
- Seeds the database

Visit http://localhost:8000

**Useful Docker commands:**
```bash
# View running containers
docker ps

# View app logs
docker logs producthub_app

# Stop all containers
docker-compose down

# Stop and delete the database volume (full reset)
docker-compose down -v

# Re-seed the database
docker exec producthub_app php artisan migrate:fresh --seed

# Run tests inside the container
docker exec producthub_app php artisan test

# Open a shell inside the app container
docker exec -it producthub_app sh
```

---

## Running Tests
```bash
php artisan test
```

Tests use an in-memory SQLite database — no extra configuration needed.

To run a specific test file:
```bash
php artisan test --filter AuthTest
php artisan test --filter ProductTest
php artisan test --filter AdminTest
```

---

## Authorization Summary

| Action                              | Who          |
|-------------------------------------|--------------|
| Register / Login                    | Everyone     |
| View and manage own products        | Users only   |
| Edit or delete another user's product | ❌ 403      |
| View all products                   | Admin only   |
| Delete any product                  | Admin only   |
| Block / Unblock / Create users      | Admin only   |
| Access admin panel                  | Admin only   |

---

## Tech Stack

- **Laravel 11**
- **Laravel Breeze** (Blade stack)
- **Tailwind CSS** (via CDN)
- **MySQL 8.0**
- **PHP 8.3**
- **Nginx**
- **Docker & Docker Compose**