<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    /**
     * A small but realistic dataset. Cities marked with "*" are shown as
     * "popular cities" on the home page.
     */
    private const LOCATIONS = [
        ['India', 'IN', [
            'Maharashtra' => [
                'Mumbai*' => ['Andheri', 'Bandra', 'Borivali', 'Powai', 'Thane West'],
                'Pune*' => ['Kothrud', 'Hinjewadi', 'Viman Nagar', 'Baner', 'Hadapsar'],
                'Nagpur' => ['Dharampeth', 'Sitabuldi', 'Manish Nagar'],
            ],
            'Gujarat' => [
                'Ahmedabad*' => ['Navrangpura', 'Satellite', 'Maninagar', 'Bopal'],
                'Surat' => ['Adajan', 'Vesu', 'Varachha'],
                'Vadodara' => ['Alkapuri', 'Gotri', 'Manjalpur'],
            ],
            'Karnataka' => [
                'Bengaluru*' => ['Koramangala', 'Indiranagar', 'Whitefield', 'HSR Layout', 'Jayanagar'],
                'Mysuru' => ['Vijayanagar', 'Kuvempunagar', 'Gokulam'],
            ],
            'Delhi' => [
                'New Delhi*' => ['Connaught Place', 'Dwarka', 'Rohini', 'Saket', 'Lajpat Nagar'],
            ],
            'Tamil Nadu' => [
                'Chennai*' => ['T. Nagar', 'Adyar', 'Velachery', 'Anna Nagar'],
                'Coimbatore' => ['RS Puram', 'Gandhipuram', 'Peelamedu'],
            ],
            'Telangana' => [
                'Hyderabad*' => ['Gachibowli', 'Banjara Hills', 'Kukatpally', 'Madhapur'],
            ],
        ]],
    ];

    public function run(): void
    {
        foreach (self::LOCATIONS as [$countryName, $iso, $states]) {
            $country = Country::updateOrCreate(
                ['iso_code' => $iso],
                ['name' => $countryName, 'slug' => Str::slug($countryName)],
            );

            foreach ($states as $stateName => $cities) {
                $state = $country->states()->updateOrCreate(
                    ['slug' => Str::slug($stateName)],
                    ['name' => $stateName],
                );

                foreach ($cities as $cityName => $areas) {
                    $popular = str_ends_with($cityName, '*');
                    $cityName = rtrim($cityName, '*');

                    $city = $state->cities()->updateOrCreate(
                        ['slug' => Str::slug($cityName)],
                        ['name' => $cityName, 'is_popular' => $popular],
                    );

                    foreach ($areas as $areaName) {
                        $city->areas()->updateOrCreate(
                            ['slug' => Str::slug($areaName)],
                            ['name' => $areaName],
                        );
                    }
                }
            }
        }
    }
}
