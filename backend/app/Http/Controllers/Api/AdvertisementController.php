<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\Request;

class AdvertisementController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->header('X-Language', 'en');
        $ads = Advertisement::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function($ad) use ($lang) {
                return [
                    'id' => $ad->id,
                    'image_url' => $ad->image_url,
                    'title' => ($lang == 'ar' && !empty($ad->title_ar)) ? $ad->title_ar : ($ad->title_en ?? $ad->title_ar),
                    'description' => ($lang == 'ar' && !empty($ad->description_ar)) ? $ad->description_ar : ($ad->description_en ?? $ad->description_ar),
                    'button_text' => ($lang == 'ar' && !empty($ad->button_text_ar)) ? $ad->button_text_ar : ($ad->button_text_en ?? $ad->button_text_ar),
                    'click_action' => $ad->click_action
                ];
            });

        return response()->json($ads);
    }
}
