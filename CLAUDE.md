# Bazaar – OLX-style marketplace (Laravel assignment)

Practical assessment for a Part-Time Laravel Developer role at Honeybee Digital. Deadline: 2–3 days.

## Assignment requirements
- User registration/login
- Add product/service: name, details, category, subcategory, country, state, city, area, price
- Show listings on the frontend
- Category-wise, city-wise and city + category-wise listings
- Listing detail page

## Stack
Laravel 12, Breeze auth with Blade views styled in Bootstrap 5 (Tailwind removed), Alpine.js for the small dynamic parts, jQuery Validation on login/register, MySQL (XAMPP, db `laravel_marketplace`, user root, no password), PHP 8.2.

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

Day 2 done:
- Listing form (`my-listings/_form.blade.php` + Alpine `resources/js/listing-form.js`): dependent dropdowns via `/ajax/*`, restores old input/edit values, photo previews
- `StoreListingRequest` / `UpdateListingRequest`: each id must belong to its parent (subcategory→category, state→country, city→state, area→city); max 5 photos counting existing ones
- My Listings (`/dashboard/listings`, `MyListingController`, `ListingPolicy`): status tabs, edit (status, remove photos), soft delete
- Browse (`BrowseController`, one view for /listings, /category, /city, /city/{city}/{category}): search, price filter, sort, category/city facets with counts
- Detail page (`ListingController@show`): gallery, seller, phone for logged-in users, similar ads, view count once per session; inactive ads 404 for non-owners
- Dashboard with stats; prices use Indian digit grouping (`Listing::indianNumber`, no intl extension here)
- `ListingFactory` (needs Category + Location seeders); 46 tests passing; checked in a real browser with puppeteer

Day 3 done:
- Per-page `<title>` via `<x-slot:title>`, indigo primary button, removed unused welcome view
- README: requirement → URL table, setup, demo accounts, screenshots (`docs/screenshots/`), Mermaid ER diagram, design decisions

Final pass done:
- Phone number field on registration and profile (`PhoneNumberTest`)
- Full browser test (puppeteer + Edge, 94 checks, guest + new user flows, verified against MySQL): all pass, no JS errors or 500s
- Bugs found and fixed: removing the cover photo while uploading a new one made the new upload the cover; "Log in to see phone number" now returns to the ad (`/listing/{slug}/contact`, auth route) with the phone shown
- Deleting an account also deletes the user's photo files and upload folders (User model `deleting`/`deleted` events), soft-deleted ads included
- Login/register: browser validation off (`novalidate`), jQuery Validation instead (`resources/js/auth-validation.js`, separate Vite entry); strong passwords via `Password::defaults()` in AppServiceProvider; live checklist, strength meter, show/hide, suggest password; `RegistrationValidationTest`
- 65 PHPUnit tests passing (demo users keep `password`: login does not check strength)

Design switched to plain Bootstrap 5 (user asked for a simple, normal Bootstrap look):
- Tailwind removed; `resources/css/app.css` imports Bootstrap; `Paginator::useBootstrapFive()`
- Breeze components rewritten for Bootstrap (dropdown/modal/nav-link components deleted; navbar collapse + dropdown + delete-account modal use Bootstrap JS)
- Demo images are plain grey placeholders with the title
- jQuery Validation uses `is-invalid` / `invalid-feedback`; re-validates flagged fields on `input` (paste/autofill)

Validation on every form (jQuery + Laravel):
- `resources/js/form-validation.js` (imported in app.js) validates every `form[data-validate]`; rules from HTML attributes + `data-rule-*` (strongpassword, phone, equalto, filetypes, maxfilesize, photolimit, notlessthan), messages via `data-msg-*`
- Forms: search, filters, login, register, forgot/reset/confirm password, profile, update password, delete account, listing create/edit
- `x-password-help` component (checklist, strength, suggest) on register, reset password, update password
- Server: `BrowseFilterRequest` (q, prices, max >= min, sort) redirects to the same page with errors; profile rules match register (min name, lowercase email)
- `FormValidationTest`; 72 PHPUnit tests; browser test of all forms 41/41

Remaining (optional): push to GitHub when the user asks (no remote yet).
