<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SavedLocation;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;

class AuthController extends Controller
{
    public function sendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'role' => 'required|in:rider,driver',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $inputPhone = $request->phone;
        $normalizedPhone = $inputPhone;
        $legacyPhone = $inputPhone;

        if (str_starts_with($inputPhone, '+9630')) {
            $normalizedPhone = '+963' . substr($inputPhone, 5);
            $legacyPhone = $inputPhone;
        } else if (str_starts_with($inputPhone, '+963')) {
            $normalizedPhone = $inputPhone;
            $legacyPhone = '+9630' . substr($inputPhone, 4);
        }

        // Check if phone already exists with a different role (check both formats)
        $existingUser = User::where(function($q) use ($normalizedPhone, $legacyPhone) {
            $q->where('phone', $normalizedPhone)->orWhere('phone', $legacyPhone);
        })->first();

        if ($existingUser && $existingUser->role !== $request->role) {
            $otherRole = ($existingUser->role === 'rider') ? __('messages.rider') : __('messages.driver');
            return response()->json([
                'message' => __('messages.already_registered_as', ['role' => $otherRole])
            ], 403);
        }

        $code = rand(100000, 999999);
        
        // Store code in cache for 10 minutes for verification using normalized phone
        Cache::put('otp_' . $normalizedPhone, $code, now()->addMinutes(10));
        // Also allow the legacy phone in case the frontend logic differs
        if ($normalizedPhone !== $legacyPhone) {
            Cache::put('otp_' . $legacyPhone, $code, now()->addMinutes(10));
        }
        
        // Simulation: We don't send real SMS yet, just log it and return it
        Log::info("Verification code for {$request->phone}: {$code}");


        // Log is already done above


        return response()->json([
            'message' => __('messages.otp_sent'),
            'code' => $code 
        ]);
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'code' => 'required',
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $inputPhone = $request->phone;
        $normalizedPhone = $inputPhone;
        $legacyPhone = $inputPhone;

        if (str_starts_with($inputPhone, '+9630')) {
            $normalizedPhone = '+963' . substr($inputPhone, 5);
            $legacyPhone = $inputPhone;
        } else if (str_starts_with($inputPhone, '+963')) {
            $normalizedPhone = $inputPhone;
            $legacyPhone = '+9630' . substr($inputPhone, 4);
        }

        // Verify code from cache (check both just in case)
        $cachedCode = Cache::get('otp_' . $normalizedPhone) ?? Cache::get('otp_' . $legacyPhone);
        
        // Check for Magic Login Bypass (123456)
        $isMagicBypass = false;
        if ($request->code == '123456') {
            $magicSetting = DB::table('settings')->first();
            if ($magicSetting && isset($magicSetting->magic_login_enabled) && $magicSetting->magic_login_enabled == 1) {
                $isMagicBypass = true;
            }
        }

        if (!$isMagicBypass && (!$cachedCode || $cachedCode != $request->code)) {
            return response()->json([
                'message' => __('messages.otp_invalid')
            ], 422);
        }

        // If verified, remove from cache
        Cache::forget('otp_' . $normalizedPhone);
        Cache::forget('otp_' . $legacyPhone);

        // Lookup user (check both formats for legacy support)
        $user = User::where(function($q) use ($normalizedPhone, $legacyPhone) {
            $q->where('phone', $normalizedPhone)->orWhere('phone', $legacyPhone);
        })->first();
        
