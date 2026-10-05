# Bazaar – OLX-style marketplace (Laravel assignment)

Practical assessment for a Part-Time Laravel Developer role at Honeybee Digital. Deadline: 2–3 days.

## Assignment requirements
- User registration/login
- Add product/service: name, details, category, subcategory, country, state, city, area, price
- Show listings on the frontend
- Category-wise, city-wise and city + category-wise listings
- Listing detail page

## Stack
Laravel 12, Breeze (Blade + Tailwind + Alpine.js), MySQL (XAMPP, db `laravel_marketplace`, user root, no password), PHP 8.2.

## Architecture decisions
- `categories` table points to itself: `parent_id` NULL = category, set = subcategory.
- Separate tables for countries → states → cities → areas.
- `listings` stores `category_id`, `subcategory_id` and the full location chain on purpose, so browse queries need no joins.
- Slugs in URLs: `/category/{slug}`, `/city/{slug}`, `/city/{city}/{category}`, `/listing/{slug}`.
- Owner pages under `/dashboard/listings` (resource controller + ListingPolicy).
- AJAX dropdown endpoints under `/ajax/...` (states, cities, areas, subcategories).

## Plan
- Day 1: setup, migrations, models, seeders, auth, then test in the browser.
- Day 2: add/edit listing form (Form Requests that check the category and location hierarchy, image upload), My Listings, browse pages, detail page.
- Day 3: home page polish, feature tests, README with screenshots/ER diagram, small git commits.

## Local environment notes
- XAMPP MySQL listens on port **3307** (set in `.env`); `.env.example` keeps the default 3306.
- Port 8000 is taken by XAMPP Apache, so run `APP_URL=http://127.0.0.1:8001 php artisan serve --port=8001` (the APP_URL override keeps storage image URLs correct).
- Tests run on in-memory SQLite (`phpunit.xml`) so `RefreshDatabase` never wipes the dev DB.
- Demo login: demo@example.com / password (also rahul@, priya@, arjun@, sneha@example.com).

## Progress
Day 1 done:
- Laravel + Breeze, MySQL, `FILESYSTEM_DISK=public`, `storage:link`
- Migrations: locations, categories, listings + listing_images, `phone` on users
- Models: Country, State, City, Area, Category, Listing (scopes: active, inCategory, inCity, search, withCardData), ListingImage, User (phone, listings)
- Seeders: CategorySeeder, LocationSeeder, DemoListingSeeder (5 users, 63 listings, GD-generated placeholder images in `storage/app/public/listings/demo`)
- Home page (`HomeController`, `home.blade.php`, `x-listing-card`): categories, popular cities, 16 latest active listings
- Navigation works for guests (Log in / Register links)
- HomePageTest; register/login checked in the browser

Next (Day 2): the card/category/city links already point to `/listing/{slug}`, `/category/{slug}`, `/city/{slug}` and return 404 until those routes exist.
1. Listing create/edit form (Form Requests that check the category and location hierarchy, image upload) + AJAX dropdown endpoints
2. My Listings (`/dashboard/listings`, ListingPolicy)
3. Browse pages (category, city, city + category) and the listing detail page
