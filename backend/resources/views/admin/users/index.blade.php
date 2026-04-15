@extends('admin.layout')

@section('title', __('admin.users'))

@section('content')
    <!-- Action Bar -->
    <div style="display: flex; justify-content: flex-end; margin-bottom: 2rem;">
        <button onclick="openModal('add')" class="btn btn-primary">
            + {{ __('admin.add_new') }}
        </button>
    </div>

    @if(session('success'))
        <div class="card" style="margin-bottom: 1rem; border-color: rgba(34, 197, 94, 0.3); color: #4ade80;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Users Table -->
    <div class="card" style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('admin.status') }}</th>
                    <th>{{ __('admin.name') }}</th>
                    <th>{{ __('admin.role') }}</th>
                    <th>Category</th>
                    <th>Wallet</th>
                    <th>Online</th>
                    <th>{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>#{{ $user->id }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: #334be9; display: flex; align-items: center; justify-content: center; font-weight: bold; overflow: hidden;">
                                @if($user->avatar)
                                    <img src="{{ $user->avatar }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                @endif
                            </div>
                            <div>
                                <div style="font-weight: 500;">{{ $user->name }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $user->phone ?? $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge {{ $user->role == 'driver' ? 'badge-warning' : 'badge-success' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                        @if($user->is_admin)
                            <span class="badge" style="background: rgba(99, 102, 241, 0.2); color: #818cf8;">
                                {{ $user->admin_role ? ucfirst(str_replace('_', ' ', $user->admin_role)) : 'Admin' }}
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($user->role === 'driver')
                            @php
                                $catColors = ['economy' => '#4ade80', 'comfort' => '#60a5fa', 'premium' => '#f59e0b'];
                                $catColor = $catColors[$user->service_category ?? 'economy'] ?? '#4ade80';
                            @endphp
                            <span class="badge" style="background: {{ $catColor }}22; color: {{ $catColor }};">
                                {{ ucfirst($user->service_category ?? 'economy') }}
                            </span>
                        @else
                            <span style="color: var(--text-muted);">—</span>
                        @endif
                    </td>
                        @if($user->role === 'driver')
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="font-weight: 700; color: #4ade80;">{{ number_format($user->wallet_balance ?? 0) }} SYP</span>
                                <button onclick="openWalletModal({{ $user->id }}, '{{ addslashes($user->name) }}', {{ $user->wallet_balance ?? 0 }})"
                                        class="btn" style="background: rgba(234, 179, 8, 0.15); color: #facc15; font-size: 0.7rem; padding: 0.25rem 0.5rem;"
                                        title="Edit Wallet">💰</button>
                            </div>
                        @else
                            <span style="color: var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td>
                        @if($user->role === 'driver')
                            <span style="color: {{ $user->is_online ? '#4ade80' : '#f87171' }}; font-weight: 600;">
                                {{ $user->is_online ? '🟢 Online' : '🔴 Offline' }}
                            </span>
                        @else
                            <span style="color: var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.5rem;">
                            <button onclick="editUser({{ json_encode($user) }})" class="btn" style="background: rgba(255, 255, 255, 0.1); color: var(--text);">✏️</button>
                            
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Delete this user?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-icon">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div style="margin-top: 2rem;">
            {{ $users->links() }} 
        </div>
    </div>

    <!-- Wallet Edit Modal -->
    <div id="walletModal" class="overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h2 class="modal-title">💰 Edit Driver Wallet</h2>
                <button class="close-modal" onclick="closeWalletModal()">✕</button>
            </div>
            <form id="walletForm" method="POST">
                @csrf
                <div style="margin-bottom: 1.25rem;">
                    <label>Driver</label>
                    <input type="text" id="walletDriverName" readonly style="opacity: 0.6;">
                </div>
                <div style="margin-bottom: 1.25rem;">
                    <label>Current Balance</label>
                    <input type="text" id="walletCurrentBalance" readonly style="opacity: 0.6; color: #4ade80; font-weight: 700;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label>New Balance (SYP)</label>
                    <input type="number" name="wallet_balance" id="walletNewBalance" required min="0" step="1" placeholder="Enter new balance">
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end; border-top: 1px solid var(--border); padding-top: 1.25rem;">
                    <button type="button" onclick="closeWalletModal()" class="btn" style="background: rgba(255,255,255,0.05); color: var(--text-muted);">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="padding-left: 2rem; padding-right: 2rem;">💾 Save Balance</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="userModal" class="overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">{{ __('admin.add_new') }}</h2>
                <button class="close-modal" onclick="closeModal()">✕</button>
            </div>
            
            <form id="userForm" method="POST">
                @csrf
                <div id="methodField"></div>

                <div style="margin-bottom: 1.25rem;">
                    <label>Full Name</label>
                    <input type="text" name="name" id="name" required placeholder="John Doe">
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label>Email Address</label>
                    <input type="email" name="email" id="email" required placeholder="john@example.com">
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label>Password</label>
                    <input type="password" name="password" id="password" placeholder="••••••••">
                    <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">Leave blank to stay unchanged when editing.</small>
                </div>

                <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
                    <div style="flex: 1;">
                        <label>App Role</label>
                        <select name="role" id="role" onchange="toggleServiceCategory()">
                            <option value="user">User/Rider</option>
                            <option value="driver">Driver</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label>Admin Access</label>
                        <select name="is_admin" id="is_admin" onchange="toggleAdminRole()">
                            <option value="0">No Access</option>
                            <option value="1">Admin Access</option>
                        </select>
                    </div>
                </div>

                <div id="serviceCategorySection" style="margin-bottom: 1.25rem; display: none;">
                    <label>Service Category</label>
                    <select name="service_category" id="service_category">
                        <option value="economy">🟢 Economy</option>
                        <option value="comfort">🔵 Comfort</option>
                        <option value="premium">🟡 Premium</option>
                    </select>
                    <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">Determines which ride requests this driver receives.</small>
                </div>

                <div id="adminRoleSection" style="margin-bottom: 2rem; display: none;">
                    <label>Admin Dashboard Role</label>
                    <select name="admin_role" id="admin_role">
                        <option value="super_admin">Super Admin (All Access)</option>
                        <option value="accountant">Accountant</option>
                        <option value="data_entry">Data Entry</option>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; border-top: 1px solid var(--border); padding-top: 1.25rem;">
                    <button type="button" onclick="closeModal()" class="btn" style="background: rgba(255,255,255,0.05); color: var(--text-muted);">{{ __('admin.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" style="padding-left: 2rem; padding-right: 2rem;">✨ {{ __('admin.save') }}</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleAdminRole() {
            const isAdmin = document.getElementById('is_admin').value == '1';
            document.getElementById('adminRoleSection').style.display = isAdmin ? 'block' : 'none';
        }

        function toggleServiceCategory() {
            const role = document.getElementById('role').value;
            document.getElementById('serviceCategorySection').style.display = role === 'driver' ? 'block' : 'none';
        }

        function openModal(mode) {
            document.getElementById('userModal').classList.add('show');
            if (mode === 'add') {
                document.getElementById('modalTitle').innerText = "{{ __('admin.add_new') }}";
                document.getElementById('userForm').action = "{{ route('admin.users.store') }}";
                document.getElementById('methodField').innerHTML = '';
                document.getElementById('name').value = '';
                document.getElementById('email').value = '';
                document.getElementById('role').value = 'user';
                document.getElementById('service_category').value = 'economy';
                document.getElementById('is_admin').value = '0';
                document.getElementById('admin_role').value = 'data_entry';
                document.getElementById('password').required = true;
                toggleAdminRole();
                toggleServiceCategory();
            }
        }

        function editUser(user) {
            document.getElementById('userModal').classList.add('show');
            document.getElementById('modalTitle').innerText = "{{ __('admin.edit') }}";
            document.getElementById('userForm').action = "users/" + user.id;
            document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            
            document.getElementById('name').value = user.name;
            document.getElementById('email').value = user.email;
            document.getElementById('role').value = user.role;
            document.getElementById('service_category').value = user.service_category || 'economy';
            document.getElementById('is_admin').value = user.is_admin ? '1' : '0';
            document.getElementById('admin_role').value = user.admin_role || 'data_entry';
            document.getElementById('password').required = false;
            toggleAdminRole();
            toggleServiceCategory();
        }

        function closeModal() {
            document.getElementById('userModal').classList.remove('show');
        }

        function openWalletModal(userId, userName, currentBalance) {
            document.getElementById('walletModal').classList.add('show');
            document.getElementById('walletDriverName').value = userName;
            document.getElementById('walletCurrentBalance').value = Math.round(currentBalance) + ' SYP';
            document.getElementById('walletNewBalance').value = Math.round(currentBalance);
            document.getElementById('walletForm').action = "{{ url('admin/users') }}/" + userId + "/wallet";
        }

        function closeWalletModal() {
            document.getElementById('walletModal').classList.remove('show');
        }
    </script>

@endsection
