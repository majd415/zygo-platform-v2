<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WalletTransaction;

class AdminTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = WalletTransaction::with('user', 'ride')
            ->orderBy('created_at', 'desc');

        // Search by user name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by user_type
        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        // Filter by transaction_type
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Filter by type (credit/debit)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->paginate(25);

        \Log::info("[ZYGO_DASH] Admin transactions page loaded: {$transactions->total()} total records");

        return view('admin.transactions.index', compact('transactions'));
    }
}
