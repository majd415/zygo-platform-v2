<?php
// C:\xampp\htdocs\dashboardtaxi\views\coupons.php

$coupons = $couponModel->getCoupons();
?>

<div class="space-y-8" x-data="{ showCreateModal: false }">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-black text-slate-800 italic tracking-tighter uppercase"><?php echo __('promo_engine'); ?></h2>
            <p class="text-slate-400 mt-1 uppercase text-[10px] font-black tracking-widest leading-none"><?php echo __('promo_desc'); ?></p>
        </div>
        <button @click="showCreateModal = true" class="px-8 py-3 bg-primary text-white font-black rounded-2xl text-[10px] uppercase tracking-widest shadow-premium hover:scale-105 transition-all">
            <?php echo __('create_new_promo'); ?>
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="glass-card p-6 rounded-[32px] border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1"><?php echo __('active_coupons'); ?></p>
                <h3 class="text-3xl font-black text-slate-800 italic"><?php echo count($coupons); ?></h3>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
            </div>
        </div>
        <!-- More stats could go here -->
    </div>

    <!-- Coupons Table -->
    <div class="glass-card rounded-[40px] overflow-hidden border-slate-100 shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-50 bg-slate-50/50">
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400"><?php echo __('coupon_code'); ?></th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400"><?php echo __('type_value'); ?></th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400"><?php echo __('description'); ?></th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400"><?php echo __('validity'); ?></th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400"><?php echo __('status'); ?></th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right"><?php echo __('action'); ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($coupons)): ?>
                        <tr><td colspan="6" class="px-8 py-12 text-center text-slate-400 font-black text-[10px] uppercase tracking-widest"><?php echo __('no_promo_msg'); ?></td></tr>
                    <?php endif; ?>
                    <?php foreach ($coupons as $c): ?>
                    <tr class="hover:bg-slate-50/50 transition-all duration-300 group">
                        <td class="px-8 py-6">
                            <span class="bg-primary/5 border border-primary/10 px-4 py-2 rounded-xl text-primary font-black uppercase tracking-widest text-[11px] shadow-sm group-hover:bg-primary group-hover:text-white transition-all">
                                <?php echo htmlspecialchars($c['code']); ?>
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            <p class="text-sm font-black text-slate-800 tracking-tighter">
                                <?php if ($c['discount_percentage'] > 0): ?>
                                    <?php echo __('percentage'); ?> / <span class="text-primary"><?php echo $c['discount_percentage']; ?>%</span>
                                <?php else: ?>
                                    <?php echo __('fixed'); ?> / <span class="text-primary"><?php echo $c['fixed_discount']; ?> SYP</span>
                                <?php endif; ?>
                            </p>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5"><?php echo __('discount_magnitude'); ?></p>
                        </td>
                        <td class="px-8 py-6">
                            <p class="text-xs font-bold text-slate-500 line-clamp-1 italic"><?php echo htmlspecialchars($c['description'] ?? __('no_description')); ?></p>
                        </td>
                        <td class="px-8 py-6">
                            <p class="text-[10px] font-black text-slate-700 uppercase tracking-widest">
                                <?php echo $c['starts_at'] ? date('M d', strtotime($c['starts_at'])) : '∞'; ?> - 
                                <?php echo date('M d, Y', strtotime($c['expiration_date'])); ?>
                            </p>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5"><?php echo __('campaign_duration'); ?></p>
                        </td>
                        <td class="px-8 py-6">
                            <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter <?php echo $c['is_active'] ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'; ?> shadow-sm">
                                <?php echo $c['is_active'] ? __('active') : __('inactive'); ?>
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <form action="api/coupon_action.php" method="POST" onsubmit="return confirm('<?php echo __('confirm_delete_promo'); ?>');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                <button type="submit" class="p-3 hover:bg-white rounded-2xl text-slate-400 hover:text-red-500 transition-all border border-transparent hover:border-red-50 shadow-sm hover:shadow-md active:scale-90">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Coupon Modal -->
    <div x-show="showCreateModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-md p-6" x-cloak>
        <div class="glass-card max-w-xl w-full p-10 rounded-[48px] bg-white shadow-2xl" @click.away="showCreateModal = false">
            <h3 class="text-3xl font-black italic mb-2 text-slate-800 uppercase tracking-tighter"><?php echo __('forge_promo'); ?></h3>
            <p class="text-slate-400 text-sm mb-10 leading-relaxed font-medium"><?php echo __('forge_desc'); ?></p>
            
            <form action="api/coupon_action.php" method="POST" class="space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 tracking-widest"><?php echo __('coupon_code'); ?></label>
                        <input type="text" name="code" required class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-xs font-black uppercase focus:outline-none focus:border-primary transition-colors" placeholder="e.g. SUMMER2026">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 tracking-widest"><?php echo __('type'); ?></label>
                        <select name="type" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-xs font-black uppercase focus:outline-none focus:border-primary transition-colors">
                            <option value="percentage"><?php echo __('percentage'); ?> (%)</option>
                            <option value="fixed"><?php echo __('fixed'); ?> (SYP)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 tracking-widest"><?php echo __('price'); ?></label>
                        <input type="number" name="value" required class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-xs font-black focus:outline-none focus:border-primary transition-colors" placeholder="e.g. 15">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 tracking-widest"><?php echo __('campaign_description'); ?></label>
                        <input type="text" name="description" required class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-xs font-bold italic focus:outline-none focus:border-primary transition-colors" placeholder="e.g. Summer special discount">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 tracking-widest"><?php echo __('starts_at'); ?></label>
                        <input type="date" name="starts_at" required class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-xs font-bold focus:outline-none focus:border-primary transition-colors">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 tracking-widest"><?php echo __('expires_at'); ?></label>
                        <input type="date" name="expires_at" required class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-xs font-bold focus:outline-none focus:border-primary transition-colors">
                    </div>
                </div>

                <div class="flex space-x-4 pt-4">
                    <button type="button" @click="showCreateModal = false" class="flex-1 py-5 rounded-2xl bg-slate-100 text-slate-500 font-black text-[10px] uppercase tracking-widest hover:bg-slate-200 transition"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="flex-1 py-5 rounded-2xl bg-primary text-white font-black text-[10px] uppercase tracking-widest hover:shadow-premium shadow-lg transition"><?php echo __('execute_deployment'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