        if ($user) {
            // Update FCM Token if provided
            if ($request->has('fcm_token')) {
                $user->fcm_token = $request->fcm_token;
            }

            // Respect the requested role only if the user doesn't have one yet 
            // (Actually for existing users we always trust the DB role)
            // if ($request->has('role') && in_array($request->role, ['rider', 'driver'])) {
            //     $user->role = $request->role;
            // }

            // Update to normalized format if it was found via legacy format
            if ($user->phone !== $normalizedPhone) {
                // Check if the normalized one already exists (to avoid unique constraint violation)
                if (!User::where('phone', $normalizedPhone)->exists()) {
                    $user->phone = $normalizedPhone;
                }
            }
            
            $user->save();
            
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'message' => __('messages.otp_verified'),
                'is_registered' => true,
                'access_token' => $token,
                'user' => $user
            ]);
        }

        return response()->json([
            'message' => __('messages.otp_verified'),
            'is_registered' => false
        ]);
    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users',
            'email' => 'nullable|string|email|max:255|unique:users',
            'role' => 'required|in:rider,driver,admin',
            'fcm_token' => 'nullable|string',
        ]);



        if ($validator->fails()) {
            Log::warning("Registration validation failed", ['errors' => $validator->errors()->all(), 'request' => $request->all()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $phone = $request->phone;
        Log::info("Registering user with phone: " . $phone);
        
        if (str_starts_with($phone, '+9630')) {
            $phone = '+963' . substr($phone, 5);
        } else if (!str_starts_with($phone, '+963')) {
             // Fallback for raw numbers without prefix
             $phone = '+963' . ltrim($phone, '0');
        }

        if (strlen($phone) <= 4) {
             Log::error("Registration failed: Invalid phone number detected.", ['phone' => $phone]);
             return response()->json(['message' => 'Invalid phone number.'], 422);
        }

        $avatarPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = public_path('uploads/avatars');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $file->move($path, $filename);
            $avatarPath = 'uploads/avatars/' . $filename;
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $phone,
            'email' => $request->email,
            'password' => Hash::make('password123'), // Default for SMS flow
            'role' => $request->role,
            'avatar' => $avatarPath ?? $request->avatar,
            'fcm_token' => $request->fcm_token,
            'phone_verified_at' => now(),
        ]);


        $token = $user->createToken('auth_token')->plainTextToken;

        // Broadcast to admin dashboard
        event(new \App\Events\BroadcastAdminStats([
            'new_user' => true,
            'role' => $user->role
        ]));

        return response()->json([
            'message' => __('messages.registered_successfully'),
            'access_token' => $token,
            'user' => $user
        ]);

    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required|in:rider,driver,admin',
            'fcm_token' => 'nullable|string',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => __('messages.invalid_credentials')], 401);
        }

        if ($user->role !== $request->role) {
            return response()->json(['message' => __('messages.unauthorized_role')], 403);
        }

        if ($request->has('fcm_token')) {
            $user->fcm_token = $request->fcm_token;
            $user->save();
        }


        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => __('messages.login_successful'),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $code = rand(1000, 9999);
        
        $user = User::where('email', $request->email)->first();
        if ($user) {
             // In a real app, storing in DB column 'verification_code' is okay for simple MVP.
             // Ideally use PasswordResetTokens table.
             $user->verification_code = $code;
             $user->save();
        }

        try {
            Mail::to($request->email)->send(new VerificationCodeMail($code, 'reset'));
        } catch (\Exception $e) {
            Log::error("Mail send failed: " . $e->getMessage());
            return response()->json(['message' => 'Failed to send email.'], 500);
        }

        Log::info("Reset code for {$request->email}: {$code}");

        return response()->json([
            'message' => 'Reset code sent successfully.',
            'code' => $code // Remove in production
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully.']);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => __('messages.logged_out')]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $data = $request->only(['name', 'phone', 'role']);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            $path = public_path('uploads/avatars');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                @unlink(public_path($user->avatar));
            }
            $file->move($path, $filename);
            $data['avatar'] = 'uploads/avatars/' . $filename;
        } else if ($request->has('avatar')) {
            $data['avatar'] = $request->avatar;
        }
        
        if ($request->has('bio')) {
            $data['bio'] = $request->bio;
        }

        $user->update($data);

        return response()->json([
            'message' => __('messages.profile_updated'),
            'user' => $user->fresh()
        ]);
    }

    public function uploadProfilePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $user = $request->user();
            $file = $request->file('photo');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            
            $path = public_path('uploads/avatars');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            
            // Delete old avatar if exists
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                @unlink(public_path($user->avatar));
            }

            $file->move($path, $filename);
            
            // Store only relative path
            $relativePath = 'uploads/avatars/' . $filename;
            $user->update(['avatar' => $relativePath]);
            
            return response()->json([
                'status' => 'success',
                'message' => __('messages.photo_uploaded'),
                'path' => $relativePath,
                'url' => asset($relativePath)
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'No file uploaded.'], 400);
    }
    public function updateFcmToken(Request $request)
{
    $validator = Validator::make($request->all(), [
        'fcm_token' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user = $request->user(); // المستخدم الحالي عبر sanctum/token
    $user->fcm_token = $request->fcm_token;
    $user->save();

    return response()->json([
        'message' => 'FCM token updated successfully',
        'fcm_token' => $user->fcm_token
    ]);
}

    public function getVets()
    {
        $vets = User::where('role', 'vet')->paginate(10);
        return response()->json($vets);
    }

    public function getSavedLocations(Request $request)
    {
        return response()->json($request->user()->savedLocations);
    }


    public function saveLocation(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $location = SavedLocation::updateOrCreate(
            ['user_id' => $request->user()->id, 'type' => $request->type],
            [
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]
        );

        return response()->json(['message' => 'Location saved', 'location' => $location]);
    }

    public function toggleOnline(Request $request)
    {
        $user = $request->user();
        
        // Use explicitly provided status if available, otherwise toggle
        if ($request->has('is_online')) {
            $user->is_online = $request->boolean('is_online');
        } else {
            $user->is_online = !$user->is_online;
        }
        
        $user->save();

        if ($user->is_online) {
            if ($user->latitude && $user->longitude) {
                \Illuminate\Support\Facades\Redis::geoadd('driver_locations', $user->longitude, $user->latitude, $user->id);
            }
        } else {
            \Illuminate\Support\Facades\Redis::zrem('driver_locations', $user->id);
        }

        // Broadcast to admin
        event(new \App\Events\GlobalDriverLocationUpdated($user));

        return response()->json([
            'status' => 'success',
            'is_online' => (bool)$user->is_online
        ]);
    }

    public function updateLocation(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'driver') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'bearing' => 'nullable|numeric',
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        try {
            // Update Redis Geospatial index
            \Illuminate\Support\Facades\Redis::geoadd('driver_locations', $request->longitude, $request->latitude, $user->id);
        } catch (\Throwable $e) {
            Log::warning("Redis GEOADD failed: " . $e->getMessage());
        }

        try {
            // Keep DB snapshot updated
            $user->update([
                'last_latitude' => $request->latitude,
                'last_longitude' => $request->longitude,
                'last_bearing' => $request->bearing ?? 0,
                'last_location_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Database location update failed: " . $e->getMessage());
        }

        // Broadcast to admin
        event(new \App\Events\GlobalDriverLocationUpdated($user));

        return response()->json(['message' => 'Location updated']);
    }
}
