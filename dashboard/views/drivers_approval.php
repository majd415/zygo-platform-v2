<?php
// C:\xampp\htdocs\dashboardtaxi\views\drivers_approval.php
$statusFilter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$drivers = $driverModel->getAllDrivers($statusFilter, $search);

// Group documents for easy access in Alpine
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

<div class="space-y-8" 
     x-data="{ 
        showDetails: false, 
        selectedDriver: null, 
        showZoom: false, 
        zoomImg: '', 
        zoomList: [],
        zoomIndex: 0,
        docKeys: <?php echo htmlspecialchars(json_encode(array_values($docFields))); ?>,
        preview: function(url) { 
            // Prepare the list of all available document URLs
            this.zoomList = this.docKeys
                .map(key => this.selectedDriver[key])
                .filter(path => path)
                .map(path => doc_url(path));
            
            // Set current index and show modal
            this.zoomIndex = this.zoomList.indexOf(url);
            if (this.zoomIndex === -1) {
                this.zoomList.push(url);
                this.zoomIndex = this.zoomList.length - 1;
            }
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
     @keydown.escape.window="showDetails = false; showZoom = false"
     @keydown.right.window="if(showZoom) nextZoom()"
     @keydown.left.window="if(showZoom) prevZoom()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-black text-slate-800 italic tracking-tighter uppercase"><?php echo __('fleet_command'); ?></h2>
            <p class="text-slate-400 mt-1 uppercase text-[10px] font-black tracking-widest leading-none"><?php echo __('fleet_desc'); ?></p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center gap-4">
            <!-- Search Field -->
            <form method="GET" class="relative group">
                <input type="hidden" name="p" value="drivers">
                <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" 
                       placeholder="<?php echo __('search_drivers_placeholder'); ?>"
                       class="bg-white border border-slate-200 rounded-2xl pl-12 pr-6 py-3.5 text-sm focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all w-full sm:w-80 shadow-premium">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </form>

            <div class="flex bg-slate-100 p-1 rounded-2xl shadow-inner">
                <a href="?p=drivers" class="px-6 py-2.5 <?php echo !isset($_GET['status']) ? 'bg-white text-primary shadow-sm font-black' : 'text-slate-400 hover:text-slate-600'; ?> rounded-xl text-[10px] transition-all uppercase tracking-widest"><?php echo __('all_fleet'); ?></a>
                <a href="?p=drivers&status=pending" class="px-6 py-2.5 <?php echo ($_GET['status'] ?? '') === 'pending' ? 'bg-white text-primary shadow-sm font-black' : 'text-slate-400 hover:text-slate-600'; ?> rounded-xl text-[10px] transition-all uppercase tracking-widest"><?php echo __('pending_approval'); ?></a>
                <a href="?p=drivers&status=approved" class="px-6 py-2.5 <?php echo ($_GET['status'] ?? '') === 'approved' ? 'bg-white text-primary shadow-sm font-black' : 'text-slate-400 hover:text-slate-600'; ?> rounded-xl text-[10px] transition-all uppercase tracking-widest"><?php echo __('active_fleet'); ?></a>
            </div>
        </div>
    </div>

    <!-- Drivers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        <?php foreach ($drivers as $d): ?>
        <div class="glass-card group relative p-8 rounded-[48px] bg-white border border-slate-100 hover:border-primary/20 hover:shadow-premium transition-all duration-500 overflow-hidden">
            <!-- Navigation Link Overlay -->
            <a href="#" @click.prevent="selectedDriver = <?php echo htmlspecialchars(json_encode($d), ENT_QUOTES, 'UTF-8'); ?>; showDetails = true;" class="absolute inset-0 z-10"></a>

            <div class="relative z-20">
                <div class="flex items-start justify-between mb-8">
                    <div class="relative">
                        <div class="w-20 h-20 rounded-[28px] bg-slate-50 p-0.5 border border-slate-100 overflow-hidden shadow-inner group-hover:scale-105 transition-transform duration-500">
                            <?php if ($d['avatar']): ?>
                                <img src="<?php echo get_avatar_url($d['avatar']); ?>" class="w-full h-full object-cover rounded-[26px]">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-primary text-white text-2xl font-black italic rounded-[26px]">
                                    <?php echo substr($d['name'], 0, 1); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-2xl border-4 border-white <?php echo ($d['is_online'] || in_array($d['status'], ['active', 'approved'])) ? 'bg-green-500' : 'bg-slate-300'; ?> shadow-sm transition-colors duration-500"></div>
                    </div>
                    <div class="flex flex-col items-end space-y-2">
                        <div class="px-3 py-1 rounded-full <?php echo in_array($d['status'], ['active', 'approved']) ? 'bg-green-100 text-green-500' : 'bg-red-100 text-red-500'; ?> text-[8px] font-black uppercase tracking-widest border border-current">
                            <?php echo __($d['status']); ?>
                        </div>
                        <div class="text-[9px] font-black text-slate-300 uppercase tracking-tighter">#CPT-<?php echo str_pad($d['id'], 4, '0', STR_PAD_LEFT); ?></div>
                    </div>
                </div>

                <div class="mb-8">
                    <h4 class="text-xl font-black text-slate-800 uppercase tracking-tighter group-hover:text-primary transition-colors leading-tight mb-1"><?php echo htmlspecialchars($d['name']); ?></h4>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest"><?php echo htmlspecialchars($d['phone']); ?></p>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-8 pt-6 border-t border-slate-50">
                    <div>
                        <p class="text-[8px] font-black text-slate-300 uppercase tracking-[0.2em] mb-1"><?php echo __('rating'); ?></p>
                        <p class="text-sm font-black text-slate-700 italic"><?php echo number_format($d['rating'] ?? 5.0, 1); ?> <span class="text-yellow-400 text-[10px]">★</span></p>
                    </div>
                    <div>
                        <p class="text-[8px] font-black text-slate-300 uppercase tracking-[0.2em] mb-1"><?php echo __('balance'); ?></p>
                        <p class="text-sm font-black text-primary italic"><?php echo number_format($d['wallet_balance'] ?? 0); ?></p>
                    </div>
                </div>

            </div>



            <!-- Stats Bar -->
            <div class="grid grid-cols-2 gap-4 pt-6 border-t border-slate-50 mt-auto">
                <div class="text-center">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">RATING</p>
                    <div class="flex items-center justify-center space-x-1">
                        <span class="text-sm font-black text-slate-800"><?php echo number_format($d['rating'], 1); ?></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-yellow-400" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.363 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                    </div>
                </div>
                <div class="text-center border-l border-slate-50">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1"><?php echo __('status'); ?></p>
                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-tighter <?php echo in_array($d['status'], ['active', 'approved']) ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-400'; ?>">
                        <?php echo __($d['status']); ?>
                    </span>
                </div>
                <!-- Remove Driver -->
            <div class="pt-4 border-t border-slate-50 mt-4 flex justify-end relative z-30" @click.stop>
                <form method="POST" action="api/driver_action.php" @click.stop onsubmit="return confirm('<?php echo __('confirm_delete_driver'); ?>')">
                    <input type="hidden" name="user_id" value="<?php echo $d['id']; ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" @click.stop class="p-2 rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all active:scale-90" title="<?php echo __('remove_driver'); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Details Modal (Alpine.js) -->
    <div x-show="showDetails" 
         class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-md p-6" 
         x-cloak
         @keydown.escape.window="showDetails = false">
        <div class="glass-card max-w-5xl w-full max-h-[90vh] overflow-y-auto rounded-[60px] bg-white shadow-2xl p-12 border-white/50 relative" 
             @click.away="showDetails = false">
            
            <button @click="showDetails = false" class="absolute top-12 right-12 text-slate-400 hover:text-slate-800 transition active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>            <template x-if="selectedDriver">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16" x-data="{ docStatus: 'pending', reason: '' }" x-init="docStatus = selectedDriver.doc_status || 'pending'; reason = selectedDriver.rejection_reason || ''">
                    <!-- Column 1: Profile & Dossier -->
                    <div class="space-y-12">
                        <div class="flex items-center space-x-8">
                            <div class="w-32 h-32 rounded-[40px] bg-slate-100 border border-slate-200 overflow-hidden shadow-premium">
                                <template x-if="selectedDriver.avatar">
                                    <img :src="asset_url(selectedDriver.avatar)" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!selectedDriver.avatar">
                                    <div class="w-full h-full flex items-center justify-center bg-primary text-white text-5xl font-black italic" x-text="selectedDriver.name.charAt(0)"></div>
                                </template>
                            </div>
                            <div>
                                <h3 class="text-4xl font-black text-slate-800 italic uppercase tracking-tighter" x-text="selectedDriver.name"></h3>
                                <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-2" x-text="selectedDriver.phone"></p>
                                <p class="text-xs font-bold text-slate-400 mt-1" x-text="selectedDriver.email"></p>
                            </div>
                        </div>

                        <!-- Full Record Stats -->
                        <div class="grid grid-cols-3 gap-6">
                            <div class="p-6 bg-slate-50 rounded-[32px] border border-slate-100 flex flex-col items-center">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1"><?php echo __('lifetime_trips'); ?></p>
                                <p class="text-2xl font-black text-slate-800 italic" x-text="selectedDriver.total_trips || '0'"></p>
                            </div>
                            <div class="p-6 bg-slate-50 rounded-[32px] border border-slate-100 flex flex-col items-center text-center">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1"><?php echo __('reliability'); ?></p>
                                <p class="text-2xl font-black text-green-600 italic">99%</p>
                            </div>
                            <div class="p-6 bg-slate-50 rounded-[32px] border border-slate-100 flex flex-col items-center">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1"><?php echo __('joined'); ?></p>
                                <p class="text-xs font-black text-slate-800 italic" x-text="new Date(selectedDriver.created_at).toLocaleDateString()"></p>
                            </div>
                        </div>

                        <!-- Comprehensive Actions Control -->
                        <div class="space-y-6">
                            <h5 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400"><?php echo __('authority_controls'); ?></h5>
                            
                            <!-- Document Status (Control Only) -->
                            <div class="p-10 bg-slate-900 rounded-[48px] shadow-2xl text-white relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-primary/10 rounded-full blur-3xl -mr-16 -mt-16"></div>
                                
                                <form method="POST" action="api/driver_action.php" class="relative z-10 space-y-8">
                                    <input type="hidden" name="user_id" :value="selectedDriver.id">
                                    <input type="hidden" name="doc_id" :value="selectedDriver.doc_id">
                                    <input type="hidden" name="action" value="update_doc_status">
                                    <input type="hidden" name="redirect_to" value="../index.php?p=drivers">

                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-black italic tracking-tight"><?php echo __('document_status'); ?></p>
                                            <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest"><?php echo __('review_paperwork_desc'); ?></p>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <select name="status" x-model="docStatus" class="bg-white/5 text-white text-[10px] font-black uppercase tracking-widest px-8 py-4 rounded-2xl border border-white/10 outline-none focus:border-primary transition-all appearance-none cursor-pointer">
                                                <option value="pending" class="bg-slate-900 italic">PENDING</option>
                                                <option value="approved" class="bg-slate-900 italic">APPROVED</option>
                                                <option value="rejected" class="bg-slate-900 italic">REJECTED</option>
                                            </select>
                                            <button type="submit" class="bg-primary text-white p-4 rounded-2xl hover:scale-105 transition active:scale-95 shadow-premium">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div x-show="docStatus === 'rejected' || docStatus === 'pending'" x-transition class="space-y-4">
                                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest"><?php echo __('rejection_reason'); ?></p>
                                        <textarea name="rejection_reason" x-model="reason" placeholder="<?php echo __('enter_reason_placeholder'); ?>"
                                                  class="w-full bg-white/5 text-white text-xs p-6 rounded-[32px] border border-white/10 outline-none focus:border-primary h-32 placeholder:text-slate-700 transition-all"></textarea>
                                    </div>
                                </form>
                            </div>

                            <!-- Vehicle Information Card -->
                            <div class="p-10 bg-white rounded-[48px] border border-slate-100 shadow-premium">
                                <div class="flex items-center space-x-4 mb-8">
                                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" /></svg>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-black italic text-slate-800 uppercase tracking-tighter"><?php echo __('vehicle_info'); ?></h6>
                                        <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest"><?php echo __('physical_asset_details'); ?></p>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="space-y-1">
                                        <p class="text-[8px] font-black text-slate-300 uppercase tracking-[0.2em]"><?php echo __('car_type'); ?></p>
                                        <p class="text-xs font-black text-slate-800 uppercase italic" x-text="(selectedDriver.car_type || 'normal').toUpperCase()"></p>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-[8px] font-black text-slate-300 uppercase tracking-[0.2em]"><?php echo __('car_model'); ?></p>
                                        <p class="text-xs font-black text-slate-800 uppercase italic" x-text="selectedDriver.car_model || '---'"></p>
                                    </div>
                                    <div class="space-y-1 border-t border-slate-50 pt-4">
                                        <p class="text-[8px] font-black text-slate-300 uppercase tracking-[0.2em]"><?php echo __('car_year'); ?></p>
                                        <p class="text-xs font-black text-slate-800 uppercase italic" x-text="selectedDriver.car_year || '---'"></p>
                                    </div>
                                    <div class="space-y-1 border-t border-slate-50 pt-4">
                                        <p class="text-[8px] font-black text-slate-300 uppercase tracking-[0.2em]"><?php echo __('car_color'); ?></p>
                                        <p class="text-xs font-black text-slate-800 uppercase italic" x-text="selectedDriver.car_color || '---'"></p>
                                    </div>
                                    <div class="col-span-2 space-y-1 border-t border-slate-50 pt-4 text-center">
                                        <p class="text-[8px] font-black text-slate-300 uppercase tracking-[0.2em]"><?php echo __('car_plate'); ?></p>
                                        <p class="text-xl font-black text-slate-900 tracking-widest italic" x-text="selectedDriver.car_plate || '---'"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Identity Verification - VIEW ONLY -->
                            <div class="p-8 bg-white/5 rounded-[40px] border border-slate-100 flex items-center justify-between">
                                <div>
                                    <p class="text-base font-black italic tracking-tight text-slate-800"><?php echo __('identity_verification'); ?></p>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest"><?php echo __('global_access_desc'); ?></p>
                                </div>
                                <div class="px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest border"
                                     :class="selectedDriver.is_verified == 1 ? 'bg-green-50 text-green-500 border-green-200' : 'bg-slate-50 text-slate-400 border-slate-100'">
                                    <span x-text="selectedDriver.is_verified == 1 ? '<?php echo __('verified'); ?>' : '<?php echo __('pending'); ?>'"></span>
                                </div>
                            </div>

                            <!-- Driver Activity - VIEW ONLY -->
                            <div class="p-8 bg-white/5 rounded-[40px] border border-slate-100 flex items-center justify-between">
                                <div>
                                    <p class="text-base font-black italic tracking-tight text-slate-800"><?php echo __('driver_activity'); ?></p>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest"><?php echo __('realtime_participation_desc'); ?></p>
                                </div>
                                <div class="px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest border"
                                     :class="selectedDriver.status === 'active' ? 'bg-primary/10 text-primary border-primary/20' : 'bg-slate-50 text-slate-400 border-slate-100'">
                                    <span x-text="selectedDriver.status.toUpperCase()"></span>
                                </div>
                            </div>

                            <!-- Manual Notification Panel -->
                            <div class="p-10 bg-slate-50 rounded-[48px] border border-slate-200 mt-12">
                                <div class="flex items-center space-x-4 mb-8">
                                    <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-black italic text-slate-800"><?php echo __('send_direct_message'); ?></h6>
                                        <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Targets FCM Token: <span class="text-slate-600" x-text="selectedDriver.fcm_token ? 'ACTIVE' : 'MISSING'"></span></p>
                                    </div>
                                </div>
                                <form method="POST" action="api/driver_action.php" class="space-y-4">
                                    <input type="hidden" name="user_id" :value="selectedDriver.id">
                                    <input type="hidden" name="action" value="send_notification">
                                    <input type="hidden" name="redirect_to" value="../index.php?p=drivers">
                                    
                                    <input type="text" name="title_ar" placeholder="<?php echo __('notification_title_ar'); ?>" 
                                           class="w-full bg-white border border-slate-200 rounded-2xl p-4 text-xs font-black outline-none focus:border-primary transition-all shadow-sm">
                                    <textarea name="message_ar" placeholder="<?php echo __('notification_message_ar'); ?>" 
                                              class="w-full bg-white border border-slate-200 rounded-2xl p-4 text-xs font-black outline-none focus:border-primary transition-all h-24 shadow-sm"></textarea>
                                    
                                    <button type="submit" class="w-full py-4 rounded-2xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-primary transition-all active:scale-95 shadow-premium flex items-center justify-center space-x-2">
                                        <span><?php echo __('dispatch_push_command'); ?></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Document Archive -->
                    <div>
                        <h5 class="text-[10px] font-black uppercase tracking-[0.2em] mb-8 text-slate-400"><?php echo __('documentary_evidence'); ?></h5>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <?php foreach ($docFields as $label => $field): ?>
                            <template x-if="selectedDriver && selectedDriver.<?php echo $field; ?>">
                                <div class="glass-card p-4 rounded-3xl border border-slate-100 group">
                                    <div class="aspect-video bg-slate-900 rounded-2xl overflow-hidden cursor-zoom-in relative mb-3" @click="preview(doc_url(selectedDriver.<?php echo $field; ?>))">
                                        <img :src="doc_url(selectedDriver.<?php echo $field; ?>)" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" /></svg>
                                        </div>
                                    </div>
                                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest text-center"><?php echo $label; ?></p>
                                </div>
                            </template>
                            <?php endforeach; ?>
                        </div>

                        <template x-if="!selectedDriver.doc_id">
                            <div class="p-12 text-center bg-slate-50 rounded-[48px] border-2 border-dashed border-slate-200">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest"><?php echo __('no_docs_msg'); ?></p>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Global Zoom -->
    <div x-show="showZoom" class="fixed inset-0 z-[150] flex items-center justify-center bg-slate-900/95 backdrop-blur-xl p-12" x-cloak>
        <!-- Close Button -->
        <button @click="showZoom = false" class="absolute top-12 right-12 text-white hover:text-primary transition active:scale-90 z-20">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>

        <!-- Navigation: Previous -->
        <button @click="prevZoom()" x-show="zoomList.length > 1" class="absolute left-8 top-1/2 -translate-y-1/2 p-4 rounded-full bg-white/5 hover:bg-white/10 text-white transition active:scale-90 z-20 group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        </button>

        <!-- Image Container -->
        <div class="relative max-w-full max-h-full flex flex-col items-center justify-center">
            <img :src="zoomList[zoomIndex]" class="max-w-full max-h-[85vh] rounded-[40px] shadow-2xl object-contain border-8 border-white/5 mx-auto">
            
            <!-- Counter -->
            <div x-show="zoomList.length > 1" class="mt-8 px-6 py-2 rounded-full bg-white/10 text-white/60 text-[10px] font-black uppercase tracking-[0.3em] backdrop-blur-sm border border-white/5">
                IMAGE <span class="text-white" x-text="zoomIndex + 1"></span> OF <span class="text-white" x-text="zoomList.length"></span>
            </div>
        </div>

        <!-- Navigation: Next -->
        <button @click="nextZoom()" x-show="zoomList.length > 1" class="absolute right-8 top-1/2 -translate-y-1/2 p-4 rounded-full bg-white/5 hover:bg-white/10 text-white transition active:scale-90 z-20 group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </button>
    </div>
</div>

<script>
    function asset_url(path) {
        if (!path) return '';
        if (path.startsWith('http')) return path;
        const baseUrl = '<?php echo ASSET_URL; ?>';
        // If it's an avatar or doesn't have a path, assume it's in public/uploads/avatars/
        if (path.includes('uploads/avatars/')) return baseUrl + path;
        if (!path.includes('/') && !path.includes('_docs/')) return baseUrl + 'uploads/avatars/' + path;
        return baseUrl + path;
    }

    function doc_url(path) {
        if (!path) return '';
        if (path.startsWith('http')) return path;
        const baseUrl = '<?php echo ASSET_URL; ?>';
        if (path.startsWith('driver_docs/')) return baseUrl + '../storage/app/public/' + path;
        if (!path.includes('/')) return baseUrl + '../storage/app/public/driver_docs/' + path;
        return baseUrl + path;
    }
</script>
