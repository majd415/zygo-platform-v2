@extends('admin.layout')

@section('title', '🚕 Ride Management')

@section('content')

    <!-- Status Filter -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            @php $currentStatus = request('status', 'all'); @endphp
            @foreach(['all' => '📋 All', 'searching' => '🔍 Searching', 'accepted' => '🤝 Accepted', 'arrived' => '📍 Arrived', 'started' => '🚗 Started', 'completed' => '✅ Completed', 'cancelled' => '❌ Cancelled'] as $key => $label)
                <a href="{{ route('admin.rides.index', ['status' => $key]) }}"
                   class="btn" style="font-size: 0.8rem; padding: 0.5rem 1rem;
                   {{ $currentStatus === $key ? 'background: linear-gradient(135deg, var(--primary), #7c3aed); color: white; box-shadow: 0 4px 15px var(--primary-glow);' : 'background: rgba(255,255,255,0.05); color: var(--text-muted);' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if(session('success'))
        <div class="card" style="margin-bottom: 1rem; border-color: rgba(34, 197, 94, 0.3); color: #4ade80;">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="card" style="margin-bottom: 1rem; border-color: rgba(239, 68, 68, 0.3); color: #f87171;">
            ❌ {{ session('error') }}
        </div>
    @endif

    <!-- Rides Table -->
    <div class="card" style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Rider</th>
                    <th>Driver</th>
                    <th>Status</th>
                    <th>Price</th>
                    <th>Distance</th>
                    <th>Duration</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rides as $ride)
                <tr>
                    <td style="font-weight: 600; color: var(--primary);">#{{ $ride->id }}</td>
                    <td>
                        <div style="font-weight: 500;">{{ $ride->rider->name ?? '—' }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $ride->pickup_address ?? '' }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 500;">{{ $ride->driver->name ?? '—' }}</div>
                    </td>
                    <td>
                        @php
                            $statusColors = [
                                'searching' => 'rgba(234, 179, 8, 0.2); color: #facc15',
                                'accepted' => 'rgba(59, 130, 246, 0.2); color: #60a5fa',
                                'arrived' => 'rgba(6, 182, 212, 0.2); color: #22d3ee',
                                'started' => 'rgba(34, 197, 94, 0.2); color: #4ade80',
                                'completed' => 'rgba(139, 92, 246, 0.2); color: #a78bfa',
                                'cancelled' => 'rgba(239, 68, 68, 0.2); color: #f87171',
                                'no_driver_found' => 'rgba(107, 114, 128, 0.2); color: #9ca3af',
                            ];
                            $style = $statusColors[$ride->status] ?? 'rgba(107, 114, 128, 0.2); color: #9ca3af';
                        @endphp
                        <span style="background: {{ $style }}; padding: 0.3rem 0.75rem; border-radius: 99px; font-size: 0.75rem; font-weight: 600;">
                            {{ ucfirst(str_replace('_', ' ', $ride->status)) }}
                        </span>
                    </td>
                    <td style="font-weight: 700; color: #4ade80;">{{ number_format($ride->ride_price) }} SYP</td>
                    <td>{{ $ride->distance_text ?? '—' }}</td>
                    <td>{{ $ride->duration_text ?? '—' }}</td>
                    <td>
                        <span style="font-size: 0.8rem;">{{ $ride->payment_method === 'wallet' ? '💳 Wallet' : '💵 Cash' }}</span>
                    </td>
                    <td style="font-size: 0.8rem; color: var(--text-muted);">
                        {{ $ride->created_at ? $ride->created_at->format('M d, H:i') : '—' }}
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                            <!-- Edit Price -->
                            <button onclick="openPriceModal({{ $ride->id }}, {{ $ride->ride_price }})"
                                    class="btn" style="background: rgba(234, 179, 8, 0.15); color: #facc15; font-size: 0.75rem; padding: 0.4rem 0.7rem;"
                                    title="Edit Price">💰</button>

                            @if($ride->status !== 'completed' && $ride->status !== 'cancelled')
                                <!-- Complete (Simple) -->
                                <form action="{{ route('admin.rides.complete_simple', $ride->id) }}" method="POST"
                                      onsubmit="return confirm('Complete ride #{{ $ride->id }} WITHOUT financial processing?');" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn" style="background: rgba(34, 197, 94, 0.15); color: #4ade80; font-size: 0.75rem; padding: 0.4rem 0.7rem;"
                                            title="Complete (No Financials)">✅</button>
                                </form>

                                <!-- Complete (With Financials) -->
                                @if($ride->driver_id)
                                <form action="{{ route('admin.rides.complete_financials', $ride->id) }}" method="POST"
                                      onsubmit="return confirm('Complete ride #{{ $ride->id }} WITH commission deduction from driver?');" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn" style="background: rgba(139, 92, 246, 0.15); color: #a78bfa; font-size: 0.75rem; padding: 0.4rem 0.7rem;"
                                            title="Complete + Deduct Commission">💸</button>
                                </form>
                                @endif
                            @endif

                            <!-- Delete -->
                            <form action="{{ route('admin.rides.destroy', $ride->id) }}" method="POST"
                                  onsubmit="return confirm('DELETE ride #{{ $ride->id }}? This cannot be undone!');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn" style="background: rgba(239, 68, 68, 0.15); color: #f87171; font-size: 0.75rem; padding: 0.4rem 0.7rem;"
                                        title="Delete Ride">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                        No rides found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div style="margin-top: 2rem;">
            {{ $rides->links() }}
        </div>
    </div>

    <!-- Edit Price Modal -->
    <div id="priceModal" class="overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h2 class="modal-title">💰 Edit Ride Price</h2>
                <button class="close-modal" onclick="closePriceModal()">✕</button>
            </div>
            <form id="priceForm" method="POST">
                @csrf
                <div style="margin-bottom: 1.5rem;">
                    <label>Ride ID</label>
                    <input type="text" id="priceRideId" readonly style="opacity: 0.6;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label>New Price (SYP)</label>
                    <input type="number" name="ride_price" id="priceInput" required min="0" step="1" placeholder="Enter new price">
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end; border-top: 1px solid var(--border); padding-top: 1.25rem;">
                    <button type="button" onclick="closePriceModal()" class="btn" style="background: rgba(255,255,255,0.05); color: var(--text-muted);">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="padding-left: 2rem; padding-right: 2rem;">💾 Save Price</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Legend -->
    <div class="card" style="margin-top: 2rem; padding: 1rem 1.5rem;">
        <div style="display: flex; gap: 2rem; flex-wrap: wrap; font-size: 0.8rem; color: var(--text-muted);">
            <span>💰 = Edit Price</span>
            <span style="color: #4ade80;">✅ = Complete (No Financials)</span>
            <span style="color: #a78bfa;">💸 = Complete + Deduct Commission</span>
            <span style="color: #f87171;">🗑️ = Delete</span>
        </div>
    </div>

    <script>
        function openPriceModal(rideId, currentPrice) {
            document.getElementById('priceModal').classList.add('show');
            document.getElementById('priceRideId').value = '#' + rideId;
            document.getElementById('priceInput').value = Math.round(currentPrice);
            document.getElementById('priceForm').action = "{{ url('admin/rides') }}/" + rideId + "/price";
        }
        function closePriceModal() {
            document.getElementById('priceModal').classList.remove('show');
        }
    </script>

@endsection
