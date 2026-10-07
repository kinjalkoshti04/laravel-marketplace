# Bazaar – OLX-style Marketplace

A simple buy & sell website built with **Laravel 12**, **MySQL** and **Bootstrap 5**.
Users can register, post ads for products or services, and browse ads by category and city.

![Home page](docs/screenshots/home.png)

## Features

- Register / login (strong password rules)
- Post an ad: title, details, category, subcategory, country, state, city, area, price and up to 5 photos
- Category, city and city + category listing pages, with search, price filter and sorting
- Ad detail page with photo gallery, seller info and similar ads
- My Listings: edit, mark as sold, or delete your ads
- Validation on every form in the browser (jQuery) and on the server (Laravel)

## Pages

| Page | URL |
|---|---|
| All ads | `/listings` |
| Category | `/category/vehicles` |
| City | `/city/mumbai` |
| City + category | `/city/mumbai/vehicles` |
| Ad detail | `/listing/{slug}` |
| Post an ad | `/dashboard/listings/create` |

## Setup

You need PHP 8.2, Composer, Node.js and MySQL (for example XAMPP).

```bash
git clone https://github.com/kinjalkoshti04/laravel-marketplace.git
cd laravel-marketplace

composer install
npm install
npm run build

cp .env.example .env
php artisan key:generate
```

Create a MySQL database named `laravel_marketplace`, check the `DB_` settings in `.env`, then run:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Open http://127.0.0.1:8000

## Demo login

| Email | Password |
|---|---|
| demo@example.com | password |

The seeder adds categories, cities, 5 demo users and 63 demo ads with photos.

## Tests

```bash
php artisan test
```

## Screenshots

| Category page | Ad detail |
|---|---|
| ![Category](docs/screenshots/category.png) | ![Ad detail](docs/screenshots/listing-detail.png) |

| Post an ad | My listings |
|---|---|
| ![Post an ad](docs/screenshots/post-ad-form.png) | ![My listings](docs/screenshots/my-listings.png) |

## Database

- **categories** – one table; `parent_id` empty = category, set = subcategory
- **countries → states → cities → areas** – location tables
- **listings** – the ads, with category and full location saved on each ad for fast browsing
- **listing_images** – ad photos
- **users** – with optional phone number

Demo photos are public domain (see `database/seeders/images/CREDITS.md`).
