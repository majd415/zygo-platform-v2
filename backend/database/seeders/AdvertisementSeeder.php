<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Advertisement;

class AdvertisementSeeder extends Seeder
{
    public function run(): void
    {
        Advertisement::create([
            'image_url' => 'images/sliders/elevate_ride_ad_1774870087545.png',
            'title' => 'ELEVATE YOUR RIDE',
            'description' => 'Experience ultimate comfort in journeys.',
            'button_text' => 'Try Premium',
            'is_active' => true,
            'sort_order' => 1,
            'click_action' => 'https://www.mercedes-benz.com/',
        ]);

        Advertisement::create([
            'image_url' => 'images/sliders/safe_reliable_ad_1774870172609.png',
            'title' => 'Safe & Reliable',
            'description' => 'Your safety is our priority always.',
            'button_text' => 'Learn More',
            'is_active' => true,
            'sort_order' => 2,
            'click_action' => 'https://www.google.com/search?q=taxi+safety',
        ]);

        Advertisement::create([
            'image_url' => 'images/sliders/nano_banana_ad_1774870128201.png',
            'title' => 'Nano Banana',
            'description' => 'Fresh and fast delivery for everyone.',
            'button_text' => 'Order Now',
            'is_active' => true,
            'sort_order' => 3,
            'click_action' => 'https://www.google.com/search?q=fresh+bananas',
        ]);

        Advertisement::create([
            'image_url' => 'images/sliders/crate_deals_ad_1774870214974.png',
            'title' => 'Crate Deals',
            'description' => 'Great deals on every crate you order.',
            'button_text' => 'Shop Now',
            'is_active' => true,
            'sort_order' => 4,
            'click_action' => 'https://www.google.com/search?q=grocery+deals',
        ]);
    }
}
