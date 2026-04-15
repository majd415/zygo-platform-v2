<?php
// C:\xampp\htdocs\dashboardtaxi\views\ratings.php

require_once 'models/RatingModel.php';
$ratingModel = new RatingModel();

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $ratingModel->deleteRating($_GET['id']);
    header('Location: ?p=ratings&deleted=1');
    exit;
}

$ratings = $ratingModel->getAll();
?>

<div class="space-y-8 animate__animated animate__fadeIn">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight"><?php echo __('ratings'); ?></h1>
            <p class="text-slate-500 mt-1 font-medium"><?php echo __('monitoring_fleet_feedback'); ?></p>
        </div>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
    <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 px-6 py-4 rounded-2xl font-bold flex items-center space-x-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        <span>Rating deleted and captain score recalculated successfully.</span>
    </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden text-[10px]">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-50">
                        <th class="px-8 py-6 font-black uppercase text-slate-400 tracking-widest"><?php echo __('date'); ?></th>
                        <th class="px-8 py-6 font-black uppercase text-slate-400 tracking-widest"><?php echo __('rider'); ?></th>
                        <th class="px-8 py-6 font-black uppercase text-slate-400 tracking-widest"><?php echo __('captain'); ?></th>
                        <th class="px-8 py-6 font-black uppercase text-slate-400 tracking-widest"><?php echo __('stars'); ?></th>
                        <th class="px-8 py-6 font-black uppercase text-slate-400 tracking-widest"><?php echo __('comment'); ?></th>
                        <th class="px-8 py-6 font-black uppercase text-slate-400 tracking-widest text-right"><?php echo __('action'); ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($ratings)): ?>
                        <tr>
                            <td colspan="6" class="px-8 py-12 text-center text-slate-400 font-medium text-sm">
                                No ratings found in the system yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ratings as $r): ?>
                            <tr class="group hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-6">
                                    <span class="font-bold text-slate-500"><?php echo date('M d, H:i', strtotime($r['created_at'])); ?></span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center font-black text-slate-400">
                                            <?php echo substr($r['rider_name'] ?? 'P', 0, 1); ?>
                                        </div>
                                        <span class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($r['rider_name'] ?? 'Passenger'); ?></span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center font-black text-primary">
                                            <?php echo substr($r['driver_name'] ?? 'C', 0, 1); ?>
                                        </div>
                                        <span class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($r['driver_name'] ?? 'Captain'); ?></span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center space-x-0.5">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 <?php echo $i <= $r['rating'] ? 'text-amber-400' : 'text-slate-200'; ?>" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="max-w-xs text-sm text-slate-500 font-medium italic truncate" title="<?php echo htmlspecialchars($r['comment']); ?>">
                                        "<?php echo $r['comment'] ? htmlspecialchars($r['comment']) : 'No comment provided'; ?>"
                                    </p>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <button onclick="confirmDelete(<?php echo $r['id']; ?>)" class="p-2 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this rating? The captain\'s average score will be recalculated.')) {
        window.location.href = '?p=ratings&action=delete&id=' + id;
    }
}
</script>
