<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DemoListingSeeder extends Seeder
{
    private const IMAGE_DIR = 'listings/demo';

    /**
     * Real photos per subcategory: database/seeders/images/<subcategory-slug>/*.jpg
     * (see CREDITS.md there). Without photos a grey placeholder is generated.
     */
    private const PHOTO_SOURCE = __DIR__.'/images';

    /**
     * Demo sellers. The first one is the account mentioned in the README.
     */
    private const USERS = [
        ['Demo User', 'demo@example.com', '9876543210'],
        ['Rahul Sharma', 'rahul@example.com', '9822012345'],
        ['Priya Patel', 'priya@example.com', '9898076543'],
        ['Arjun Reddy', 'arjun@example.com', '9849011223'],
        ['Sneha Iyer', 'sneha@example.com', '9840055667'],
    ];

    /**
     * Subcategory slug => [[title, min price, max price, description], ...].
     * Each row becomes one listing, so titles stay realistic and unique.
     */
    private const TEMPLATES = [
        'mobile-phones' => [
            ['iPhone 13 128GB Midnight', 32000, 42000, 'Battery health 88%, no scratches, always used with a cover. Box and bill available.'],
            ['Samsung Galaxy S22 Ultra 256GB', 45000, 58000, 'Phantom black, S-Pen included. Minor scuff on the frame, screen is perfect.'],
            ['OnePlus 11R 5G 8/128', 22000, 27000, 'One year old, under warranty till next month. Comes with original charger.'],
            ['Redmi Note 12 Pro', 11000, 14500, 'Used as a secondary phone. Works perfectly, selling because I upgraded.'],
        ],
        'tablets' => [
            ['iPad 9th Gen 64GB WiFi', 18000, 23000, 'Space grey, used mostly for reading. Includes a Smart Folio cover.'],
            ['Samsung Galaxy Tab S6 Lite', 14000, 18000, 'With S-Pen, perfect for notes and online classes.'],
        ],
        'mobile-accessories' => [
            ['Apple AirPods Pro (2nd Gen)', 14000, 17500, 'Sealed pack, received as a gift. Bill available.'],
            ['Anker 20000mAh Power Bank', 1500, 2200, 'Fast charging, used a handful of times on trips.'],
        ],
        'cars' => [
            ['Maruti Swift VXI 2019 Petrol', 520000, 610000, 'Single owner, 42,000 km driven, all services at authorised centre. Insurance valid.'],
            ['Hyundai Creta SX 2021 Diesel', 1350000, 1550000, 'Second owner, 38,000 km, sunroof, new tyres. No accidents.'],
            ['Honda City ZX 2018 CVT', 820000, 940000, 'Automatic, white colour, well maintained. Service records available.'],
            ['Tata Nexon XZ+ 2020', 780000, 880000, 'Petrol, 31,000 km, 5-star safety rating. Ceramic coating done.'],
        ],
        'motorcycles' => [
            ['Royal Enfield Classic 350 2021', 155000, 180000, 'Gunmetal grey, 12,000 km, all papers clear. Crash guard and saddle stays fitted.'],
            ['Bajaj Pulsar NS200 2020', 85000, 102000, 'Single owner, regularly serviced, new chain sprocket kit.'],
            ['KTM Duke 250 2022', 175000, 195000, 'Under warranty, 6,500 km driven. Never raced.'],
        ],
        'scooters' => [
            ['Honda Activa 6G 2022', 62000, 72000, 'Only 7,000 km driven, ladies-used scooter, excellent mileage.'],
            ['Ather 450X Gen 3', 105000, 125000, 'Electric scooter with fast charger. Battery warranty remaining.'],
        ],
        'bicycles' => [
            ['Hero Sprint 26T Gear Cycle', 5500, 8000, '21 gears, disc brakes, used for one season only.'],
            ['Firefox Road Runner Pro', 9000, 13000, 'Hybrid cycle, lightweight frame, new tyres.'],
        ],
        'houses-for-sale' => [
            ['2 BHK Flat, 950 sq.ft, Ready to Move', 6500000, 9500000, '3rd floor in a gated society with lift, gym and covered parking. East facing.'],
            ['3 BHK Apartment with Balcony View', 11000000, 16000000, '1,450 sq.ft carpet, semi-furnished, 2 parking slots, close to metro.'],
        ],
        'houses-for-rent' => [
            ['1 BHK for Rent near Metro Station', 14000, 22000, 'Semi-furnished, 24x7 water, suitable for working professionals or small family.'],
            ['Fully Furnished 2 BHK for Rent', 28000, 42000, 'AC in both rooms, fridge, washing machine, modular kitchen. 2 months deposit.'],
            ['Single Room PG for Boys', 7000, 10000, 'Food included, WiFi, housekeeping. 5 min walk to IT park.'],
        ],
        'shops-offices' => [
            ['Shop for Rent on Main Road, 300 sq.ft', 25000, 45000, 'Ground floor, high footfall area, suitable for retail or clinic.'],
            ['Furnished Office Space, 20 Seats', 60000, 95000, 'Plug-and-play office with conference room, pantry and power backup.'],
        ],
        'land-plots' => [
            ['Residential Plot 1,200 sq.ft, Clear Title', 2500000, 4500000, 'NA plot in an approved layout, road touch, electricity available.'],
        ],
        'tvs-audio' => [
            ['Sony Bravia 55" 4K Smart TV', 38000, 48000, '2 years old, perfect picture, with wall mount and remote.'],
            ['JBL Bar 5.1 Soundbar', 22000, 28000, 'Detachable surround speakers, used gently. Box available.'],
        ],
        'laptops-computers' => [
            ['MacBook Air M1 8GB/256GB', 52000, 62000, 'Battery cycle count 180, no dents. Charger included.'],
            ['Dell Inspiron 15 i5 11th Gen', 32000, 40000, '16GB RAM, 512GB SSD, ideal for students and office work.'],
            ['Gaming PC Ryzen 5 + RTX 3060', 65000, 78000, '16GB RAM, 1TB NVMe, RGB cabinet. Runs every modern game at 1080p high.'],
        ],
        'cameras' => [
            ['Canon EOS 1500D with 18-55mm Lens', 24000, 30000, 'Shutter count around 8,000. Comes with bag and 32GB card.'],
            ['GoPro Hero 10 Black', 22000, 27000, 'With 2 batteries, head mount and waterproof case.'],
        ],
        'kitchen-appliances' => [
            ['LG 260L Double Door Refrigerator', 14000, 19000, 'Frost free, 4 years old, works perfectly. Moving out sale.'],
            ['IFB 25L Convection Microwave', 6500, 9000, 'Used for one year, all functions working.'],
        ],
        'sofa-dining' => [
            ['5 Seater L-Shape Sofa', 14000, 22000, 'Grey fabric, very comfortable, no stains. Pickup only.'],
            ['Sheesham Wood 6 Seater Dining Table', 18000, 28000, 'Solid wood with cushioned chairs. Moving to another city.'],
        ],
        'beds-wardrobes' => [
            ['Queen Size Bed with Storage', 12000, 18000, 'Engineered wood, hydraulic storage. Mattress not included.'],
            ['3 Door Wardrobe with Mirror', 9000, 14000, 'Walnut finish, plenty of space, minor wear on one handle.'],
        ],
        'home-decor' => [
            ['Set of 3 Wall Paintings', 2500, 4500, 'Abstract canvas prints with frames, 24x36 inches.'],
        ],
        'fashion-men' => [
            ['Men\'s Leather Jacket, Size L', 2500, 4500, 'Genuine leather, worn twice. Dark brown.'],
            ['Nike Air Max Sneakers, UK 9', 4000, 6500, 'Original, worn a few times, with box.'],
        ],
        'fashion-women' => [
            ['Designer Lehenga, Worn Once', 6000, 12000, 'Maroon with golden embroidery, size M, dry cleaned.'],
            ['Michael Kors Handbag', 7000, 11000, 'Original with dust bag and tag. Barely used.'],
        ],
        'fashion-kids' => [
            ['Kids Party Wear Set (4-5 yrs)', 800, 1500, 'Sherwani set for boys, worn once at a wedding.'],
        ],
        'it-software-jobs' => [
            ['Laravel Developer (2+ yrs) - Full Time', 40000, 70000, 'Build and maintain Laravel + MySQL web apps. Salary per month, hybrid work.'],
            ['Junior React Developer - Freshers Welcome', 22000, 32000, 'Work on dashboards using React and REST APIs. Salary per month.'],
        ],
        'sales-marketing-jobs' => [
            ['Field Sales Executive', 18000, 26000, 'Two-wheeler required, incentives on top of salary. Salary per month.'],
            ['Digital Marketing Executive', 22000, 35000, 'SEO, social media and Google Ads experience preferred. Salary per month.'],
        ],
        'delivery-driver-jobs' => [
            ['Delivery Partner - Daily Payouts', 18000, 25000, 'Own bike and driving licence needed. Flexible shifts. Earnings per month.'],
            ['Personal Car Driver Required', 16000, 22000, 'Experienced driver for a family, 10-hour shift. Salary per month.'],
        ],
        'home-repair' => [
            ['Electrician & Plumber at Your Doorstep', 300, 500, 'Same-day visits for wiring, fittings, leaks and repairs. Visiting charge shown.'],
            ['AC Service & Gas Refill', 500, 800, 'Split and window AC servicing with 30-day service warranty.'],
        ],
        'tuition-classes' => [
            ['Maths & Science Tuition (Class 8-10)', 2000, 3500, 'Experienced teacher, small batches, home tuition available. Fee per month.'],
            ['Spoken English Classes - Weekend Batch', 2500, 4000, '8-week course for students and professionals. Fee per course.'],
        ],
        'packers-movers' => [
            ['Packers & Movers - Local Shifting', 5000, 9000, 'Packing, loading, transport and unpacking for 1-2 BHK. Starting price.'],
        ],
        'event-services' => [
            ['Wedding & Event Photography', 25000, 45000, 'Candid + traditional photography, edited album included. Per day.'],
            ['Birthday Party Decoration', 3000, 6000, 'Balloon and theme decoration at your venue. Starting price.'],
        ],
        'dogs' => [
            ['Golden Retriever Puppies, KCI Registered', 18000, 28000, '45 days old, vaccinated and dewormed. Parents can be seen.'],
            ['Labrador Puppy, Male', 12000, 18000, 'Healthy and playful, first vaccination done.'],
        ],
        'cats' => [
            ['Persian Kitten, Doll Face', 8000, 14000, 'Litter trained, 2 months old, very friendly.'],
        ],
        'pet-food-accessories' => [
            ['Large Dog Cage with Tray', 3000, 5000, 'Foldable metal cage, used for 6 months.'],
        ],
    ];

    private ?string $font = null;

    public function run(): void
    {
        mt_srand(2026); // same demo data on every fresh seed

        $users = collect(self::USERS)->map(fn ($u) => User::updateOrCreate(
            ['email' => $u[1]],
            ['name' => $u[0], 'phone' => $u[2], 'password' => 'password', 'email_verified_at' => now()],
        ));

        $subcategories = Category::whereNotNull('parent_id')->with('parent')->get()->keyBy('slug');
        $cities = City::with(['state', 'areas'])->get();
        // Popular cities get most of the listings so their pages look busy.
        $weightedCities = $cities->flatMap(fn (City $c) => array_fill(0, $c->is_popular ? 4 : 1, $c))->values();

        Storage::disk('public')->deleteDirectory(self::IMAGE_DIR);
        $this->font = $this->findFont();

        foreach (self::TEMPLATES as $subSlug => $rows) {
            $sub = $subcategories->get($subSlug)
                ?? throw new \RuntimeException("Unknown subcategory [{$subSlug}], run CategorySeeder first.");

            $photos = $this->photosFor($subSlug);

            foreach ($rows as $row => [$title, $min, $max, $description]) {
                $city = $weightedCities[mt_rand(0, $weightedCities->count() - 1)];
                /** @var Area $area */
                $area = $city->areas[mt_rand(0, $city->areas->count() - 1)];
                $createdAt = now()->subMinutes(mt_rand(10, 60 * 24 * 30));

                $listing = Listing::create([
                    'user_id' => $users[mt_rand(0, $users->count() - 1)]->id,
                    'category_id' => $sub->parent_id,
                    'subcategory_id' => $sub->id,
                    'country_id' => $city->state->country_id,
                    'state_id' => $city->state_id,
                    'city_id' => $city->id,
                    'area_id' => $area->id,
                    'type' => in_array($sub->parent->slug, ['services', 'jobs']) ? 'service' : 'product',
                    'title' => $title,
                    'description' => $this->description($description, $area, $city),
                    'price' => $this->price($min, $max),
                    'is_negotiable' => mt_rand(0, 1) === 1,
                    'status' => mt_rand(1, 10) === 1 ? Listing::STATUS_SOLD : Listing::STATUS_ACTIVE,
                ]);

                $listing->forceFill([
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ])->save();

                $imageCount = mt_rand(1, 3);
                if ($photos) {
                    $imageCount = min($imageCount, count($photos));
                }
                for ($i = 0; $i < $imageCount; $i++) {
                    $listing->images()->create([
                        // Each ad of the same subcategory starts at a different photo.
                        'path' => $photos
                            ? $this->copyPhoto($listing, $photos[($row + $i) % count($photos)], $i)
                            : $this->makeImage($listing, $i),
                        'sort_order' => $i,
                        'is_primary' => $i === 0,
                    ]);
                }
            }
        }
    }

    private function description(string $base, Area $area, City $city): string
    {
        $closers = [
            'Serious buyers only, please call or message.',
            'Price is slightly negotiable for quick deal.',
            'Available for viewing on weekends.',
            'Genuine reason for selling. No exchange.',
            'Contact for more photos and details.',
        ];

        return $base."\n\nLocated in {$area->name}, {$city->name}. ".$closers[array_rand($closers)];
    }

    /** Random price in range, rounded the way people actually write prices. */
    private function price(int $min, int $max): int
    {
        $price = mt_rand($min, $max);
        $step = match (true) {
            $price >= 100000 => 5000,
            $price >= 10000 => 500,
            $price >= 1000 => 100,
            default => 50,
        };

        return (int) (round($price / $step) * $step);
    }

    /**
     * @return list<string> photo files for the subcategory, sorted by name
     */
    private function photosFor(string $subSlug): array
    {
        $files = array_filter(
            glob(self::PHOTO_SOURCE."/{$subSlug}/*") ?: [],
            fn ($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true),
        );
        sort($files, SORT_NATURAL);

        return $files;
    }

    private function copyPhoto(Listing $listing, string $file, int $index): string
    {
        $path = self::IMAGE_DIR."/{$listing->id}-{$index}.".strtolower(pathinfo($file, PATHINFO_EXTENSION));
        Storage::disk('public')->put($path, file_get_contents($file));

        return $path;
    }

    /**
     * Draws a plain 800x600 placeholder with the ad title in the middle.
     */
    private function makeImage(Listing $listing, int $index): string
    {
        // Extra photos of the same ad get a slightly darker background.
        $shade = 233 - $index * 12;

        $img = imagecreatetruecolor(800, 600);
        imagefill($img, 0, 0, imagecolorallocate($img, $shade, $shade + 3, $shade + 6));
        $textColor = imagecolorallocate($img, 73, 80, 87);

        $lines = explode("
", wordwrap($listing->title, 26));
        $y = 300 - (count($lines) - 1) * 25;
        foreach ($lines as $line) {
            $this->centeredText($img, $line, 30, $y, $textColor);
            $y += 50;
        }

        $path = self::IMAGE_DIR."/{$listing->id}-{$index}.jpg";
        ob_start();
        imagejpeg($img, null, 82);
        Storage::disk('public')->put($path, ob_get_clean());
        imagedestroy($img);

        return $path;
    }

    private function centeredText(\GdImage $img, string $text, int $size, int $y, int $color): void
    {
        if ($this->font) {
            $box = imagettfbbox($size, 0, $this->font, $text);
            imagettftext($img, $size, 0, (int) ((800 - ($box[2] - $box[0])) / 2), $y, $color, $this->font, $text);

            return;
        }

        // No TTF font on this machine: fall back to GD's built-in bitmap font.
        imagestring($img, 5, (int) ((800 - strlen($text) * imagefontwidth(5)) / 2), $y - 15, $text, $color);
    }

    private function findFont(): ?string
    {
        $candidates = [
            'C:/Windows/Fonts/arialbd.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
        ];

        foreach ($candidates as $font) {
            if (is_file($font) && function_exists('imagettftext')) {
                return $font;
            }
        }

        return null;
    }
}
