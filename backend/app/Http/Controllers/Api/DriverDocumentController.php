<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DriverDocument;

class DriverDocumentController extends Controller
{
    public function getStatus(Request $request)
    {
        return response()->json(['status' => $request->user()->status]);
    }

    public function submitStep(Request $request)
    {
        $user = $request->user();
        $doc = DriverDocument::firstOrCreate(['user_id' => $user->id]);

        // National ID
        if ($request->hasFile('national_id_front')) $doc->national_id_front = $request->file('national_id_front')->store('driver_docs', 'public');
        if ($request->hasFile('national_id_back')) $doc->national_id_back = $request->file('national_id_back')->store('driver_docs', 'public');
        
        // Vehicle Photos (4)
        if ($request->hasFile('vehicle_front')) {
            $path = $request->file('vehicle_front')->store('driver_docs', 'public');
            $doc->car_photo_front = $path;
            $doc->car_photo = $path; // Backward compatibility
        }
        if ($request->hasFile('vehicle_back')) $doc->car_photo_back = $request->file('vehicle_back')->store('driver_docs', 'public');
        if ($request->hasFile('vehicle_left')) $doc->car_photo_left = $request->file('vehicle_left')->store('driver_docs', 'public');
        if ($request->hasFile('vehicle_right')) $doc->car_photo_right = $request->file('vehicle_right')->store('driver_docs', 'public');

        // License
        if ($request->hasFile('license_front')) {
            $path = $request->file('license_front')->store('driver_docs', 'public');
            $doc->driving_license = $path;
        }
        if ($request->hasFile('license_back')) $doc->license_back = $request->file('license_back')->store('driver_docs', 'public');
        
        // Registration
        if ($request->hasFile('registration_front')) $doc->registration_front = $request->file('registration_front')->store('driver_docs', 'public');
        if ($request->hasFile('registration_back')) $doc->registration_back = $request->file('registration_back')->store('driver_docs', 'public');
        
        // Insurance
        if ($request->hasFile('insurance_photo')) $doc->insurance_photo = $request->file('insurance_photo')->store('driver_docs', 'public');

        // Legacy compatibility for older frontend
        if ($request->hasFile('driving_license')) $doc->driving_license = $request->file('driving_license')->store('driver_docs', 'public');
        if ($request->hasFile('car_photo')) $doc->car_photo = $request->file('car_photo')->store('driver_docs', 'public');

        // Text Fields
        $doc->car_type = $request->input('car_type', 'normal'); // Default to normal
        if ($request->filled('car_model')) $doc->car_model = $request->car_model;
        if ($request->filled('car_year')) $doc->car_year = $request->car_year;
        if ($request->filled('car_plate')) $doc->car_plate = $request->car_plate;
        if ($request->filled('car_color')) $doc->car_color = $request->car_color;
        
        $doc->save();

        // Update user status if all basic info is there
        if ($doc->car_model && $doc->car_plate && $doc->car_photo_front) {
            $user->status = 'pending';
            $user->save();
        }

        return response()->json(['message' => 'Step saved successfully', 'document' => $doc]);
    }
}
