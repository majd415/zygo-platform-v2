@extends('admin.layout')

@section('title', 'Financial Transactions')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">💰 Financial Transactions</h2>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <form method="GET" action="{{ route('admin.transactions.index') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; color: var(--text-muted);">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="User name or email..." 
                    style="width: 100%; padding: 0.5rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-secondary); color: var(--text-primary);">
            </div>
            <div style="min-width: 140px;">
                <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; color: var(--text-muted);">User Type</label>
                <select name="user_type" style="width: 100%; padding: 0.5rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-secondary); color: var(--text-primary);">
                    <option value="">All</option>
                    <option value="driver" {{ request('user_type') == 'driver' ? 'selected' : '' }}>Driver</option>
                    <option value="rider" {{ request('user_type') == 'rider' ? 'selected' : '' }}>Rider</option>
                    <option value="platform" {{ request('user_type') == 'platform' ? 'selected' : '' }}>Platform</option>
                </select>
            </div>
            <div style="min-width: 160px;">
                <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; color: var(--text-muted);">Transaction Type</label>
                <select name="transaction_type" style="width: 100%; padding: 0.5rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-secondary); color: var(--text-primary);">
                    <option value="">All</option>
                    <option value="ride_payment" {{ request('transaction_type') == 'ride_payment' ? 'selected' : '' }}>Ride Payment</option>
                    <option value="commission" {{ request('transaction_type') == 'commission' ? 'selected' : '' }}>Commission</option>
                    <option value="recharge" {{ request('transaction_type') == 'recharge' ? 'selected' : '' }}>Recharge</option>
                    <option value="payout" {{ request('transaction_type') == 'payout' ? 'selected' : '' }}>Payout</option>
                </select>
            </div>
            <div style="min-width: 120px;">
                <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; color: var(--text-muted);">Direction</label>
                <select name="type" style="width: 100%; padding: 0.5rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-secondary); color: var(--text-primary);">
                    <option value="">All</option>
                    <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Credit (+)</option>
                    <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Debit (-)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1.5rem; border-radius: 8px; background: var(--primary); color: white; border: none; cursor: pointer;">
                🔍 Filter
            </button>
            <a href="{{ route('admin.transactions.index') }}" style="padding: 0.5rem 1rem; color: var(--text-muted); text-decoration: none;">
                Clear
            </a>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Type</th>
                    <th>Transaction</th>
                    <th>Amount</th>
                    <th>Before</th>
                    <th>After</th>
                    <th>Ride</th>
                    <th>Description</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                <tr>
                    <td>{{ $tx->id }}</td>
                    <td>
                        <div>{{ $tx->user->name ?? 'N/A' }}</div>
                        <small style="color: var(--text-muted);">{{ ucfirst($tx->user_type ?? $tx->user->role ?? '---') }}</small>
                    </td>
                    <td>
                        <span class="badge {{ $tx->type == 'credit' ? 'badge-success' : 'badge-warning' }}">
                            {{ $tx->type == 'credit' ? '+ Credit' : '- Debit' }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 0.85rem; padding: 2px 8px; border-radius: 4px; background: rgba(139,92,246,0.1); color: #8b5cf6;">
                            {{ ucfirst(str_replace('_', ' ', $tx->transaction_type ?? '---')) }}
                        </span>
                    </td>
                    <td style="font-weight: bold; color: {{ $tx->type == 'credit' ? '#10b981' : '#ef4444' }};">
                        {{ $tx->type == 'credit' ? '+' : '-' }}${{ number_format($tx->amount, 2) }}
                    </td>
                    <td style="color: var(--text-muted);">${{ number_format($tx->balance_before ?? 0, 2) }}</td>
                    <td style="color: var(--text-muted);">${{ number_format($tx->balance_after ?? 0, 2) }}</td>
                    <td>
                        @if($tx->ride_id)
                            <span style="font-size: 0.85rem;">#{{ $tx->ride_id }}</span>
                        @else
                            <span style="color: var(--text-muted);">---</span>
                        @endif
                    </td>
                    <td style="font-size: 0.85rem; max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                        {{ $tx->description ?? '---' }}
                    </td>
                    <td style="font-size: 0.85rem;">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                        No transactions found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div style="display: flex; justify-content: center; padding: 1rem;">
            {{ $transactions->appends(request()->query())->links() }}
        </div>
    </div>
@endsection
