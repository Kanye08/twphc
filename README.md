# ProductHub

A product management web application built with Laravel 11 and Laravel Breeze.
Regular users manage their own products. Admins manage users and oversee all products.

---

## Test Accounts

After setup, these accounts are ready to use:

| Role         | Email               | Password | Notes                       |
|--------------|---------------------|----------|-----------------------------|
| Super Admin  | admin@example.com   | password | Full admin access           |
| Regular User | user@example.com    | password | Has 3 seeded products       |
| Regular User | jane@example.com    | password | Has 3 seeded products       |
| Blocked User | blocked@example.com | password | Login will be rejected      |

---

## Option 1 — Running Locally (Without Docker)

Use this if you already have PHP, Composer and MySQL installed on your machine.

**Requirements**
- PHP 8.2 or higher
- Composer
- MySQL

**Steps**

1. Clone the repository and enter the project folder:
```bash
   git clone <your-repo-url>
   cd laravel-assessment
```

2. Install PHP dependencies:
```bash
   composer install
```

3. Create your environment file and generate the app key:
```bash
   cp .env.example .env
   php artisan key:generate
```

4. Open `.env` and update these lines to match your local MySQL setup:
```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=twpc
   DB_USERNAME=root
   DB_PASSWORD=your_mysql_password
```

5. Run migrations and seed the database:
```bash
   php artisan migrate --seed
```

6. Start the development server:
```bash
   php artisan serve
```

7. Visit http://localhost:8000 in your browser.

---

## Option 2 — Running with Docker (Recommended)

Use this if you do not want to install PHP or MySQL on your machine.
Docker packages everything the app needs and runs it in isolated containers.

### Step 1 — Install Docker

1. Go to https://www.docker.com/products/docker-desktop
2. Download and install Docker Desktop for your OS
3. Open Docker Desktop and wait until you see the green **"Docker is running"** status
4. Confirm it works by running this in your terminal:
```bash
   docker --version
   docker-compose --version
```
   You should see version numbers printed. If you do, Docker is ready.

---

### Step 2 — Configure your environment file

Docker uses `.env.example` as its template. Make sure these values are set
exactly as shown — they must match the database settings in `docker-compose.yml`:
```env
APP_NAME="ProductHub"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=twpc
DB_USERNAME=laravel
DB_PASSWORD=secret

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

> **Important:** `DB_HOST=db` — not `127.0.0.1`. Inside Docker, containers
> talk to each other by service name. The database container is named `db`.

You do not need to touch your local `.env` file. It stays as-is for local development.

---

### Step 3 — Build and start the containers

Open your terminal, navigate to your project folder and run:
```bash
docker-compose up -d --build
```

This single command:
- Downloads PHP, Nginx and MySQL Docker images (first time only, takes 2-5 minutes)
- Builds your Laravel app image
- Starts all three containers (app, webserver, database)
- Runs in the background so your terminal stays free

---

### Step 4 — Watch the startup logs

Run this to follow what's happening inside the app container:
```bash
docker logs producthub_app -f
```

Wait until you see:
```
Database is ready!
Application is ready at http://localhost:8000
```

Then press `Ctrl + C` to stop watching the logs.

> The entrypoint script automatically handles everything on startup:
> creates the `.env`, generates the app key, waits for MySQL,
> runs migrations and seeds the database.

---

### Step 5 — Open the app

Visit http://localhost:8000 in your browser.

### Super Admin Login
```
Email:    admin@example.com
Password: password
```

Log in as admin to access the admin panel where you can manage users
and view all products across the platform.

---

## Useful Docker Commands

**Stop the containers** (your data is preserved):
```bash
docker-compose down
```

**Start the containers again** (no rebuild needed):
```bash
docker-compose up -d
```

**Full reset** — wipes the database and rebuilds everything from scratch:
```bash
docker-compose down -v
docker-compose up -d --build
```

**Run the test suite inside the container:**
```bash
docker exec producthub_app php artisan test
```

**Re-run migrations and seeders manually:**
```bash
docker exec producthub_app php artisan migrate:fresh --seed
```

**Open a shell inside the running app container:**
```bash
docker exec -it producthub_app sh
```

**Check all running containers:**
```bash
docker ps
```

---

## Troubleshooting

**Port 8000 is already in use:**

Change the port mapping in `docker-compose.yml`:
```yaml
ports:
  - "9000:80"
```
Then visit http://localhost:9000 instead.

**"Database not ready yet" keeps repeating in the logs:**

MySQL takes a few seconds to boot. Just wait — it will connect.
If it never connects, check what MySQL is saying:
```bash
docker logs producthub_db
```

**Code changes not reflecting:**

Your project folder is mounted as a volume so changes reflect immediately.
If you changed anything in `config/` or `.env`, clear the cache:
```bash
docker exec producthub_app php artisan config:clear
docker exec producthub_app php artisan cache:clear
```

**Added a new Composer package and need to rebuild:**
```bash
docker-compose down
docker-compose up -d --build
```

---

## Running Tests
```bash
php artisan test
```

Tests use an in-memory SQLite database — no extra configuration needed.

Run a specific test file:
```bash
php artisan test --filter AuthTest
php artisan test --filter ProductTest
php artisan test --filter AdminTest
```

---

## Project Structure
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   ├── AuthenticatedSessionController.php  # Login / logout
│   │   │   └── RegisteredUserController.php        # Registration
│   │   ├── Admin/
│   │   │   ├── UserController.php                  # Block, unblock, create users
│   │   │   └── ProductController.php               # View and delete any product
│   │   ├── DashboardController.php
│   │   ├── ProductController.php                   # User product CRUD
│   │   └── ProfileController.php
│   ├── Middleware/
│   │   ├── IsAdmin.php                             # Restricts routes to admins only
│   │   └── CheckNotBlocked.php                     # Logs out blocked users mid-session
│   └── Requests/Auth/
│       └── LoginRequest.php                        # Checks blocked status before auth
├── Models/
│   ├── User.php
│   └── Product.php
└── Policies/
    └── ProductPolicy.php                           # Ownership checks for products

routes/
├── web.php                                         # All application routes
└── auth.php                                        # Breeze auth routes

resources/views/
├── layouts/
│   ├── app.blade.php                               # Sidebar layout (authenticated)
│   └── guest.blade.php                             # Centered layout (auth pages)
├── auth/                                           # Login and register pages
├── dashboard.blade.php
├── products/                                       # index, create, edit
├── admin/
│   ├── users/                                      # index, create
│   └── products/                                   # index
└── profile/

docker/
├── nginx.conf                                      # Nginx site config
└── entrypoint.sh                                   # Startup script

Dockerfile                                          # PHP 8.3 app image
docker-compose.yml                                  # All three containers
```

---

## Authorization Summary

| Action                                | Who           |
|---------------------------------------|---------------|
| Register / Login                      | Everyone      |
| View and manage own products          | Users only    |
| Edit or delete another user's product | ❌ 403        |
| View all products                     | Admin only    |
| Delete any product                    | Admin only    |
| Block / Unblock / Create users        | Admin only    |
| Access admin panel                    | Admin only    |

---

## Tech Stack

- **Laravel 11**
- **Laravel Breeze** (Blade stack)
- **Tailwind CSS** (via CDN)
- **MySQL 8.0**
- **PHP 8.3**
- **Nginx**
- **Docker & Docker Compose**