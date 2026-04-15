<?php
// C:\xampp\htdocs\dashboardtaxi\views\rides.php
$statusFilter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$pageNo = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($pageNo - 1) * $limit;

$rides = $rideModel->getRides($statusFilter, $search, $limit, $offset);
$totalRides = $rideModel->getRideCount($statusFilter, $search);
$totalPages = ceil($totalRides / $limit);
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800"><?php echo __('ride_management'); ?></h2>
            <p class="text-slate-400 text-sm"><?php echo __('ride_tracking_desc'); ?></p>
        </div>
        <div class="flex bg-slate-100 p-1 rounded-xl">
            <a href="?p=rides" class="px-4 py-2 <?php echo $statusFilter === '' ? 'bg-primary text-white font-black' : 'text-slate-500 hover:text-slate-800'; ?> rounded-lg text-[10px] transition uppercase tracking-widest"><?php echo __('all_filter'); ?></a>
            <a href="?p=rides&status=searching" class="px-4 py-2 <?php echo $statusFilter === 'searching' ? 'bg-primary text-white font-black' : 'text-slate-500 hover:text-slate-800'; ?> rounded-lg text-[10px] transition uppercase tracking-widest"><?php echo __('searching_filter'); ?></a>
            <a href="?p=rides&status=accepted" class="px-4 py-2 <?php echo $statusFilter === 'accepted' ? 'bg-primary text-white font-black' : 'text-slate-500 hover:text-slate-800'; ?> rounded-lg text-[10px] transition uppercase tracking-widest"><?php echo __('live_filter'); ?></a>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-card p-4 rounded-[32px] flex flex-wrap items-center gap-4">
        <form method="GET" class="flex flex-wrap items-center gap-4 w-full">
            <input type="hidden" name="p" value="rides">
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="SEARCH BY ID, RIDE CODE, OR NAME..." 
                       class="w-full bg-slate-50 border border-slate-100 rounded-2xl pl-11 pr-4 py-3 text-[10px] font-black uppercase tracking-widest focus:outline-none focus:border-primary transition group-hover:bg-white">
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
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400"><?php echo __('ride_id'); ?></th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">RIDE CODE</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400"><?php echo __('rider'); ?></th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400"><?php echo __('captain'); ?></th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400"><?php echo __('route'); ?></th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400"><?php echo __('price'); ?></th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400"><?php echo __('status'); ?></th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center"><?php echo __('live_feed'); ?></th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($rides as $r): ?>
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 font-black text-xs text-primary uppercase tracking-tighter">#<?php echo $r['id']; ?></td>
                        <td class="px-6 py-4 font-black text-xs text-slate-400 tracking-widest uppercase"><?php echo htmlspecialchars($r['ride_code'] ?? '---'); ?></td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($r['rider_name'] ?? 'Guest'); ?></p>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter"><?php echo htmlspecialchars($r['rider_phone'] ?? ''); ?></p>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($r['driver_name']): ?>
                                <p class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($r['driver_name']); ?></p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter"><?php echo htmlspecialchars($r['driver_phone']); ?></p>
                            <?php else: ?>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-100 px-2 py-1 rounded-lg"><?php echo __('searching_msg'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <span class="text-[10px] font-bold text-slate-500 truncate max-w-[120px] uppercase tracking-tighter"><?php echo htmlspecialchars($r['pickup_address']); ?></span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-primary animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                                <span class="text-[10px] font-bold text-slate-500 truncate max-w-[120px] uppercase tracking-tighter"><?php echo htmlspecialchars($r['dropoff_address']); ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-black text-xs text-slate-800"><?php echo number_format($r['ride_price']); ?> <span class="text-[10px] text-slate-400 font-normal italic">SYP</span></td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter <?php 
                                echo match($r['status']) {
                                    'searching' => 'bg-yellow-100 text-yellow-700 border border-yellow-200',
                                    'accepted', 'started', 'arrived' => 'bg-primary/10 text-primary border border-primary/20',
                                    'completed' => 'bg-green-100 text-green-700 border border-green-200',
                                    'cancelled' => 'bg-red-100 text-red-700 border border-red-200',
                                    default => 'bg-slate-100 text-slate-500',
                                };
                            ?>">
                                <?php echo __($r['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="?p=live_map&ride_id=<?php echo $r['id']; ?>" class="inline-block p-2 bg-white rounded-xl text-primary hover:bg-primary hover:text-white transition shadow-sm border border-slate-100 hover:border-primary active:scale-90" title="Live Tracking">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </a>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <!-- Comprehensive Edit -->
                                <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8'); ?>)" 
                                        class="p-2 bg-white rounded-xl text-primary hover:bg-primary hover:text-white transition shadow-sm border border-slate-100 hover:border-primary" 
                                        title="Full Control Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>

                                <!-- Edit Price -->
                                <button onclick="openPriceModal('<?php echo $r['id']; ?>', '<?php echo $r['ride_price']; ?>')" 
                                        class="p-2 bg-white rounded-xl text-yellow-600 hover:bg-yellow-500 hover:text-white transition shadow-sm border border-slate-100 hover:border-yellow-500" 
                                        title="Edit Price Only">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>

                                <?php if ($r['status'] !== 'completed' && $r['status'] !== 'cancelled'): ?>
                                    <!-- Simple Complete -->
                                    <form action="./api/ride_action.php" method="POST" class="inline" onsubmit="return confirm('Complete Ride #<?php echo $r['id']; ?> WITHOUT financial deduction?')">
                                        <input type="hidden" name="action" value="complete_simple">
                                        <input type="hidden" name="ride_id" value="<?php echo $r['id']; ?>">
                                        <button type="submit" class="p-2 bg-white rounded-xl text-green-500 hover:bg-green-500 hover:text-white transition shadow-sm border border-slate-100 hover:border-green-500" title="Simple Complete">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </form>

                                    <!-- Financial Complete -->
                                    <?php if ($r['driver_id']): ?>
                                    <form action="./api/ride_action.php" method="POST" class="inline" onsubmit="return confirm('Complete Ride #<?php echo $r['id']; ?> AND DEDUCT commission from driver balance?')">
                                        <input type="hidden" name="action" value="complete_financial">
                                        <input type="hidden" name="ride_id" value="<?php echo $r['id']; ?>">
                                        <button type="submit" class="p-2 bg-white rounded-xl text-purple-500 hover:bg-purple-500 hover:text-white transition shadow-sm border border-slate-100 hover:border-purple-500" title="Financial Complete (Deduct Commission)">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- Delete -->
                                <form action="./api/ride_action.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete Ride #<?php echo $r['id']; ?>?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="ride_id" value="<?php echo $r['id']; ?>">
                                    <button type="submit" class="inline-block p-2 bg-white rounded-xl text-red-500 hover:bg-red-500 hover:text-white transition shadow-sm border border-slate-100 hover:border-red-500" title="Delete Ride">
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
        <div class="px-6 py-4 bg-slate-50/50 flex items-center justify-between border-t border-slate-100">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest"><?php echo $totalRides; ?> <?php echo __('events_tracked'); ?></span>
            <div class="flex space-x-1">
                <?php if ($pageNo > 1): ?>
                    <a href="?p=rides&page=<?php echo $pageNo - 1; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-1 rounded-lg bg-white border border-slate-200 text-[10px] font-black text-slate-400 hover:text-primary transition uppercase tracking-widest"><?php echo __('prev'); ?></a>
                <?php endif; ?>
                <span class="px-3 py-1 rounded-lg bg-primary text-white text-[10px] font-black tabular-nums transition uppercase tracking-widest"><?php echo $pageNo; ?> / <?php echo max(1, $totalPages); ?></span>
                <?php if ($pageNo < $totalPages): ?>
                    <a href="?p=rides&page=<?php echo $pageNo + 1; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-1 rounded-lg bg-white border border-slate-200 text-[10px] font-black text-slate-400 hover:text-primary transition uppercase tracking-widest"><?php echo __('next'); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Price Modal (Simple) -->
<div id="price-modal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display:none;">
    <div class="glass-card w-full max-w-md p-10 rounded-[40px] shadow-2xl">
        <div class="flex justify-between items-center mb-8">
            <h3 class="text-xl font-black text-slate-800 uppercase tracking-tighter">Edit Ride Price</h3>
            <button onclick="closePriceModal()" class="text-slate-400 hover:text-slate-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        
        <form action="./api/ride_action.php" method="POST" class="space-y-6">
            <input type="hidden" name="action" value="update_price">
            <input type="hidden" name="ride_id" id="modal-ride-id">
            
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">New Price (SYP)</label>
                <input type="number" name="ride_price" id="modal-ride-price" required 
                       class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-sm font-black text-slate-800 focus:outline-none focus:border-primary transition">
            </div>

            <button type="submit" class="w-full bg-primary text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-premium hover:scale-105 transition-all">Update Price</button>
        </form>
    </div>
</div>

<!-- Comprehensive Full Edit Modal -->
<div id="edit-all-modal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display:none;">
    <div class="glass-card w-full max-w-2xl p-10 rounded-[40px] shadow-2xl overflow-y-auto max-h-[90vh]">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tighter">Full Ride Control</h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Editing Ride #<span id="edit-ride-id-display"></span></p>
            </div>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        
        <form action="./api/ride_action.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="ride_id" id="edit-ride-id">
            
            <div class="space-y-2 md:col-span-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Ride Code</label>
                <input type="text" name="ride_code" id="edit-ride-code" required class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-sm font-black text-slate-800 focus:outline-none focus:border-primary transition uppercase tracking-widest">
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Pickup Address</label>
                <input type="text" name="pickup_address" id="edit-pickup" required class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-xs font-bold text-slate-800 focus:outline-none focus:border-primary transition uppercase">
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Dropoff Address</label>
                <input type="text" name="dropoff_address" id="edit-dropoff" required class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-xs font-bold text-slate-800 focus:outline-none focus:border-primary transition uppercase">
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Status</label>
                <select name="status" id="edit-status" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-xs font-black text-slate-800 focus:outline-none focus:border-primary transition uppercase">
                    <option value="pending">Pending</option>
                    <option value="searching">Searching</option>
                    <option value="accepted">Accepted</option>
                    <option value="arrived">Arrived</option>
                    <option value="started">Started</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Ride Price (SYP)</label>
                <input type="number" name="ride_price" id="edit-price" required class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-sm font-black text-slate-800 focus:outline-none focus:border-primary transition">
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Car Type</label>
                <select name="car_type" id="edit-car-type" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-xs font-black text-slate-800 focus:outline-none focus:border-primary transition uppercase">
                    <option value="standard">Standard</option>
                    <option value="premium">Premium</option>
                    <option value="xl">XL (Large)</option>
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Payment Method</label>
                <select name="payment_method" id="edit-payment" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-xs font-black text-slate-800 focus:outline-none focus:border-primary transition uppercase">
                    <option value="cash">Cash</option>
                    <option value="wallet">Wallet</option>
                </select>
            </div>

            <div class="md:col-span-2 pt-4">
                <button type="submit" class="w-full bg-primary text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-premium hover:scale-105 transition-all">Save All Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPriceModal(id, price) {
        document.getElementById('modal-ride-id').value = id;
        document.getElementById('modal-ride-price').value = Math.round(price);
        document.getElementById('price-modal').style.display = 'flex';
    }
    function closePriceModal() {
        document.getElementById('price-modal').style.display = 'none';
    }

    function openEditModal(ride) {
        document.getElementById('edit-ride-id').value = ride.id;
        document.getElementById('edit-ride-id-display').innerText = ride.id;
        document.getElementById('edit-ride-code').value = ride.ride_code || '';
        document.getElementById('edit-pickup').value = ride.pickup_address;
        document.getElementById('edit-dropoff').value = ride.dropoff_address;
        document.getElementById('edit-status').value = ride.status;
        document.getElementById('edit-price').value = Math.round(ride.ride_price);
        document.getElementById('edit-car-type').value = ride.car_type || 'standard';
        document.getElementById('edit-payment').value = ride.payment_method || 'cash';
        
        document.getElementById('edit-all-modal').style.display = 'flex';
    }
    function closeEditModal() {
        document.getElementById('edit-all-modal').style.display = 'none';
    }
</script>

<?php if (isset($_SESSION['success'])): ?>
<div id="toast-success" class="fixed top-6 right-6 z-50 animate__animated animate__fadeInRight">
    <div class="flex items-center gap-3 bg-green-500 text-white px-6 py-4 rounded-2xl shadow-2xl font-bold text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
</div>
<script>setTimeout(() => document.getElementById('toast-success')?.remove(), 3000);</script>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div id="toast-error" class="fixed top-6 right-6 z-50 animate__animated animate__fadeInRight">
    <div class="flex items-center gap-3 bg-red-500 text-white px-6 py-4 rounded-2xl shadow-2xl font-bold text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
</div>
<script>setTimeout(() => document.getElementById('toast-error')?.remove(), 3000);</script>
<?php endif; ?>
