<?php
// C:\xampp\htdocs\dashboardtaxi\views\driver_detail.php
// Expected variables from index.php: $driver, $docs, $data['stats'] (or I'll fetch it here if not available)
$stats = $data['stats'] ?? ['total_trips' => 0, 'total_earnings' => 0, 'reliability' => '100%'];
$rejectionReason = $driver['rejection_reason'] ?? '';

$docFields = [
    __('national_id_front') => 'national_id_front',
    __('national_id_back') => 'national_id_back',
    __('car_photo') => 'car_photo',
    __('car_front') => 'car_photo_front',
    __('car_back') => 'car_photo_back',
    __('driving_license') => 'driving_license',
    __('license_back') => 'license_back',
    __('registration_front') => 'registration_front',
    __('registration_back') => 'registration_back',
    __('insurance') => 'insurance_photo'
];
?>

<div class="space-y-12" 
     x-data="{ 
        showZoom: false, 
        zoomImg: '', 
        zoomList: [],
        zoomIndex: 0,
        status: '<?php echo $docs['status'] ?? 'pending'; ?>',
        rejectionReason: '<?php echo htmlspecialchars($rejectionReason); ?>',
        preview(url) { 
            // static list for this driver
            this.zoomList = [
                <?php foreach ($docFields as $f): if (!empty($docs[$f])): ?>'<?php echo get_doc_url($docs[$f]); ?>',<?php endif; endforeach; ?>
            ];
            this.zoomIndex = this.zoomList.indexOf(url);
            this.showZoom = true; 
        },
        nextZoom: function() {
            if (this.zoomList.length > 0) {
                this.zoomIndex = (this.zoomIndex + 1) % this.zoomList.length;
            }
        },
        prevZoom: function() {
            if (this.zoomList.length > 0) {
                this.zoomIndex = (this.zoomIndex - 1 + this.zoomList.length) % this.zoomList.length;
            }
        }
     }"
     @keydown.escape.window="showZoom = false"
     @keydown.right.window="if(showZoom) nextZoom()"
     @keydown.left.window="if(showZoom) prevZoom()">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2">
            <a href="?p=drivers" class="inline-flex items-center text-[10px] font-black text-slate-400 hover:text-primary transition uppercase tracking-widest group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" /></svg>
                <?php echo __('back_to_fleet'); ?>
            </a>
            <h2 class="text-4xl font-black text-slate-800 italic uppercase tracking-tighter"><?php echo __('captain_dossier'); ?>: <span class="text-primary"><?php echo htmlspecialchars($driver['name']); ?></span></h2>
        </div>
        
        <div class="flex items-center gap-4">
            <span class="px-6 py-2.5 rounded-2xl bg-slate-100 text-slate-400 font-black text-[10px] uppercase tracking-widest border border-slate-200">ID: #<?php echo str_pad($driver['id'], 5, '0', STR_PAD_LEFT); ?></span>
            <div class="px-6 py-2.5 rounded-2xl <?php echo $driver['status'] === 'active' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'; ?> font-black text-[10px] uppercase tracking-widest border border-current opacity-80">
                <?php echo __($driver['status']); ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-12">
        <!-- Left Column: Primary Stats & Authority -->
        <div class="space-y-8">
            <div class="glass-card p-10 rounded-[60px] bg-white border border-slate-100 shadow-premium relative overflow-hidden">
                <div class="relative z-10 flex flex-col items-center">
                    <div class="relative group">
                        <div class="w-40 h-40 rounded-[48px] bg-slate-50 border-4 border-white shadow-2xl p-1 mb-8 overflow-hidden">
                            <img src="<?php echo get_avatar_url($driver['avatar']); ?>" 
                                 class="w-full h-full object-cover rounded-[44px]"
                                 onerror="this.src='../assets/images/default_avatar.png'">
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full border-4 border-white <?php echo $driver['is_online'] ? 'bg-green-500' : 'bg-slate-300'; ?> shadow-lg"></div>
                    </div>
                    
                    <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tighter mb-1"><?php echo htmlspecialchars($driver['name']); ?></h3>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6"><?php echo htmlspecialchars($driver['phone']); ?></p>
                    
                    <div class="grid grid-cols-3 gap-4 w-full pt-8 border-t border-slate-50">
                        <div class="text-center">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1"><?php echo __('trips'); ?></p>
                            <p class="text-lg font-black text-slate-800 italic"><?php echo number_format($stats['total_trips']); ?></p>
                        </div>
                        <div class="text-center border-x border-slate-50">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1"><?php echo __('rating'); ?></p>
                            <p class="text-lg font-black text-slate-800 italic"><?php echo number_format($driver['rating'] ?? 5.0, 1); ?> <span class="text-yellow-400 text-xs">★</span></p>
                        </div>
                        <div class="text-center">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1"><?php echo __('wallet'); ?></p>
                            <p class="text-lg font-black text-primary italic"><?php echo number_format($driver['wallet_balance'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Authority Controls Card -->
            <div class="glass-card p-10 rounded-[60px] bg-slate-900 text-white shadow-2xl relative">
                <h5 class="text-[10px] font-black uppercase tracking-[0.2em] mb-10 text-slate-500 italic"><?php echo __('authority_controls'); ?></h5>
                
                <div class="space-y-6">
                    <!-- Document Status -->
                    <div class="p-6 bg-white/5 rounded-[32px] border border-white/10 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-black italic tracking-tight"><?php echo __('document_status'); ?></p>
                                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest"><?php echo __('review_paperwork_desc'); ?></p>
                            </div>
                            <div class="px-4 py-2 rounded-xl bg-primary/20 border border-primary/30 text-primary text-[10px] font-black uppercase tracking-widest">
                                <?php echo __($docs['status'] ?? 'pending'); ?>
                            </div>
                        </div>
                        
                        <form method="POST" action="api/driver_action.php" class="space-y-4 pt-4 border-t border-white/5">
                            <input type="hidden" name="user_id" value="<?php echo $driver['id']; ?>">
                            <input type="hidden" name="action" value="update_doc_status">
                            <input type="hidden" name="doc_id" value="<?php echo $docs['id'] ?? 0; ?>">
                            <input type="hidden" name="redirect_to" value="../index.php?p=driver_detail&id=<?php echo $driver['id']; ?>">
                            <form method="POST" action="api/driver_action.php" class="flex space-x-2">
                                <input type="hidden" name="user_id" value="<?php echo $driver['id']; ?>">
                                <input type="hidden" name="doc_id" value="<?php echo $docs['id'] ?? 0; ?>">
                                <input type="hidden" name="action" value="update_doc_status">
                                <input type="hidden" name="redirect_to" value="../index.php?p=driver_detail&id=<?php echo $driver['id']; ?>">
                                
                                <select name="status" x-model="status" class="bg-slate-800 text-white text-[10px] font-black uppercase tracking-widest px-4 py-3 rounded-xl border border-white/10 outline-none cursor-pointer">
                                    <option value="pending"><?php echo __('pending'); ?></option>
                                    <option value="approved"><?php echo __('approved'); ?></option>
                                    <option value="rejected"><?php echo __('rejected'); ?></option>
                                </select>
                                <button type="submit" class="bg-primary text-white p-3 rounded-xl hover:scale-105 transition active:scale-95 shadow-premium">
                                    <?php echo __('update'); ?>
                                </button>
                            </form>
                        </div>

                        <div x-show="status === 'rejected' || status === 'pending'" x-transition class="space-y-2">
                            <p class="text-[9px] font-black text-red-400 uppercase tracking-widest"><?php echo __('rejection_reason'); ?></p>
                            <textarea name="rejection_reason" placeholder="<?php echo __('enter_reason_placeholder'); ?>"
                                      class="w-full bg-slate-800 text-white text-xs p-4 rounded-2xl border border-white/10 outline-none focus:border-red-400 h-24 placeholder:text-slate-600"><?php echo htmlspecialchars($driver['rejection_reason'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Identity Verification - VIEW ONLY -->
                    <div class="p-6 bg-white/5 rounded-3xl border border-white/10 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-black italic tracking-tight text-white"><?php echo __('identity_verification'); ?></p>
                            <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest"><?php echo __('global_access_desc'); ?></p>
                        </div>
                        <span class="px-6 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest <?php echo $driver['is_verified'] ? 'bg-green-500/20 text-green-400' : 'bg-slate-500/20 text-slate-400'; ?>">
                            <?php echo $driver['is_verified'] ? __('verified') : __('pending'); ?>
                        </span>
                    </div>

                    <!-- Driver Activity - VIEW ONLY -->
                    <div class="p-6 bg-white/5 rounded-3xl border border-white/10 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-black italic tracking-tight text-white"><?php echo __('driver_activity'); ?></p>
                            <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest"><?php echo __('realtime_participation_desc'); ?></p>
                        </div>
                        <span class="px-6 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest <?php echo $driver['status'] === 'active' ? 'bg-primary/20 text-primary' : 'bg-slate-500/20 text-slate-400'; ?>">
                            <?php echo strtoupper($driver['status']); ?>
                        </span>
                    </div>
                </div>

                <!-- Danger Zone: Remove Captain -->
                <div class="mt-10 pt-8 border-t border-white/10">
                    <p class="text-[9px] font-black text-red-500 uppercase tracking-widest mb-4 italic">DANGER ZONE</p>
                    <form method="POST" action="api/driver_action.php" onsubmit="return confirm('<?php echo __('confirm_delete_driver'); ?>')">
                        <input type="hidden" name="user_id" value="<?php echo $driver['id']; ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="w-full py-4 rounded-[26px] bg-red-500/10 text-red-500 border border-red-500/20 text-[10px] font-black uppercase tracking-widest hover:bg-red-500 hover:text-white transition-all active:scale-95 flex items-center justify-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            <span><?php echo __('remove_driver'); ?></span>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Communication Panel -->
            <div class="glass-card p-10 rounded-[60px] bg-white border border-slate-100 shadow-premium">
                <div class="flex items-center space-x-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                    </div>
                    <div>
                        <h4 class="text-xl font-black text-slate-800 italic uppercase tracking-tighter"><?php echo __('send_direct_message'); ?></h4>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Targets FCM Token: <span class="text-slate-600"><?php echo !empty($driver['fcm_token']) ? 'ACTIVE' : 'MISSING'; ?></span></p>
                    </div>
                </div>
                <form method="POST" action="api/driver_action.php" class="space-y-6">
                    <input type="hidden" name="user_id" value="<?php echo $driver['id']; ?>">
                    <input type="hidden" name="action" value="send_notification">
                    <input type="hidden" name="redirect_to" value="../index.php?p=driver_detail&id=<?php echo $driver['id']; ?>">
                    
                    <div class="space-y-2">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest"><?php echo __('notification_title_ar'); ?></p>
                        <input type="text" name="title_ar" placeholder="عنوان التنبيه" dir="rtl" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-3.5 text-sm focus:outline-none focus:border-primary transition-all">
                    </div>
                    
                    <div class="space-y-2">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest"><?php echo __('notification_message_ar'); ?></p>
                        <textarea name="message_ar" placeholder="نص التنبيه..." dir="rtl" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-3.5 text-sm focus:outline-none focus:border-primary transition-all h-24"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-[26px] text-[10px] font-black uppercase tracking-[0.2em] hover:bg-primary hover:shadow-premium transition-all active:scale-95 flex items-center justify-center space-x-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                        <span><?php echo __('dispatch_push_command'); ?></span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Column: Document Archive -->
        <div class="xl:col-span-2 space-y-8">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest italic"><?php echo __('documentary_archive'); ?></h4>
                <div class="flex items-center space-x-3">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest"><?php echo __('verified_assets_active'); ?></span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <?php foreach ($docFields as $label => $field): ?>
                <?php if (!empty($docs[$field])): ?>
                    <div class="glass-card p-6 rounded-[48px] bg-white border border-slate-100 group hover:shadow-premium transition-all duration-500 relative overflow-hidden">
                        <div class="aspect-video bg-slate-900 rounded-[32px] overflow-hidden relative cursor-zoom-in mb-6 group-hover:scale-[1.02] transition-transform" @click="preview('<?php echo get_doc_url($docs[$field]); ?>')">
                            <img src="<?php echo get_doc_url($docs[$field]); ?>" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span class="bg-white/20 backdrop-blur-md px-8 py-3 rounded-full text-white text-[9px] font-black uppercase tracking-widest border border-white/30 italic">Verify Evidence</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between px-2">
                            <div>
                                <h6 class="text-[11px] font-black text-slate-800 uppercase tracking-tighter mb-0.5"><?php echo $label; ?></h6>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic"><?php echo __('digitized_entry'); ?></p>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-500 border border-green-100 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="glass-card p-12 rounded-[48px] bg-slate-50 border-2 border-dashed border-slate-200 flex flex-col items-center justify-center opacity-40">
                        <div class="w-16 h-16 rounded-3xl bg-white border border-slate-200 flex items-center justify-center text-slate-300 mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest text-center italic"><?php echo $label; ?><br>UNAVAILABLE DOCUMENTATION</p>
                    </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
            <!-- Vehicle Information Card -->
            <div class="glass-card p-10 rounded-[60px] bg-white border border-slate-100 shadow-premium">
                <div class="flex items-center space-x-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary border border-primary/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" /></svg>
                    </div>
                    <div>
                        <h4 class="text-xl font-black text-slate-800 italic uppercase tracking-tighter"><?php echo __('vehicle_info'); ?></h4>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest"><?php echo __('physical_asset_details'); ?></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-8">
                    <div class="space-y-1">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em]"><?php echo __('car_type'); ?></p>
                        <p class="text-sm font-black text-slate-800 uppercase italic"><?php echo strtoupper($docs['car_type'] ?? 'normal'); ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em]"><?php echo __('car_model'); ?></p>
                        <p class="text-sm font-black text-slate-800 uppercase italic"><?php echo htmlspecialchars($docs['car_model'] ?? '---'); ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em]"><?php echo __('car_year'); ?></p>
                        <p class="text-sm font-black text-slate-800 uppercase italic"><?php echo htmlspecialchars($docs['car_year'] ?? '---'); ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em]"><?php echo __('car_color'); ?></p>
                        <p class="text-sm font-black text-slate-800 uppercase italic"><?php echo htmlspecialchars($docs['car_color'] ?? '---'); ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em]"><?php echo __('car_plate'); ?></p>
                        <p class="text-base font-black text-slate-900 tracking-wider italic"><?php echo htmlspecialchars($docs['car_plate'] ?? '---'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Real-time Activity Logs (Optional enhancement) -->
            <div class="glass-card p-10 rounded-[60px] bg-white border border-slate-100 shadow-premium">
                <h5 class="text-[10px] font-black uppercase tracking-[0.2em] mb-8 text-slate-400 italic"><?php echo __('recent_log_entries'); ?></h5>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl">
                        <div class="flex items-center space-x-4">
                            <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                            <span class="text-[10px] font-bold text-slate-700 italic">SYSTEM_VERIFICATION_CHECK</span>
                        </div>
                        <span class="text-[9px] font-black text-slate-400"><?php echo date('Y-m-d H:i'); ?></span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl">
                        <div class="flex items-center space-x-4">
                            <div class="w-2 h-2 rounded-full bg-green-500"></div>
                            <span class="text-[10px] font-bold text-slate-700 italic">FLEET_ADMITTANCE_PASSED</span>
                        </div>
                        <span class="text-[9px] font-black text-slate-400"><?php echo date('Y-m-d H:i', strtotime('-1 day')); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Zoom Modal -->
    <div x-show="showZoom" class="fixed inset-0 z-[150] flex items-center justify-center bg-slate-900/95 backdrop-blur-3xl p-12 transition-all" x-cloak>
        <!-- Close Button -->
        <button @click="showZoom = false" class="absolute top-12 right-12 text-white/50 hover:text-white transition-all active:scale-90 p-4 z-20">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>

        <!-- Navigation: Previous -->
        <button @click="prevZoom()" x-show="zoomList.length > 1" class="absolute left-8 top-1/2 -translate-y-1/2 p-4 rounded-full bg-white/5 hover:bg-white/10 text-white transition active:scale-90 z-20 group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-10 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        </button>

        <!-- Image Container -->
        <div class="relative max-w-full max-h-full flex flex-col items-center justify-center">
            <img :src="zoomList[zoomIndex]" class="max-w-full max-h-[85vh] rounded-[60px] shadow-2xl object-contain border-8 border-white/5 shadow-white/5">
            
            <!-- Counter -->
            <div x-show="zoomList.length > 1" class="mt-8 px-8 py-3 rounded-full bg-white/10 text-white/60 text-[10px] font-black uppercase tracking-[0.4em] backdrop-blur-sm border border-white/5">
                RECORD <span class="text-white" x-text="zoomIndex + 1"></span> / <span class="text-white" x-text="zoomList.length"></span>
            </div>
        </div>

        <!-- Navigation: Next -->
        <button @click="nextZoom()" x-show="zoomList.length > 1" class="absolute right-8 top-1/2 -translate-y-1/2 p-4 rounded-full bg-white/5 hover:bg-white/10 text-white transition active:scale-90 z-20 group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-10 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </button>
    </div>
</div>
