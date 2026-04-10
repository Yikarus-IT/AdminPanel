# AdminPanel

AdminPanel is a Laravel + Vue portfolio project that demonstrates a modern, database-backed admin interface. The goal of the project is to show practical full-stack skills with Laravel, Vue, MySQL, migrations, validation, seeded data, and automated tests.

The application currently includes a responsive admin dashboard shell with two functional modules: `Products` and `Users`.

## Project Goals

- Build a portfolio-ready admin panel using modern Laravel and Vue.
- Demonstrate real CRUD workflows backed by a MySQL database.
- Keep the UI clean, responsive, and practical for business-style admin screens.
- Use migrations, seeders, request validation, and tests as part of the normal development workflow.
- Grow the project incrementally into a stronger example for interviews and GitHub portfolio reviews.

## Current Features

### Dashboard

The dashboard is the landing screen for the admin panel. It provides a high-level overview of the project and uses the product data to show simple business metrics.

Current dashboard elements include:

- KPI cards for product count, inventory value, low-stock items, and urgent alerts.
- A top products table.
- A right-side activity panel.
- A responsive three-column admin layout on large screens.

### Products Module

The Products module is a full CRUD section backed by the `products` table.

It currently supports:

- Listing active products.
- Creating new products.
- Editing existing products.
- Archiving products with soft deletes.
- Viewing archived/deleted products through a `View deleted` toggle.
- Server-side validation through Laravel Form Requests.
- MySQL-backed persistence through Laravel Eloquent.
- Decimal product prices with two-decimal display formatting.

Product fields currently include:

- `name`
- `category`
- `sku`
- `price`
- `stock`
- `status`
- `description`
- `deleted_at` for soft deletes

Soft deletes are used for products so archived product IDs can still be referenced later by future statistics, logs, reports, or related records.

### Users Module

The Users module is a second DB-backed CRUD section using Laravel's default `users` table.

It currently supports:

- Listing users.
- Creating users.
- Editing users.
- Deleting users.
- Password hashing through Laravel's model casting.
- Server-side validation through Laravel Form Requests.

User fields currently include:

- `name`
- `email`
- `password`

At the moment, user deletion is a hard delete. A future improvement would be to add soft deletes to users if they begin owning records such as orders, logs, or audit history.

## Tech Stack

### Backend

- PHP 8.3+
- Laravel 13
- Laravel Eloquent ORM
- Laravel migrations and seeders
- Laravel Form Requests for validation
- PHPUnit for backend feature tests
- MySQL 8 for local database persistence

### Frontend

- Vue 3
- Vite
- Tailwind CSS 4
- Axios for HTTP requests
- Vitest for frontend tests
- Vue Testing Library for user-focused component and interaction tests
- jsdom for browser-like test execution

## Testing

The project includes both backend and frontend tests.

Run backend tests:

```bash
php artisan test
```

Run frontend tests:

```bash
npm run test:frontend
```

Run a production frontend build:

```bash
npm run build
```

Current test coverage includes:

- Product listing.
- Product creation.
- Product validation.
- Product soft delete behavior.
- Fetching deleted products.
- User listing.
- User creation.
- User validation.
- Vue sidebar behavior.
- Vue product and user interaction flows.

## Local Setup

Install PHP dependencies:

```bash
composer install
```

Install JavaScript dependencies:

```bash
npm install
```

Create your environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure your `.env` database settings. This project is currently intended to use MySQL locally:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=adminpanel
DB_USERNAME=your_mysql_user
DB_PASSWORD=your_mysql_password
```

Run migrations:

```bash
php artisan migrate
```

Seed demo data only when you intentionally want demo records:

```bash
php artisan db:seed
```

Run the frontend development server:

```bash
npm run dev
```

If you are using Herd, serve the project through your configured local domain, for example:

```text
http://adminpanel.test
```

## Database Safety Note

For normal development and deployment, use:

```bash
php artisan migrate
```

Avoid using this command unless you intentionally want to wipe and rebuild the database:

```bash
php artisan migrate:fresh --seed
```

`migrate:fresh --seed` drops all tables, recreates them, and reloads seeded data. That is useful for resetting a local sandbox, but it will delete manually created records. In a real deployment, the safe migration command is `php artisan migrate --force`.

## Project Status

This is currently a portfolio project, not a finished production SaaS application.

Important future improvements:

- Add authentication and protect all admin routes.
- Add authorization/policies for product and user actions.
- Add pagination, search, and filtering to products and users.
- Split the large Vue `App.vue` file into feature-specific components and composables.
- Add soft deletes to users if users begin owning related records.
- Improve README screenshots and add a short demo GIF.
- Add a GitHub Actions workflow for backend and frontend tests.

## Why This Project Matters

This project is designed to show practical full-stack development skills rather than only static UI work. It demonstrates:

- Laravel backend structure.
- Vue frontend interactivity.
- Real database-backed modules.
- Form validation.
- Soft delete decisions for data preservation.
- Backend and frontend testing.
- A UI direction suitable for business admin tools.

That makes it a useful foundation for a GitHub portfolio and interview discussions around maintainability, scalability, security, and incremental feature development.
