<?php
// C:\xampp\htdocs\dashboardtaxi\views\settings.php
$settingsList = $model->all('settings');
$s = $settingsList[0] ?? [
    'price_per_km_syp' => 2500,
    'search_radius_km' => 5.0,
    'id' => 1
];
?>

<form action="./api/settings_action.php" method="POST" class="space-y-8 animate__animated animate__fadeIn">
    <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
    
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-black text-slate-800 italic tracking-tighter uppercase"><?php echo __('global_parameters'); ?></h2>
            <p class="text-slate-400 mt-1 uppercase text-[10px] font-black tracking-widest leading-none"><?php echo __('system_fee_config'); ?></p>
        </div>
        <div class="flex space-x-3">
            <button type="button" onclick="testDiag()" class="bg-slate-100 text-slate-800 px-6 py-3.5 rounded-2xl font-black text-[10px] uppercase tracking-widest border border-slate-200 hover:bg-slate-200 transition-all">Test Diag</button>
            <button type="submit" class="bg-primary text-white px-8 py-3.5 rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-premium hover:scale-105 transition-all"><?php echo __('save_all_changes_btn'); ?></button>
        </div>
    </div>

    <script>
    function testDiag() {
        const formData = new FormData();
        formData.append('test', '1');
        fetch('./api/test_post.php', {
            method: 'POST',
            body: formData
        }).then(r => r.text()).then(t => alert(t));
    }
    </script>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Revenue Settings -->
        <div class="glass-card p-10 rounded-[48px] space-y-8">
            <h3 class="text-xl font-black text-slate-800 flex items-center uppercase tracking-tighter">
                <span class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mr-4 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <?php echo __('commission_rates'); ?>
            </h3>
            
            <div class="space-y-6">
                <!-- Placeholder for platform fee slider - currently static visual -->
                <div class="p-6 rounded-3xl bg-slate-50 border border-slate-100">
                    <div class="flex justify-between mb-5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none"><?php echo __('base_platform_fee'); ?> (%)</label>
                        <span id="commission_val" class="text-primary font-black text-xs uppercase"><?php echo number_format($s['commission_rate'] ?? 15, 1); ?>%</span>
                    </div>
                    <input type="range" name="commission_rate" min="0" max="50" step="0.5" 
                           value="<?php echo $s['commission_rate'] ?? 15; ?>" 
                           oninput="document.getElementById('commission_val').innerText = parseFloat(this.value).toFixed(1) + '%'"
                           onchange="this.form.submit()"
                           class="w-full accent-primary bg-slate-200 h-1.5 rounded-lg cursor-pointer appearance-none transition-all hover:bg-slate-300">
                </div>
                
                <div class="p-6 rounded-3xl bg-slate-50 border border-slate-100">
                    <div class="flex justify-between mb-5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none"><?php echo __('ride_price_km_stat'); ?> (SYP)</label>
                        <span class="text-slate-800 font-black text-xs"><?php echo number_format($s['price_per_km_syp'] ?? 0); ?></span>
                    </div>
                    <input type="number" step="0.01" name="price_per_km_syp" class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 text-sm font-black text-slate-800 focus:outline-none focus:border-primary transition" value="<?php echo $s['price_per_km_syp'] ?? ''; ?>">
                </div>
                
                <div class="p-6 rounded-3xl bg-slate-50 border border-slate-100">
                    <div class="flex justify-between mb-5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">WAITING FEE PER 5 MIN (SYP)</label>
                        <span class="text-slate-800 font-black text-xs"><?php echo number_format($s['waiting_fee_per_5_min_syp'] ?? 500); ?></span>
                    </div>
                    <input type="number" step="0.01" name="waiting_fee_per_5_min_syp" class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 text-sm font-black text-slate-800 focus:outline-none focus:border-primary transition" value="<?php echo $s['waiting_fee_per_5_min_syp'] ?? 500; ?>">
                </div>

                <div class="p-6 rounded-3xl bg-slate-50 border border-slate-100">
                    <div class="flex justify-between mb-5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">MINIMUM TRIP FARE (SYP)</label>
                        <span class="text-slate-800 font-black text-xs"><?php echo number_format($s['min_fare_syp'] ?? 5000); ?></span>
                    </div>
                    <input type="number" step="0.01" name="min_fare_syp" class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 text-sm font-black text-slate-800 focus:outline-none focus:border-primary transition" value="<?php echo $s['min_fare_syp'] ?? 5000; ?>">
                </div>
            </div>
        </div>

        <!-- Operations Settings -->
        <div class="glass-card p-10 rounded-[48px] space-y-8">
            <h3 class="text-xl font-black text-slate-800 flex items-center uppercase tracking-tighter">
                <span class="w-10 h-10 rounded-xl bg-accent/10 text-accent flex items-center justify-center mr-4 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </span>
                <?php echo __('dispatch_engine'); ?>
            </h3>
            
            <div class="space-y-6">
                <div class="flex items-center justify-between p-6 rounded-3xl bg-slate-50 border border-slate-100">
                    <div>
                        <p class="text-xs font-black text-slate-800 uppercase tracking-tighter"><?php echo __('auto_assignment'); ?></p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1"><?php echo __('direct_dispatch_desc'); ?></p>
                    </div>
                    <div class="w-12 h-6 bg-primary rounded-full relative shadow-inner cursor-pointer">
                        <div class="absolute right-1 top-1 w-4 h-4 bg-white rounded-full transition-all shadow-md"></div>
                    </div>
                </div>

                <div class="p-6 rounded-3xl bg-slate-50 border border-slate-100">
                    <div class="flex justify-between mb-5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none"><?php echo __('search_radius_stat'); ?> (KM)</label>
                        <span class="text-slate-800 font-black text-xs"><?php echo $s['search_radius_km']; ?> KM</span>
                    </div>
                    <div class="relative">
                        <input type="number" step="0.1" name="search_radius_km" 
                               onchange="this.form.submit()"
                               class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 text-sm font-black text-slate-800 focus:outline-none focus:border-primary transition" value="<?php echo $s['search_radius_km']; ?>">
                        <div class="absolute right-5 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 uppercase tracking-widest pointer-events-none">KM</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Category Multipliers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
        <div class="glass-card p-10 rounded-[48px] space-y-8">
            <h3 class="text-xl font-black text-slate-800 flex items-center uppercase tracking-tighter">
                <span class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center mr-4 shadow-sm">🚗</span>
                SERVICE CATEGORY MULTIPLIERS
            </h3>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Economy = base price (×1.0). Comfort and Premium are multiplied by these values.</p>
            
            <div class="space-y-6">
                <div class="p-6 rounded-3xl bg-slate-50 border border-slate-100">
                    <div class="flex justify-between mb-5">
                        <label class="text-[10px] font-black text-green-500 uppercase tracking-widest leading-none">🟢 ECONOMY</label>
                        <span class="text-slate-800 font-black text-xs">×1.00 (BASE)</span>
                    </div>
                    <input type="text" value="×1.00 — Base Price" readonly class="w-full bg-slate-100 border border-slate-200 rounded-2xl px-5 py-4 text-sm font-black text-slate-400 cursor-not-allowed">
                </div>

                <div class="p-6 rounded-3xl bg-slate-50 border border-slate-100">
                    <div class="flex justify-between mb-5">
                        <label class="text-[10px] font-black text-blue-500 uppercase tracking-widest leading-none">🔵 COMFORT MULTIPLIER</label>
                        <span class="text-slate-800 font-black text-xs">×<?php echo number_format($s['comfort_multiplier'] ?? 1.10, 2); ?></span>
                    </div>
                    <input type="number" step="0.01" min="1.00" max="5.00" name="comfort_multiplier" class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 text-sm font-black text-slate-800 focus:outline-none focus:border-blue-500 transition" value="<?php echo $s['comfort_multiplier'] ?? 1.10; ?>">
                    <p class="text-[9px] text-slate-400 mt-2 uppercase tracking-wider">e.g. 1.10 = +10% over economy</p>
                </div>

                <div class="p-6 rounded-3xl bg-slate-50 border border-slate-100">
                    <div class="flex justify-between mb-5">
                        <label class="text-[10px] font-black text-amber-500 uppercase tracking-widest leading-none">🟡 PREMIUM MULTIPLIER</label>
                        <span class="text-slate-800 font-black text-xs">×<?php echo number_format($s['premium_multiplier'] ?? 1.25, 2); ?></span>
                    </div>
                    <input type="number" step="0.01" min="1.00" max="5.00" name="premium_multiplier" class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 text-sm font-black text-slate-800 focus:outline-none focus:border-amber-500 transition" value="<?php echo $s['premium_multiplier'] ?? 1.25; ?>">
                    <p class="text-[9px] text-slate-400 mt-2 uppercase tracking-wider">e.g. 1.25 = +25% over economy</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
        <!-- Security & Debugging -->
        <div class="glass-card p-10 rounded-[48px] space-y-8 border-primary/10 bg-primary/[0.02]">
            <h3 class="text-xl font-black text-slate-800 flex items-center uppercase tracking-tighter text-primary">
                <span class="w-10 h-10 rounded-xl bg-primary/20 text-primary flex items-center justify-center mr-4 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </span>
                <?php echo __('security_control'); ?>
            </h3>
            
            <div class="space-y-6">
                <div class="flex items-center justify-between p-6 rounded-3xl bg-white border border-primary/10 shadow-sm">
                    <div>
                        <p class="text-xs font-black text-primary uppercase tracking-tighter"><?php echo __('magic_login_bypass'); ?></p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">ENABLE LOGIN VIA MAGIC CODE (123456) FOR TESTING</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="magic_login_enabled" value="1" class="sr-only peer" onchange="this.form.submit()" <?php echo ($s['magic_login_enabled'] ?? 0) ? 'checked' : ''; ?>>
                        <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary border-2 border-transparent peer-checked:border-primary/20 shadow-inner"></div>
                    </label>
                </div>

                <div class="p-6 rounded-3xl bg-slate-50 border border-slate-100 opacity-50 cursor-not-allowed">
                    <div class="flex justify-between mb-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">DEBUG MODE SENSITIVITY</p>
                        <span class="text-slate-400 font-black text-xs uppercase">NORMAL</span>
                    </div>
                    <div class="h-1.5 w-full bg-slate-200 rounded-full overflow-hidden">
                        <div class="h-full w-1/3 bg-slate-300"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

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
