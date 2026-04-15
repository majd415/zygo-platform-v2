<?php
// C:\xampp\htdocs\dashboardtaxi\views\notifications.php
$limit = 4;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$history = $notificationModel->getNotifications($limit, $offset);
$total = $notificationModel->getTotalCount();
$totalPages = ceil($total / $limit);
?>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    <!-- Composer -->
    <div class="xl:col-span-2 space-y-6">
        <div class="glass-card p-8 rounded-[40px]">
            <h2 class="text-2xl font-bold mb-2 text-slate-800"><?php echo __('broadcast_center'); ?></h2>
            <p class="text-slate-400 text-sm mb-8"><?php echo __('broadcast_desc'); ?></p>

            <form method="POST" action="api/send_notification.php" class="space-y-6">
                <!-- Send Push Notification Form (same as before) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2"><?php echo __('notif_title_en'); ?></label>
                        <input type="text" name="title_en" required class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3.5 text-xs font-bold text-slate-700 focus:outline-none focus:border-primary transition" placeholder="e.g. Major Service Update">
                    </div>
                    <div dir="rtl">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2"><?php echo __('notif_title_ar'); ?></label>
                        <input type="text" name="title_ar" required class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3.5 text-xs font-bold text-slate-700 focus:outline-none focus:border-primary transition" placeholder="مثلاً: تحديث هام في الخدمة">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2"><?php echo __('alert_type'); ?></label>
                    <select name="type" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3.5 text-xs font-bold text-slate-500 focus:outline-none focus:border-primary uppercase tracking-wider">
                        <option value="important"><?php echo __('important'); ?></option>
                        <option value="promotional"><?php echo __('promotional'); ?></option>
                        <option value="emergency"><?php echo __('emergency'); ?></option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2"><?php echo __('message_body_en'); ?></label>
                        <textarea name="message_en" rows="4" required class="w-full bg-slate-50 border border-slate-100 rounded-2xl p-4 text-xs font-medium text-slate-600 focus:outline-none focus:border-primary transition" placeholder="English announcement..."></textarea>
                    </div>
                    <div dir="rtl">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2"><?php echo __('message_body_ar'); ?></label>
                        <textarea name="message_ar" rows="4" required class="w-full bg-slate-50 border border-slate-100 rounded-2xl p-4 text-xs font-medium text-slate-600 focus:outline-none focus:border-primary transition" placeholder="اكتب الإعلان بالعربية هنا..."></textarea>
                    </div>
                </div>

                <div class="p-6 bg-slate-100/50 rounded-[32px] border border-slate-100">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4"><?php echo __('target_audience'); ?></label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <label class="relative flex items-center justify-center p-4 border border-slate-200 bg-white rounded-2xl cursor-pointer hover:bg-slate-50 transition has-[:checked]:bg-primary has-[:checked]:border-primary has-[:checked]:text-white" onclick="document.getElementById('specificIdContainer').classList.remove('hidden')">
                            <input type="radio" name="target" value="specific" class="hidden">
                            <span class="text-[10px] font-black uppercase tracking-tighter"><?php echo __('specific_id'); ?></span>
                        </label>
                        <label class="relative flex items-center justify-center p-4 border border-slate-200 bg-white rounded-2xl cursor-pointer hover:bg-slate-50 transition has-[:checked]:bg-primary has-[:checked]:border-primary has-[:checked]:text-white" onclick="document.getElementById('specificIdContainer').classList.add('hidden')">
                            <input type="radio" name="target" value="all" checked class="hidden">
                            <span class="text-[10px] font-black uppercase tracking-tighter"><?php echo __('all_users'); ?></span>
                        </label>
                        <label class="relative flex items-center justify-center p-4 border border-slate-200 bg-white rounded-2xl cursor-pointer hover:bg-slate-50 transition has-[:checked]:bg-primary has-[:checked]:border-primary has-[:checked]:text-white" onclick="document.getElementById('specificIdContainer').classList.add('hidden')">
                            <input type="radio" name="target" value="drivers" class="hidden">
                            <span class="text-[10px] font-black uppercase tracking-tighter"><?php echo __('drivers'); ?></span>
                        </label>
                        <label class="relative flex items-center justify-center p-4 border border-slate-200 bg-white rounded-2xl cursor-pointer hover:bg-slate-50 transition has-[:checked]:bg-primary has-[:checked]:border-primary has-[:checked]:text-white" onclick="document.getElementById('specificIdContainer').classList.add('hidden')">
                            <input type="radio" name="target" value="riders" class="hidden">
                            <span class="text-[10px] font-black uppercase tracking-tighter"><?php echo __('passengers'); ?></span>
                        </label>
                    </div>
                </div>

                <div id="specificIdContainer" class="hidden animate__animated animate__fadeIn">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2"><?php echo __('target_user_id'); ?> <span class="text-red-500">*</span></label>
                    <input type="text" name="user_id" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3.5 text-xs font-bold text-slate-700 focus:outline-none focus:border-primary transition" placeholder="e.g. 104">
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <div class="flex items-center space-x-4">
                        <button type="button" class="text-[10px] font-black text-slate-400 hover:text-primary transition uppercase tracking-widest"><?php echo __('schedule_dispatch'); ?></button>
                        <button type="button" class="text-[10px] font-black text-slate-400 hover:text-primary transition uppercase tracking-widest"><?php echo __('attach_media'); ?></button>
                    </div>
                    <button type="submit" class="bg-primary text-white px-10 py-4 rounded-2xl font-black uppercase text-[11px] tracking-widest shadow-premium hover:scale-105 active:scale-95 transition-all">
                        <?php echo __('send_broadcast'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- History Sidebar -->
    <div class="space-y-6">
        <form method="POST" action="api/notification_action.php" id="bulkDeleteForm">
            <input type="hidden" name="action" value="bulk_delete">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest"><?php echo __('recent_dispatches'); ?></h4>
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" id="selectAllNotifs" class="w-4 h-4 rounded border-slate-200 text-primary focus:ring-primary">
                        <span class="text-[10px] font-black uppercase text-slate-400 group-hover:text-primary transition"><?php echo __('select_all'); ?></span>
                    </label>
                    <button type="submit" id="bulkDeleteBtn" class="bg-red-500 text-white p-2 rounded-lg opacity-0 pointer-events-none transition-all hover:bg-red-600" title="Delete Selected">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </div>

            <div class="space-y-4">
                <?php foreach ($history as $n): ?>
                <div class="glass-card p-6 rounded-[32px] border-l-4 group hover:bg-slate-50 transition-colors relative <?php echo $n['type'] === 'emergency' ? 'border-red-500' : ($n['type'] === 'promotional' ? 'border-accent' : 'border-primary'); ?>">
                    <div class="absolute -left-2 top-1/2 -translate-y-1/2 flex items-center gap-2">
                         <input type="checkbox" name="ids[]" value="<?php echo $n['id']; ?>" class="notif-checkbox w-4 h-4 rounded border-slate-200 text-primary focus:ring-primary shadow-sm bg-white cursor-pointer transition-all hover:scale-110">
                    </div>
                    <div class="flex justify-between items-start mb-3">
                        <span class="px-2 py-0.5 rounded-lg bg-white shadow-sm text-[8px] font-black uppercase tracking-widest <?php echo $n['type'] === 'emergency' ? 'text-red-500' : 'text-slate-400'; ?>"><?php echo __($n['type']); ?></span>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-slate-300"><?php echo date('M d, H:i', strtotime($n['created_at'])); ?></span>
                            <button type="button" onclick="deleteNotification(<?php echo $n['id']; ?>)" class="text-slate-300 hover:text-red-500 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>
                    <h5 class="text-sm font-bold text-slate-800 mb-1"><?php echo htmlspecialchars($n['title_' . ($_SESSION['lang'] ?? 'en')] ?? $n['title_en']); ?></h5>
                    <p class="text-[11px] text-slate-400 line-clamp-2 leading-relaxed"><?php echo htmlspecialchars($n['message_' . ($_SESSION['lang'] ?? 'en')] ?? $n['message_en']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="flex justify-center items-center gap-2 mt-8">
                <?php if ($page > 1): ?>
                    <a href="?p=notifications&page=<?php echo $page - 1; ?>" class="w-10 h-10 flex items-center justify-center rounded-2xl bg-white border border-slate-100 text-slate-400 hover:text-primary transition shadow-sm hover:shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </a>
                <?php endif; ?>
                
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-4">
                    Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                </span>

                <?php if ($page < $totalPages): ?>
                    <a href="?p=notifications&page=<?php echo $page + 1; ?>" class="w-10 h-10 flex items-center justify-center rounded-2xl bg-white border border-slate-100 text-slate-400 hover:text-primary transition shadow-sm hover:shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<form id="deleteSingleForm" method="POST" action="api/notification_action.php">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteNotifId">
</form>

<script>
    function deleteNotification(id) {
        if (confirm('Are you sure you want to delete this notification?')) {
            document.getElementById('deleteNotifId').value = id;
            document.getElementById('deleteSingleForm').submit();
        }
    }

    const selectAll = document.getElementById('selectAllNotifs');
    const checkboxes = document.querySelectorAll('.notif-checkbox');
    const bulkBtn = document.getElementById('bulkDeleteBtn');

    selectAll.addEventListener('change', () => {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        toggleBulkBtn();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            toggleBulkBtn();
            if(!cb.checked) selectAll.checked = false;
        });
    });

    function toggleBulkBtn() {
        const checked = Array.from(checkboxes).some(cb => cb.checked);
        if (checked) {
            bulkBtn.classList.remove('opacity-0', 'pointer-events-none');
        } else {
            bulkBtn.classList.add('opacity-0', 'pointer-events-none');
        }
    }

    document.getElementById('bulkDeleteForm').addEventListener('submit', (e) => {
        if (!confirm('Are you sure you want to delete the selected notifications?')) {
            e.preventDefault();
        }
    });
</script>

