<?php
// C:\xampp\htdocs\dashboardtaxi\views\users.php
$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$pageNo = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($pageNo - 1) * $limit;

$users = $userModel->getUsers($roleFilter, $search, $limit, $offset);
$totalUsers = $userModel->getUserCount($roleFilter, $search);
$totalPages = ceil($totalUsers / $limit);
?>

<?php
$allUserIds = array_column($users, 'id');
?>

<div class="space-y-6" x-data="{ 
    showEditModal: false, 
    showWalletModal: false, 
    selectedUser: null,
    selectedIds: [],
    get allIds() { return <?php echo json_encode($allUserIds); ?>; },
    toggleAll() {
        if (this.selectedIds.length === this.allIds.length) {
            this.selectedIds = [];
        } else {
            this.selectedIds = [...this.allIds];
        }
    },
    exportUsers() {
        const ids = this.selectedIds.join(',');
        const role = '<?php echo $roleFilter; ?>';
        const search = '<?php echo urlencode($search); ?>';
        window.location.href = `api/export_users.php?ids=${ids}&role=${role}&search=${search}`;
    }
}">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800"><?php echo __('user_registry'); ?></h2>
            <p class="text-slate-400 text-sm"><?php echo __('user_registry_desc'); ?></p>
        </div>
        <div class="flex items-center space-x-3">
            <button @click="exportUsers()" class="bg-slate-100 border border-slate-200 text-slate-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition">
                <span x-text="selectedIds.length > 0 ? `<?php echo __('export'); ?> (${selectedIds.length})` : '<?php echo __('export_csv'); ?>'"></span>
            </button>
            <button class="bg-primary text-white px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:shadow-premium transition"><?php echo __('add_new_user'); ?></button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex items-center space-x-2 border-b border-slate-100 mb-6">
        <a href="?p=users&role=" class="px-6 py-3 text-[10px] font-black uppercase tracking-widest transition-all border-b-2 <?php echo $roleFilter === '' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-600'; ?>">
            <?php echo __('all_members'); ?>
        </a>
        <a href="?p=users&role=rider" class="px-6 py-3 text-[10px] font-black uppercase tracking-widest transition-all border-b-2 <?php echo $roleFilter === 'rider' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-600'; ?>">
            <?php echo __('users'); ?>
        </a>
        <a href="?p=users&role=driver" class="px-6 py-3 text-[10px] font-black uppercase tracking-widest transition-all border-b-2 <?php echo $roleFilter === 'driver' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-600'; ?>">
            <?php echo __('drivers'); ?>
        </a>
        <a href="?p=users&role=admin" class="px-6 py-3 text-[10px] font-black uppercase tracking-widest transition-all border-b-2 <?php echo $roleFilter === 'admin' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-600'; ?>">
            <?php echo __('administrators'); ?>
        </a>
    </div>

    <!-- Filters -->
    <div class="glass-card p-4 rounded-[32px] flex flex-wrap items-center gap-4">
        <form method="GET" class="flex flex-wrap items-center gap-4 w-full">
            <input type="hidden" name="p" value="users">
            <input type="hidden" name="role" value="<?php echo htmlspecialchars($roleFilter); ?>">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="<?php echo __('search_placeholder'); ?>" 
                       class="w-full bg-slate-50 border border-slate-100 rounded-2xl pl-11 pr-4 py-3 text-xs font-medium focus:outline-none focus:border-primary transition group-hover:bg-white">
            </div>
            <button type="submit" class="bg-slate-800 hover:bg-slate-900 px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-white transition"><?php echo __('search'); ?></button>
        </form>
    </div>

    <!-- Table -->
    <div class="glass-card rounded-[40px] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" @click="toggleAll()" :checked="selectedIds.length === allIds.length && allIds.length > 0"
                                   class="rounded border-slate-200 text-primary focus:ring-primary/20">
                        </th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400"><?php echo __('user_profile'); ?></th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400"><?php echo __('phone'); ?></th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400"><?php echo __('access'); ?></th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Category</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400"><?php echo __('rating'); ?></th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Wallet</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400"><?php echo __('status'); ?></th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400"><?php echo __('joined'); ?></th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right"><?php echo __('action'); ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($users)): ?>
                        <tr><td colspan="9" class="px-6 py-10 text-center text-slate-400 font-bold text-xs uppercase tracking-widest"><?php echo __('no_users_found'); ?></td></tr>
                    <?php endif; ?>
                    <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors group" :class="selectedIds.includes('<?php echo $u['id']; ?>') ? 'bg-primary/5' : ''">
                        <td class="px-6 py-4">
                            <input type="checkbox" x-model="selectedIds" value="<?php echo $u['id']; ?>"
                                   class="rounded border-slate-200 text-primary focus:ring-primary/20">
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex-shrink-0 flex items-center justify-center overflow-hidden">
                                    <?php if ($u['avatar']): ?>
                                        <img src="<?php echo asset_url($u['avatar']); ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <span class="text-primary font-black text-xs"><?php echo substr($u['name'], 0, 1); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800 group-hover:text-primary transition-colors"><?php echo htmlspecialchars($u['name']); ?></p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter"><?php echo htmlspecialchars($u['email']); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-[10px] font-black text-slate-500 tracking-widest"><?php echo htmlspecialchars($u['phone']); ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter <?php 
                                echo $u['role'] === 'driver' ? 'bg-primary/10 text-primary' : 
                                    ($u['role'] === 'admin' ? 'bg-accent/10 text-accent' : 'bg-slate-100 text-slate-500'); 
                            ?>">
                                <?php echo __($u['role']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($u['role'] === 'driver'): ?>
                                <?php 
                                    $cat = $u['service_category'] ?? 'economy';
                                    $catColors = ['economy' => 'green', 'comfort' => 'blue', 'premium' => 'amber'];
                                    $c = $catColors[$cat] ?? 'green';
                                ?>
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter bg-<?php echo $c; ?>-100 text-<?php echo $c; ?>-600">
                                    <?php echo ucfirst($cat); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-slate-300">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-slate-700">
                            <div class="flex items-center">
                                <span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-lg flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 fill-current mr-1" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                    <?php echo number_format($u['rating'] ?? 5.0, 1); ?>
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-xs font-black text-slate-800"><?php echo number_format($u['wallet_balance'] ?? 0); ?> <span class="text-[9px] text-slate-400 uppercase">SYP</span></span>
                                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter"><?php echo $u['role'] === 'driver' ? 'Driver Credit' : 'User Balance'; ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-500 shadow-sm"></div>
                                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest"><?php echo __('active'); ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <!-- Wallet Adjustment -->
                                <button class="p-2 hover:bg-white rounded-xl text-yellow-600 hover:text-yellow-700 transition-all border border-transparent hover:border-yellow-100 shadow-sm hover:shadow-md" 
                                        title="Adjust Wallet"
                                        @click="selectedUser = <?php echo htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8'); ?>; showWalletModal = true;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>

                                <button class="p-2 hover:bg-white rounded-xl text-slate-400 hover:text-primary transition-all border border-transparent hover:border-slate-100 shadow-sm hover:shadow-md" 
                                        @click="selectedUser = <?php echo htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8'); ?>; showEditModal = true;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <form method="POST" action="api/user_action.php" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="p-2 hover:bg-red-50 rounded-xl text-slate-400 hover:text-red-500 transition-all border border-transparent hover:border-red-100 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        ...
    </div>

    <!-- Edit User Modal -->
    <div x-show="showEditModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-md p-6" 
         x-cloak
         @keydown.escape.window="showEditModal = false">
        <div class="glass-card max-w-lg w-full rounded-[40px] bg-white shadow-2xl p-10 border-white/50 relative" 
             @click.away="showEditModal = false">
            
            <button @click="showEditModal = false" class="absolute top-8 right-8 text-slate-400 hover:text-slate-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>

            <h3 class="text-2xl font-bold text-slate-800 mb-8"><?php echo __('edit_user'); ?></h3>

            <form action="api/user_action.php" method="POST" class="space-y-6">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="user_id" :value="selectedUser ? selectedUser.id : ''">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1"><?php echo __('name'); ?></label>
                        <input type="text" name="name" :value="selectedUser ? selectedUser.name : ''" required
                               class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-3.5 text-sm font-bold text-slate-800 focus:outline-none focus:border-primary transition">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1"><?php echo __('phone'); ?></label>
                        <input type="text" name="phone" :value="selectedUser ? selectedUser.phone : ''" required
                               class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-3.5 text-sm font-bold text-slate-800 focus:outline-none focus:border-primary transition">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1"><?php echo __('email'); ?></label>
                    <input type="email" name="email" :value="selectedUser ? selectedUser.email : ''" required
                           class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-3.5 text-sm font-bold text-slate-800 focus:outline-none focus:border-primary transition">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1"><?php echo __('access'); ?> (Role)</label>
                        <select name="role" x-model="selectedUser.role" v-if="selectedUser"
                                class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-3.5 text-sm font-bold text-slate-800 focus:outline-none focus:border-primary transition appearance-none">
                            <option value="rider">Rider (User)</option>
                            <option value="driver">Driver (Captain)</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">New Password</label>
                        <input type="password" name="password" placeholder="Leave blank to keep current"
                               class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-3.5 text-sm font-bold text-slate-800 focus:outline-none focus:border-primary transition">
                    </div>
                </div>

                <!-- Service Category (visible only for drivers) -->
                <div class="space-y-2" x-show="selectedUser && selectedUser.role === 'driver'">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Service Category</label>
                    <select name="service_category" x-model="selectedUser.service_category"
                            class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-3.5 text-sm font-bold text-slate-800 focus:outline-none focus:border-primary transition appearance-none">
                        <option value="economy">🟢 Economy</option>
                        <option value="comfort">🔵 Comfort</option>
                        <option value="premium">🟡 Premium</option>
                    </select>
                    <p class="text-[9px] text-slate-400 ml-1">Determines which ride requests this driver receives.</p>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full bg-primary text-white py-4 rounded-2xl text-[11px] font-black uppercase tracking-widest hover:shadow-premium transition active:scale-[0.98]">
                        <?php echo __('save_changes'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Wallet Adjustment Modal -->
    <div x-show="showWalletModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-md p-6" 
         x-cloak
         @keydown.escape.window="showWalletModal = false">
        <div class="glass-card max-w-sm w-full rounded-[40px] bg-white shadow-2xl p-10 border-white/50 relative" 
             @click.away="showWalletModal = false">
            
            <button @click="showWalletModal = false" class="absolute top-8 right-8 text-slate-400 hover:text-slate-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>

            <h3 class="text-xl font-bold text-slate-800 mb-2">Adjust Wallet</h3>
            <p class="text-slate-400 text-xs mb-8 uppercase tracking-widest font-black" x-text="selectedUser ? selectedUser.name : ''"></p>

            <form action="api/user_action.php" method="POST" class="space-y-6">
                <input type="hidden" name="action" value="update_wallet">
                <input type="hidden" name="user_id" :value="selectedUser ? selectedUser.id : ''">

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">New Balance (SYP)</label>
                    <input type="number" name="wallet_balance" :value="selectedUser ? Math.round(selectedUser.wallet_balance) : 0" 
                           class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-3.5 text-sm font-black text-slate-800 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/5 transition">
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-primary text-white py-4 rounded-2xl text-[11px] font-black uppercase tracking-widest hover:shadow-premium transition active:scale-[0.98]">
                        Save New Balance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Note: showWalletModal needs to be added to x-data in line 18
</script>
