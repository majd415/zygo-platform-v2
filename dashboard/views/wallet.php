<?php
// C:\xampp\htdocs\dashboardtaxi\views\wallet.php
$stats = $walletModel->getWalletStats();
$cards = $walletModel->getCards();
?>

<div class="space-y-8" x-data="{ showGenerate: false, selectedCards: [], allCardIds: <?php echo htmlspecialchars(json_encode(array_map('strval', array_column($cards, 'id'))), ENT_QUOTES, 'UTF-8'); ?> }">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-black text-slate-800 italic tracking-tighter uppercase"><?php echo __('financial_hub_title'); ?></h2>
            <p class="text-slate-400 mt-1 uppercase text-[10px] font-black tracking-widest leading-none"><?php echo __('wallet_analytics_desc'); ?></p>
        </div>
        <div class="flex items-center space-x-4">
            <button @click="showGenerate = true" class="bg-primary text-white px-8 py-3.5 rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-premium hover:scale-105 transition-all active:scale-95">
                <?php echo __('generate_cards_btn'); ?>
            </button>
        </div>
    </div>

    <?php if (isset($_GET['batch'])): ?>
    <div class="glass-card p-8 rounded-[40px] border-2 border-green-400/20 bg-green-50/30 flex items-center justify-between">
        <div class="flex items-center space-x-6">
            <div class="w-16 h-16 rounded-3xl bg-green-100 flex items-center justify-center text-green-600 shadow-sm border border-green-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            </div>
            <div>
                <h4 class="text-xl font-black text-slate-800 italic tracking-tighter"><?php echo __('successfully_forged'); ?></h4>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1"><?php echo str_replace('{id}', '<span class="text-primary">'.$_GET['batch'].'</span>', __('batch_ready_msg')); ?></p>
            </div>
        </div>
        <a href="print_cards.php?batch=<?php echo $_GET['batch']; ?>" target="_blank" class="px-8 py-4 bg-white border border-slate-200 rounded-2xl text-slate-800 font-black text-[10px] uppercase tracking-widest shadow-sm hover:shadow-md hover:border-primary transition-all flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
            <?php echo __('print_batch_btn'); ?>
        </a>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass-card p-8 rounded-[40px] border-l-4 border-primary">
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-2"><?php echo __('total_redeemed'); ?></p>
            <h3 class="text-3xl font-black text-slate-800 tabular-nums leading-none"><?php echo number_format($stats['total_balance_issued']); ?> <span class="text-xs font-bold text-slate-400 italic">SYP</span></h3>
        </div>
        <div class="glass-card p-8 rounded-[40px] border-l-4 border-accent">
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-2"><?php echo __('active_card_value'); ?></p>
            <h3 class="text-3xl font-black text-slate-800 tabular-nums leading-none"><?php echo number_format($stats['active_cards_value']); ?> <span class="text-xs font-bold text-slate-400 italic">SYP</span></h3>
        </div>
        <div class="glass-card p-8 rounded-[40px] border-l-4 border-slate-800">
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-2"><?php echo __('total_injections'); ?></p>
            <h3 class="text-3xl font-black text-slate-800 tabular-nums leading-none"><?php echo number_format($stats['total_transactions']); ?> <span class="text-xs font-bold text-slate-400 italic uppercase"><?php echo __('redemptions'); ?></span></h3>
        </div>
    </div>

    <!-- Cards Table -->
    <div class="glass-card rounded-[48px] overflow-hidden shadow-sm">
        <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
            <div>
                <h4 class="font-black text-slate-800 uppercase text-sm tracking-tighter"><?php echo __('inventory_audit'); ?></h4>
                <p class="text-[10px] font-bold text-slate-400 mt-0.5"><?php echo __('latest_50_tokens'); ?></p>
            </div>
            <div class="flex items-center space-x-2">
                <button x-show="selectedCards.length > 0" 
                        @click="window.open('print_cards.php?ids=' + selectedCards.join(','), '_blank')"
                        class="text-[10px] text-white hover:bg-primary/90 transition uppercase font-black tracking-widest bg-primary px-4 py-2 rounded-xl shadow-sm flex items-center shadow-premium" x-cloak>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    <?php echo str_replace('{count}', '<span x-text="selectedCards.length" class="mx-1"></span>', __('print_selected')); ?>
                </button>
                <button class="text-[10px] text-slate-400 hover:text-primary transition uppercase font-black tracking-widest bg-white px-4 py-2 border border-slate-100 rounded-xl shadow-sm"><?php echo __('download_pdf'); ?></button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-black uppercase text-slate-400 bg-white/50">
                        <th class="px-8 py-4 w-12 text-center">
                            <input type="checkbox" 
                                   @click="selectedCards = $event.target.checked ? allCardIds : []"
                                   :checked="selectedCards.length === allCardIds.length && allCardIds.length > 0"
                                   class="w-4 h-4 text-primary bg-slate-100 border-slate-300 rounded focus:ring-primary focus:ring-2 cursor-pointer transition-all">
                        </th>
                        <th class="px-8 py-4"><?php echo __('card_serial_code'); ?></th>
                        <th class="px-8 py-4"><?php echo __('load_balance'); ?></th>
                        <th class="px-8 py-4"><?php echo __('expiration'); ?></th>
                        <th class="px-8 py-4"><?php echo __('status'); ?></th>
                        <th class="px-8 py-4 text-right"><?php echo __('verification_qr'); ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($cards as $c): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-8 py-5 text-center">
                            <input type="checkbox" value="<?php echo $c['id']; ?>" x-model="selectedCards"
                                   class="w-4 h-4 text-primary bg-slate-100 border-slate-300 rounded focus:ring-primary focus:ring-2 cursor-pointer transition-all">
                        </td>
                        <td class="px-8 py-5">
                            <span class="font-black text-xs text-primary tabular-nums tracking-widest bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200"><?php echo $c['code']; ?></span>
                        </td>
                        <td class="px-8 py-5 font-black text-sm text-slate-800 tabular-nums"><?php echo number_format($c['balance']); ?> <span class="text-[10px] font-bold text-slate-300">SYP</span></td>
                        <td class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest"><?php echo $c['expiry_date'] ?: __('no_expiry'); ?></td>
                        <td class="px-8 py-5">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter <?php 
                                echo $c['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-400';
                            ?>">
                                <?php echo $c['status']; ?>
                            </span>
                        </td>
                        <td class="px-8 py-5 text-right flex items-center justify-end space-x-2">
                            <a href="print_cards.php?id=<?php echo $c['id']; ?>" target="_blank" class="p-2.5 bg-white border border-slate-100 rounded-xl text-slate-400 group-hover:text-primary shadow-sm group-hover:shadow-md transition-all active:scale-90">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                            </a>
                            <form method="POST" action="api/wallet_action.php" onsubmit="return confirm('Delete this card permanently?');">
                                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="p-2.5 bg-white border border-slate-100 rounded-xl text-slate-400 hover:text-red-500 shadow-sm hover:shadow-md transition-all active:scale-90">
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

    <!-- Generate Modal (Alpine.js) -->
    <div x-show="showGenerate" 
         class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-md p-6" x-cloak>
        <div class="glass-card max-w-lg w-full p-10 rounded-[48px] shadow-2xl border-white/50 bg-white/95" @click.away="showGenerate = false">
            <h3 class="text-3xl font-black italic mb-2 text-slate-800 uppercase tracking-tighter"><?php echo __('mass_forge'); ?></h3>
            <p class="text-slate-400 text-sm mb-10 leading-relaxed"><?php echo __('generate_bulk_desc'); ?></p>
            
            <form method="POST" action="api/generate_cards.php" class="space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 tracking-widest"><?php echo __('card_quantity'); ?></label>
                        <input type="number" name="count" value="10" min="1" max="500" class="w-full bg-slate-100 border border-slate-200 rounded-2xl px-5 py-4 text-slate-800 focus:outline-none focus:border-primary transition font-black text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 tracking-widest"><?php echo __('face_value'); ?></label>
                        <input type="number" name="balance" value="5000" step="500" class="w-full bg-slate-100 border border-slate-200 rounded-2xl px-5 py-4 text-primary focus:outline-none focus:border-primary transition font-black text-lg">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 tracking-widest"><?php echo __('expiry_date_optional'); ?></label>
                    <input type="date" name="expiry" class="w-full bg-slate-100 border border-slate-200 rounded-2xl px-5 py-4 text-slate-800 focus:outline-none focus:border-primary transition font-bold text-xs uppercase">
                </div>
                <div class="flex space-x-4 pt-6">
                    <button type="button" @click="showGenerate = false" class="flex-1 py-4 text-[10px] font-black uppercase text-slate-400 hover:text-slate-800 transition tracking-widest"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="flex-[2] py-4 bg-primary text-white rounded-2xl font-black uppercase shadow-premium text-[11px] tracking-widest hover:scale-105 transition active:scale-95"><?php echo __('execute_forging'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
