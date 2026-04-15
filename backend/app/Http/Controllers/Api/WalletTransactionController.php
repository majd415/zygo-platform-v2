<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class WalletTransactionController extends Controller
{
    public function getBalance(Request $request)
    {
        return response()->json(['balance' => $request->user()->wallet_balance ?? 0]);
    }

    /**
     * Get the user's transaction history with pagination support.
     */
    public function getTransactions(Request $request)
    {
        $user = $request->user();
        $perPage = (int) $request->query('per_page', 6);
        
        $transactions = WalletTransaction::where('user_id', $user->id)
            ->with('ride:id,pickup_address,dropoff_address,ride_price')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        \Log::info("[ZYGO_PAY] Fetched page {$transactions->currentPage()} ({$transactions->count()} items) for User #{$user->id}");

        return response()->json($transactions);
    }

    public function topup(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);
        
        $user = $request->user();
        $balanceBefore = $user->wallet_balance;
        $user->wallet_balance += $request->amount;
        $user->save();

        WalletTransaction::create([
            'user_id' => $user->id,
            'user_type' => $user->role ?? 'rider',
            'amount' => $request->amount,
            'type' => 'credit',
            'transaction_type' => 'recharge',
            'description' => 'Wallet top-up',
            'balance_before' => $balanceBefore,
            'balance_after' => $user->wallet_balance,
        ]);

        \Log::info("[ZYGO_PAY] Top-up: User #{$user->id} +{$request->amount}. Before: {$balanceBefore}, After: {$user->wallet_balance}");

        return response()->json(['message' => 'Top-up successful', 'balance' => $user->wallet_balance]);
    }

    public function recharge(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        
        $card = \DB::table('recharge_cards')->where('code', $request->code)->where('status', 'active')->first();
        
        if (!$card) {
            return response()->json(['message' => 'Invalid or already used card'], 422);
        }

        if ($card->expiry_date && \Carbon\Carbon::parse($card->expiry_date)->isPast()) {
            return response()->json(['message' => 'Card has expired'], 422);
        }

        $user = $request->user();
        $balanceBefore = $user->wallet_balance;
        $user->wallet_balance += $card->balance;
        $user->save();

        WalletTransaction::create([
            'user_id' => $user->id,
            'user_type' => $user->role ?? 'rider',
            'amount' => $card->balance,
            'type' => 'credit',
            'transaction_type' => 'recharge',
            'description' => 'Card Recharge: ' . $card->code,
            'balance_before' => $balanceBefore,
            'balance_after' => $user->wallet_balance,
        ]);

        // Mark as used
        \DB::table('recharge_cards')->where('id', $card->id)->delete();

        \Log::info("[ZYGO_PAY] Recharge: User #{$user->id} +{$card->balance} (Card: {$card->code}). Before: {$balanceBefore}, After: {$user->wallet_balance}");

        return response()->json([
            'message' => 'Wallet recharged successfully',
            'amount' => $card->balance,
            'balance' => $user->wallet_balance
        ]);
    }

    /**
     * Verify a phone number for gifting balance.
     * Checks: exists, same role, not self.
     */
    public function verifyGiftRecipient(Request $request)
    {
        $request->validate(['phone' => 'required|string']);

        $sender = $request->user();
        $phone = $request->phone;

        // Don't allow sending to self
        if ($sender->phone === $phone) {
            return response()->json(['message' => 'You cannot gift balance to yourself.', 'valid' => false], 422);
        }

        $recipient = User::where('phone', $phone)
            ->where('role', $sender->role)
            ->where('status', 'active')
            ->first();

        if (!$recipient) {
            return response()->json([
                'message' => 'No active user found with this phone number and same role.',
                'valid' => false
            ], 404);
        }

        return response()->json([
            'valid' => true,
            'recipient_name' => $recipient->name,
            'recipient_phone' => $recipient->phone,
        ]);
    }

    /**
     * Send a gift balance to another user of the same role.
     */
    public function sendGift(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        $sender = $request->user();
        $phone = $request->phone;
        $amount = (double) $request->amount;

        // Get minimum gift amount from settings
        $settings = Setting::first();
        $minGift = $settings->min_gift_amount ?? 1000;

        if ($amount < $minGift) {
            return response()->json([
                'message' => "Minimum gift amount is {$minGift}.",
            ], 422);
        }

        // Don't allow sending to self
        if ($sender->phone === $phone) {
            return response()->json(['message' => 'You cannot gift balance to yourself.'], 422);
        }

        // Verify recipient exists with same role
        $recipient = User::where('phone', $phone)
            ->where('role', $sender->role)
            ->where('status', 'active')
            ->first();

        if (!$recipient) {
            return response()->json(['message' => 'Recipient not found or not same role.'], 404);
        }

        // Check sender has enough balance
        if ($sender->wallet_balance < $amount) {
            return response()->json(['message' => 'Insufficient balance.'], 422);
        }

        // Execute transfer in a transaction
        DB::beginTransaction();
        try {
            // Deduct from sender
            $senderBefore = $sender->wallet_balance;
            $sender->wallet_balance -= $amount;
            $sender->save();

            // Add to recipient
            $recipientBefore = $recipient->wallet_balance;
            $recipient->wallet_balance += $amount;
            $recipient->save();

            // Create sender transaction record
            WalletTransaction::create([
                'user_id' => $sender->id,
                'user_type' => $sender->role,
                'amount' => $amount,
                'type' => 'debit',
                'transaction_type' => 'gift_sent',
                'description' => "Gift sent to {$recipient->name} ({$recipient->phone})",
                'balance_before' => $senderBefore,
                'balance_after' => $sender->wallet_balance,
            ]);

            // Create recipient transaction record
            WalletTransaction::create([
                'user_id' => $recipient->id,
                'user_type' => $recipient->role,
                'amount' => $amount,
                'type' => 'credit',
                'transaction_type' => 'gift_received',
                'description' => "Gift received from {$sender->name} ({$sender->phone})",
                'balance_before' => $recipientBefore,
                'balance_after' => $recipient->wallet_balance,
            ]);

            DB::commit();

            \Log::info("[ZYGO_PAY] Gift: {$sender->name} (#{$sender->id}) → {$recipient->name} (#{$recipient->id}), Amount: {$amount}");

            return response()->json([
                'message' => 'Gift sent successfully!',
                'balance' => $sender->wallet_balance,
                'recipient_name' => $recipient->name,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("[ZYGO_PAY] Gift failed: " . $e->getMessage());
            return response()->json(['message' => 'Transfer failed. Please try again.'], 500);
        }
    }
}
