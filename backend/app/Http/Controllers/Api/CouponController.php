<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use Carbon\Carbon;

class CouponController extends Controller
{
    public function validateCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $coupon = Coupon::where('code', $request->code)->where('is_active', true)->first();
        
        if (!$coupon) return response()->json(['error' => 'Invalid coupon'], 404);
        if ($coupon->expiration_date && Carbon::parse($coupon->expiration_date)->isPast()) return response()->json(['error' => 'Coupon expired'], 400);
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) return response()->json(['error' => 'Coupon usage limit reached'], 400);

        return response()->json($coupon);
    }
}
