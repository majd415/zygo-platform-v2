<?php
// C:\xampp\htdocs\dashboardtaxi\views\support.php
$settingsList = $model->all('settings');
$s = $settingsList[0] ?? [
    'whatsapp_phone' => '',
    'email_support' => '',
    'support_phone' => '',
    'id' => 1
];
?>

<form action="./api/support_action.php" method="POST" class="space-y-8 animate__animated animate__fadeIn">
    <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
    
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-black text-slate-800 italic tracking-tighter uppercase"><?php echo __('support'); ?></h2>
            <p class="text-slate-400 mt-1 uppercase text-[10px] font-black tracking-widest leading-none"><?php echo __('edit_support_info'); ?></p>
        </div>
        <div class="flex space-x-3">
            <button type="submit" class="bg-primary text-white px-8 py-3.5 rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-premium hover:scale-105 transition-all"><?php echo __('save_all_changes_btn'); ?></button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Support Channels -->
        <div class="glass-card p-10 rounded-[48px] space-y-8 relative overflow-hidden group">
            <!-- Decorative Background Element -->
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/5 rounded-full blur-3xl group-hover:bg-primary/10 transition-all duration-700"></div>

            <h3 class="text-xl font-black text-slate-800 flex items-center uppercase tracking-tighter relative z-10">
                <span class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mr-4 shadow-sm animate-pulse-slow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </span>
                <?php echo __('support'); ?>
            </h3>
            
            <div class="space-y-6 relative z-10">
                
                <!-- Phone Input -->
                <div class="p-6 rounded-3xl bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all group/item">
                    <div class="flex justify-between mb-5 items-center">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none flex items-center gap-2">
                            <span class="text-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </span>
                            <?php echo __('phone'); ?>
                        </label>
                    </div>
                    <div class="relative">
                        <input type="text" name="support_phone" dir="ltr" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-sm font-black text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all group-hover/item:border-indigo-200" value="<?php echo $s['support_phone'] ?? ''; ?>" placeholder="e.g. +96311... or 011...">
                        <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-hover/item:text-indigo-500 transition-colors">
                            <i class="fas fa-pen"></i>
                        </div>
                    </div>
                </div>
                
                <!-- WhatsApp Input -->
                <div class="p-6 rounded-3xl bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all group/item">
                    <div class="flex justify-between mb-5 items-center">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none flex items-center gap-2">
                            <span class="text-green-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                  <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                                </svg>
                            </span>
                            <?php echo __('whatsapp_phone'); ?>
                        </label>
                    </div>
                    <div class="relative">
                        <input type="text" name="whatsapp_phone" dir="ltr" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-sm font-black text-slate-800 focus:outline-none focus:border-green-500 focus:ring-4 focus:ring-green-500/10 transition-all group-hover/item:border-green-200" value="<?php echo $s['whatsapp_phone'] ?? ''; ?>" placeholder="e.g. 963912345678">
                        <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-hover/item:text-green-500 transition-colors">
                            <i class="fas fa-pen"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Email Input -->
                <div class="p-6 rounded-3xl bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all group/item">
                    <div class="flex justify-between mb-5 items-center">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none flex items-center gap-2">
                            <span class="text-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <?php echo __('email_support'); ?>
                        </label>
                    </div>
                    <div class="relative">
                        <input type="email" name="email_support" dir="ltr" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-sm font-black text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all group-hover/item:border-blue-200" value="<?php echo $s['email_support'] ?? ''; ?>" placeholder="support@zygo.com">
                        <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-hover/item:text-blue-500 transition-colors">
                            <i class="fas fa-pen"></i>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        
        <!-- Illustration / Helper -->
        <div class="hidden lg:flex flex-col items-center justify-center p-10 text-center opacity-70">
            <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
            <lottie-player src="https://lottie.host/804eceb6-e820-4384-ad4b-bc597be78b54/yv4qgR9Qx6.json" background="transparent" speed="1" style="width: 300px; height: 300px;" loop autoplay></lottie-player>
            <h4 class="text-lg font-black text-slate-800 mt-6 capitalize"><?php echo __('support'); ?></h4>
            <p class="text-xs font-bold text-slate-400 mt-2 max-w-[250px] leading-relaxed">
                Provide your users with the best support experience. Update the contact details to stay connected.
            </p>
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
