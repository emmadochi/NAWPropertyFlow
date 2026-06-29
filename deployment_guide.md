# NAW PropertyFlow CRM - Deployment & Setup Guide

This guide provides step-by-step instructions to deploy and run **NAW PropertyFlow CRM** locally on a XAMPP environment or production server.

## System Prerequisites
* **PHP**: 8.2+ (CLI version 8.2.12 detected)
* **Web Server**: Apache (via XAMPP) or Nginx
* **Database**: MySQL 5.7+ or MariaDB
* **Composer**: PHP package manager

---

## Step 1: Clone or Place the Code base
Place the project directory inside your local server's web root:
* Windows: `C:\xampp\htdocs\NAWPropertyFlowCRM`

---

## Step 2: Configure Environment Variables
1. Copy the `.env.example` file to create a `.env` file (this has already been scaffolded for you):
   ```bash
   cp .env.example .env
   ```
2. Open `.env` and verify database credentials:
   ```env
   APP_NAME="NAW PropertyFlow CRM"
   APP_ENV=local
   APP_KEY=base64:...
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=naw_propertyflow_crm
   DB_USERNAME=root
   DB_PASSWORD=
   ```

---

## Step 3: Run Database Migrations & Seeds
1. Ensure your MySQL server (in XAMPP Control Panel) is active.
2. Run migrations and populate the CRM database with initial Nigerian real estate portfolios, dummy leads, and role-based officer accounts:
   ```bash
   php artisan migrate:fresh --seed
   ```

---

## Step 4: Configure Storage Symbolic Link
Create a symbolic link from `public/storage` to `storage/app/public` to make uploaded property images and KYC files publicly accessible:
```bash
php artisan storage:link
```

---

## Step 5: Start Local Development Server
You can run the built-in Laravel development server:
```bash
php artisan serve
```
The application will be accessible at: [http://127.0.0.1:8000](http://127.0.0.1:8000)

Alternatively, configure XAMPP virtual hosts to serve the app directly from Apache at `http://localhost/NAWPropertyFlowCRM/public`.

---

## Team Credentials for Initial Login
During seeding, five developer/reviewer accounts are created. Use these credentials to test role restrictions:

* **Super Admin**:
  * Email: `superadmin@propertyflow.com`
  * Password: `password`
* **Sales Manager**:
  * Email: `manager@propertyflow.com`
  * Password: `password`
* **Sales Executive (Host 1)**:
  * Email: `se1@propertyflow.com`
  * Password: `password`

---

## Running Verification Tests
Execute the PHPUnit integration test suite to verify route and authentication integrity:
```bash
php artisan test
```
