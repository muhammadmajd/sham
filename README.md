# Sham VPN Backend

Sham VPN is a Laravel-based backend and admin panel for managing a VPN service. It provides API endpoints for mobile or desktop VPN clients, an admin web panel for operations, and integration points for Xray/V2Ray server provisioning, traffic tracking, plans, subscriptions, and payments.

## Features

- User signup, login, JWT authentication, email verification, and password reset.
- Public and authenticated VPN configuration endpoints.
- Admin management for users, VPN servers, subscription plans, devices, applications, subscriptions, home page content, and audit logs.
- Device registration, user-device attachment, access status checks, and usage reporting.
- Xray/V2Ray client configuration generation and traffic synchronization.
- Stripe checkout support for paid plans.
- Download/application catalog management for client app releases.
- Web admin panel under `/admin-panel`.

## Tech Stack

- Laravel 12
- PHP 8.2+
- MySQL or compatible database
- Laravel Vite plugin
- Tailwind CSS
- Alpine.js
- JWT authentication via `tymon/jwt-auth`
- Stripe PHP SDK

## Requirements

- PHP 8.2 or newer
- Composer
- Database server, usually MySQL or MariaDB
- Node.js and npm only if you need to build frontend assets

For production hosting, npm is not required at runtime if the built Vite assets in `public/build` are already uploaded.

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve
```

For active development with Vite:

```bash
npm run dev
```

## Production Notes

Deploy the Laravel app with the usual production settings:

```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan migrate --force
```

Make sure these directories are writable by the web server:

```bash
storage
bootstrap/cache
```

If assets are built locally, upload `public/build` with the project and skip installing Node.js/npm on the hosting server. If assets are built on the server, run:

```bash
npm install
npm run build
```

See [DEPLOYMENT.md](DEPLOYMENT.md) for the full deployment guide.

## Admin Access

The admin panel is available at:

```text
/admin-panel
```

An admin user can be created with the project command:

```bash
php artisan app:create-admin
```

## Scheduled Tasks

The app includes scheduled traffic synchronization through:

```bash
php artisan schedule:run
```

Configure cron, Supervisor, or another process manager in production so scheduled tasks run continuously.

## License

This project is proprietary unless a separate license file says otherwise.
