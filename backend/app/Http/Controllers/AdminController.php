<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function login()
    {
        return view('admin.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('admin.login');
    }

    public function setLanguage($locale)
    {
        session()->put('locale', $locale);
        return redirect()->back();
    }

    public function dashboard()
    {
        $totalUsers = \App\Models\User::count();
        $totalOrders = \App\Models\Ride::count();
        $totalProducts = 0;
        $totalRevenue = \App\Models\Ride::where('status', 'completed')->sum('ride_price');

        // Ride & Financial Stats
        $completedRides = \App\Models\Ride::where('status', 'completed')->count();
        $settings = \App\Models\Setting::first();
        $platformEarnings = $settings->platform_earnings ?? 0;
        $driverEarnings = \App\Models\Ride::where('status', 'completed')->sum('driver_earnings');
        $totalWalletPayments = \App\Models\Ride::where('status', 'completed')
            ->where('payment_method', 'wallet')
            ->sum('ride_price');

        \Log::info("[ZYGO_DASH] Dashboard loaded: Completed={$completedRides}, Platform={$platformEarnings}, DriverEarnings={$driverEarnings}, WalletPayments={$totalWalletPayments}");

        // Revenue Chart (monthly)
        $revenueData = \App\Models\Ride::where('status', 'completed')
            ->selectRaw("DATE_FORMAT(completed_at, '%Y-%m') as month, SUM(ride_price) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        if ($revenueData->isEmpty()) {
            $revenueData = collect([['month' => date('Y-m'), 'total' => 0]]);
        }

        // Ride Status Chart
        $statusData = \App\Models\Ride::selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->get();
        if ($statusData->isEmpty()) {
            $statusData = collect([['status' => 'none', 'count' => 0]]);
        }

        // Category chart (car types)
        $categoryData = \App\Models\Ride::selectRaw("COALESCE(car_type, 'economy') as name, COUNT(*) as products_count")
            ->groupBy('car_type')
            ->get();
        if ($categoryData->isEmpty()) {
            $categoryData = collect([['name' => 'economy', 'products_count' => 0]]);
        }

        // User registration chart (monthly)
        $userData = \App\Models\User::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        if ($userData->isEmpty()) {
            $userData = collect([['month' => date('Y-m'), 'count' => 0]]);
        }

        // Recent rides
        $recentOrders = \App\Models\Ride::with('rider', 'driver')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($ride) {
                return (object) [
                    'id' => $ride->id,
                    'shipping_name' => $ride->rider->name ?? 'Unknown',
                    'product' => (object) ['name' => $ride->pickup_address . ' → ' . $ride->dropoff_address],
                    'total_amount' => $ride->ride_price ?? 0,
                    'status' => $ride->status,
                    'created_at' => $ride->created_at,
                ];
            });

        return view('admin.dashboard', compact(
            'totalUsers', 'totalOrders', 'totalProducts', 'totalRevenue',
            'completedRides', 'platformEarnings', 'driverEarnings', 'totalWalletPayments',
            'revenueData', 'statusData', 'categoryData', 'userData', 'recentOrders'
        ));
    }
}
