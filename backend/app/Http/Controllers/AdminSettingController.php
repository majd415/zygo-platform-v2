<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Traits\HandlesImageUploads;

class AdminSettingController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        $settings = Setting::all();
        $logo = Setting::where('key', 'logo')->value('value');
        return view('admin.settings.index', compact('settings', 'logo'));
    }

    public function updateLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $currentLogo = Setting::where('key', 'logo')->value('value');
        if ($currentLogo) {
            $this->deleteImage($currentLogo);
        }

        $imagePath = $this->uploadImage($request->file('logo'), 'logo');

        Setting::updateOrCreate(['key' => 'logo'], ['value' => $imagePath]);

        return redirect()->back()->with('success', 'Logo updated successfully');
    }

    public function updateMultipliers(Request $request)
    {
        $request->validate([
            'comfort_multiplier' => 'required|numeric|min:1.00|max:5.00',
            'premium_multiplier' => 'required|numeric|min:1.00|max:5.00',
        ]);

        $settings = Setting::first();
        if (!$settings) {
            return redirect()->back()->with('error', 'Settings not found.');
        }

        $settings->update([
            'comfort_multiplier' => $request->comfort_multiplier,
            'premium_multiplier' => $request->premium_multiplier,
        ]);

        return redirect()->back()->with('success', 'Service multipliers updated successfully! Comfort: ×' . $request->comfort_multiplier . ', Premium: ×' . $request->premium_multiplier);
    }

    public function destroy($id)
    {
        // Simple removal from list if ever needed, but user wanted logo focus
        Setting::destroy($id);
        return redirect()->back()->with('success', 'Setting removed');
    }
}
