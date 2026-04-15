<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:user,driver,rider',
            'service_category' => 'nullable|in:economy,comfort,premium',
            'is_admin' => 'nullable|boolean',
            'admin_role' => 'nullable|in:super_admin,accountant,data_entry',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'service_category' => $request->service_category ?? 'economy',
            'is_admin' => $request->is_admin ?? false,
            'admin_role' => $request->admin_role,
            'email_verified_at' => now(),
        ]);

        return redirect()->back()->with('success', 'User created successfully');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:user,driver,rider',
            'service_category' => 'nullable|in:economy,comfort,premium',
            'is_admin' => 'nullable|boolean',
            'admin_role' => 'nullable|in:super_admin,accountant,data_entry',
        ]);

        $data = $request->only(['name', 'email', 'role', 'service_category', 'is_admin', 'admin_role']);
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        User::destroy($id);
        return redirect()->back()->with('success', 'User deleted successfully');
    }

    /**
     * Update a driver's wallet balance from the admin dashboard.
     */
    public function updateWallet(Request $request, $id)
    {
        $request->validate([
            'wallet_balance' => 'required|numeric|min:0',
        ]);

        $user = User::findOrFail($id);
        $oldBalance = $user->wallet_balance;
        $newBalance = round($request->wallet_balance);
        $amount = $newBalance - $oldBalance;
        
        $user->update(['wallet_balance' => $newBalance]);

        // Log the transaction
        \Illuminate\Support\Facades\DB::table('wallet_transactions')->insert([
            'user_id' => $user->id,
            'transaction_type' => $amount >= 0 ? 'credit' : 'debit',
            'amount' => $amount,
            'balance_before' => $oldBalance,
            'balance_after' => $newBalance,
            'description' => "Admin wallet adjustment by Admin #" . auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Illuminate\Support\Facades\Log::info("🔧 [ADMIN] Wallet for User #{$id} changed: {$oldBalance} → {$newBalance} by Admin #" . auth()->id());

        return redirect()->back()->with('success', "{$user->name}'s wallet updated: {$oldBalance} → {$newBalance} SYP");
    }
}
