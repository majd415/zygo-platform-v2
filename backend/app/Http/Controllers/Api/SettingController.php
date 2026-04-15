<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        $settings = DB::table('settings')->first();
        if ($settings && $settings->logo) {
            $settings->logo_url = asset('storage/' . $settings->logo);
        }
        return response()->json($settings);
    }
}
