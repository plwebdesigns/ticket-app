# Ticket App

A Laravel application for managing tickets (issues), comments, and attachments. The UI is built with [Inertia.js](https://inertiajs.com/) and Vue 3, with authentication handled by [Laravel Fortify](https://laravel.com/docs/fortify).

## Requirements

- **PHP** 8.3 or higher (with common extensions: `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath` or equivalent)
- **Composer** 2.x
- **Node.js** 20.x or 22.x (LTS recommended) and **npm**

Optional but useful on macOS:

- **[Laravel Herd](https://herd.laravel.com/)** — serves the app at a `.test` domain without `php artisan serve`

## Download

Clone the repository (or download and extract the archive), then enter the project directory:

```bash
git clone https://github.com/plwebdesigns/ticket-app.git
cd ticket-app
```


## Install and configure

### Automated setup

From the project root, run:

```bash
composer setup
```

This will:

1. Install PHP dependencies (`composer install`)
2. Create `.env` from `.env.example` if it does not exist
3. Generate `APP_KEY`
4. Run database migrations (`php artisan migrate --force`)
5. Install JavaScript dependencies (`npm install`)
6. Build frontend assets (`npm run build`)


### Manual setup

If you prefer step-by-step control:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build                    # or `npm run dev` while developing (see below)
```

Edit `.env` to change `APP_NAME`, `APP_URL`, or switch `DB_CONNECTION` to `mysql` / `pgsql` and set the matching credentials. After changing database settings, run `php artisan migrate` again.

## Run locally

### Option A: All-in-one dev environment (recommended)

Starts the PHP server, queue worker, log viewer ([Pail](https://laravel.com/docs/pail)), and the Vite dev server together:

```bash
composer run dev
```

Then open the URL shown in the terminal (typically `http://127.0.0.1:8000` when using `php artisan serve`).

### Option B: Laravel Herd + Vite

If the site is linked in [Herd](https://herd.laravel.com/docs), open the app at your Herd URL (for a folder named `ticket-app`, often `http://ticket-app.test`). Set `APP_URL` in `.env` to match (including `https` if Herd uses TLS).

In a separate terminal, run the frontend dev server so Inertia/Vite hot-reloads:

```bash
npm run dev
```

Use `npm run build` when you want a production-style asset build without the dev server.

### Option C: `php artisan serve` + Vite

```bash
php artisan serve
```

In another terminal:

```bash
npm run dev
```

## Tests

```bash
php artisan test --compact
```

## Troubleshooting

- **“Unable to locate file in Vite manifest”** — Run `npm run dev` while developing, or `npm run build` for a static build.
- **Database errors** — Confirm `database/database.sqlite` exists for SQLite, or that your MySQL/PostgreSQL server is running and `.env` matches your database.
