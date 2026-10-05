<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Category name => [icon, [subcategory name => slug|null]].
     * An explicit slug is given where the plain name would not be unique.
     */
    public const CATEGORIES = [
        'Mobiles' => ['📱', [
            'Mobile Phones' => null,
            'Tablets' => null,
            'Mobile Accessories' => null,
        ]],
        'Vehicles' => ['🚗', [
            'Cars' => null,
            'Motorcycles' => null,
            'Scooters' => null,
            'Bicycles' => null,
        ]],
        'Property' => ['🏠', [
            'Houses & Apartments for Sale' => 'houses-for-sale',
            'Houses & Apartments for Rent' => 'houses-for-rent',
            'Shops & Offices' => null,
            'Land & Plots' => null,
        ]],
        'Electronics & Appliances' => ['📺', [
            'TVs & Audio' => null,
            'Laptops & Computers' => null,
            'Cameras' => null,
            'Kitchen Appliances' => null,
        ]],
        'Furniture' => ['🛋️', [
            'Sofa & Dining' => null,
            'Beds & Wardrobes' => null,
            'Home Decor' => null,
        ]],
        'Fashion' => ['👕', [
            'Men' => 'fashion-men',
            'Women' => 'fashion-women',
            'Kids' => 'fashion-kids',
        ]],
        'Jobs' => ['💼', [
            'IT & Software Jobs' => 'it-software-jobs',
            'Sales & Marketing Jobs' => 'sales-marketing-jobs',
            'Delivery & Driver Jobs' => 'delivery-driver-jobs',
        ]],
        'Services' => ['🛠️', [
            'Home Repair' => null,
            'Tuition & Classes' => null,
            'Packers & Movers' => null,
            'Event Services' => null,
        ]],
        'Pets' => ['🐶', [
            'Dogs' => null,
            'Cats' => null,
            'Pet Food & Accessories' => null,
        ]],
    ];

    public function run(): void
    {
        $order = 0;

        foreach (self::CATEGORIES as $name => [$icon, $children]) {
            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'icon' => $icon, 'sort_order' => ++$order, 'parent_id' => null],
            );

            $childOrder = 0;
            foreach ($children as $childName => $slug) {
                Category::updateOrCreate(
                    ['slug' => $slug ?? Str::slug($childName)],
                    ['name' => $childName, 'parent_id' => $parent->id, 'sort_order' => ++$childOrder],
                );
            }
        }
    }
}
