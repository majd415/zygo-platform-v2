@extends('admin.layout')

@section('title', 'System Settings')

@section('content')
    <div class="grid-4">
        <!-- Logo Update -->
        <div class="card">
            <h3>App Logo</h3>
            <div style="text-align: center; padding: 2rem; border: 2px dashed var(--border); border-radius: 1rem; margin-bottom: 1.5rem;">
                @if($logo)
                    <img src="{{ asset($logo) }}" style="max-width: 100%; height: 100px; object-fit: contain;">
                @else
                    <div style="font-size: 3rem;">🐕</div>
                    <div style="color: var(--text-muted);">No Logo Uploaded</div>
                @endif
            </div>
            <form action="{{ route('admin.settings.logo.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="logo" id="logoInput" style="display: none;" onchange="this.form.submit()">
                <button type="button" onclick="document.getElementById('logoInput').click()" class="btn btn-primary" style="width: 100%;">
                    Change Logo
                </button>
            </form>
        </div>

        <!-- Other Info -->
        <div class="card" style="grid-column: span 2;">
            <h3>App Information</h3>
            <table>
                <thead>
                    <tr>
                        <th>Key</th>
                        <th>Value</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($settings as $s)
                    @if($s->key != 'logo')
                    <tr>
                        <td style="font-weight: bold;">{{ strtoupper($s->key) }}</td>
                        <td>{{ $s->value }}</td>
                        <td>
                             <form action="{{ route('admin.settings.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Remove?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-icon">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Service Category Multipliers -->
        @php
            $settingsRow = \App\Models\Setting::first();
            $comfortMult = $settingsRow->comfort_multiplier ?? 1.10;
            $premiumMult = $settingsRow->premium_multiplier ?? 1.25;
        @endphp
        <div class="card">
            <h3>🚗 Service Category Multipliers</h3>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.85rem;">
                Economy is the base price (×1.0). Comfort and Premium are multiplied by these values.
            </p>

            @if(session('success'))
                <div style="margin-bottom: 1rem; padding: 0.75rem; border-radius: 0.5rem; background: rgba(34, 197, 94, 0.1); color: #4ade80; font-size: 0.85rem;">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.settings.multipliers.update') }}" method="POST">
                @csrf
                <div style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">🟢 Economy</label>
                        <input type="text" value="×1.00 (Base Price)" readonly style="opacity: 0.5;">
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">🔵 Comfort Multiplier</label>
                        <input type="number" name="comfort_multiplier" value="{{ $comfortMult }}" step="0.01" min="1.00" max="5.00" required>
                        <small style="color: var(--text-muted);">e.g. 1.10 = +10%</small>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">🟡 Premium Multiplier</label>
                        <input type="number" name="premium_multiplier" value="{{ $premiumMult }}" step="0.01" min="1.00" max="5.00" required>
                        <small style="color: var(--text-muted);">e.g. 1.25 = +25%</small>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">💾 Save Multipliers</button>
            </form>
        </div>
    </div>
@endsection
