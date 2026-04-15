<?php
// C:\xampp\htdocs\dashboardtaxi\views\advertisements.php
// View injected cleanly by index.php

$advertisements = $advertisementModel->getAll();
?>

<div class="mb-8 flex items-center justify-between animate__animated animate__fadeIn">
    <div>
        <h2 class="text-3xl font-bold tracking-tight text-slate-800"><?php echo __('ad_campaigns'); ?></h2>
        <p class="text-slate-500 mt-1"><?php echo __('ad_desc'); ?></p>
    </div>
    <button onclick="document.dispatchEvent(new CustomEvent('open-ad-modal', { detail: { action: 'create' } }))" class="bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-2xl font-bold uppercase tracking-widest text-xs flex items-center shadow-premium transition-transform active:scale-95">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        <?php echo __('new_campaign'); ?>
    </button>
</div>

<!-- Active Campaigns Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <?php if (empty($advertisements)): ?>
        <div class="col-span-full p-12 text-center glass-card rounded-3xl">
            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-700"><?php echo __('no_campaigns'); ?></h3>
            <p class="text-sm text-slate-500 mt-1"><?php echo __('upload_graphic'); ?></p>
        </div>
    <?php endif; ?>

    <?php foreach ($advertisements as $ad): 
        $imageUrl = empty($ad['image_url']) ? '' : ((strpos($ad['image_url'], 'http') === 0) ? $ad['image_url'] : ASSET_URL . $ad['image_url']);
    ?>
    <div class="glass-card rounded-[32px] overflow-hidden group relative flex flex-col">
        <!-- Image Cover -->
        <div class="h-48 relative bg-slate-900 overflow-hidden">
            <img src="<?php echo htmlspecialchars($imageUrl); ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-90 group-hover:opacity-100">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>
            
            <div class="absolute top-4 left-4">
                <?php if ($ad['is_active']): ?>
                    <span class="bg-green-500/20 text-green-400 border border-green-500/30 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest backdrop-blur-md shadow-lg"><?php echo __('active'); ?></span>
                <?php else: ?>
                    <span class="bg-slate-500/20 text-slate-300 border border-slate-500/30 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest backdrop-blur-md shadow-lg"><?php echo __('paused'); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="absolute top-4 right-4 flex space-x-2">
                <form method="POST" action="api/advertisement_action.php">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?php echo $ad['id']; ?>">
                    <button type="submit" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white text-white hover:text-slate-800 backdrop-blur-md flex items-center justify-center transition-all border border-white/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" /></svg>
                    </button>
                </form>
                <form method="POST" action="api/advertisement_action.php" onsubmit="return confirm('<?php echo __('confirm_delete_ad'); ?>');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $ad['id']; ?>">
                    <button type="submit" class="w-8 h-8 rounded-full bg-red-500/80 hover:bg-red-600 text-white backdrop-blur-md flex items-center justify-center transition-all border border-red-400/50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </form>
            </div>
            
            <div class="absolute bottom-4 left-4 right-4">
                <h3 class="text-lg font-bold text-white leading-tight drop-shadow-md"><?php echo htmlspecialchars($ad['title_' . ($_SESSION['lang'] ?? 'en')] ?? $ad['title_en']); ?></h3>
            </div>
        </div>

        <!-- Details -->
        <div class="p-5 flex-1 flex flex-col justify-between">
            <p class="text-sm text-slate-500 line-clamp-2 mb-4"><?php echo htmlspecialchars($ad['description_' . ($_SESSION['lang'] ?? 'en')] ?? $ad['description_en'] ?? 'No description'); ?></p>
            
            <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider mt-auto">
                <div class="flex items-center text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    <?php echo htmlspecialchars($ad['button_text_' . ($_SESSION['lang'] ?? 'en')] ?? $ad['button_text_en']); ?>
                </div>
                <?php if ($ad['click_action']): ?>
                    <a href="<?php echo htmlspecialchars($ad['click_action']); ?>" target="_blank" class="flex items-center text-slate-400 hover:text-primary transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                        <?php echo __('test_link'); ?>
                    </a>
                <?php else: ?>
                    <span class="text-slate-300"><?php echo __('unlinked'); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
    document.addEventListener('open-ad-modal', (e) => {
        document.getElementById('adModal').classList.remove('hidden');
        const fileDisplay = document.getElementById('fileNameDisplay');
        if (fileDisplay) fileDisplay.innerText = 'Upload a file';
        
        document.getElementById('imagePreviewContainer').classList.add('hidden');
        document.getElementById('imagePreview').src = '';
        document.getElementById('uploadIcon').classList.remove('hidden');
        document.getElementById('file-upload').value = '';
    });
    function closeAdModal() {
        document.getElementById('adModal').classList.add('hidden');
    }
    
    function handleImagePreview(input) {
        const fileDisplay = document.getElementById('fileNameDisplay');
        const previewContainer = document.getElementById('imagePreviewContainer');
        const previewImg = document.getElementById('imagePreview');
        const uploadIcon = document.getElementById('uploadIcon');
        
        if (input.files && input.files[0]) {
            fileDisplay.innerText = input.files[0].name;
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewContainer.classList.remove('hidden');
                if (uploadIcon) uploadIcon.classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            fileDisplay.innerText = 'Upload a file';
            previewImg.src = '';
            previewContainer.classList.add('hidden');
            if (uploadIcon) uploadIcon.classList.remove('hidden');
        }
    }
</script>

<div id="adModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden">
    
    <!-- Backdrop Click -->
    <div onclick="closeAdModal()" class="absolute inset-0"></div>
    
    <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-2xl overflow-hidden relative z-10 animate__animated animate__zoomIn animate__faster">
         
         <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
             <div>
                <h3 class="text-xl font-bold text-slate-800">Launch Campaign</h3>
                <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold mt-1">Slider Configuration</p>
             </div>
             <button type="button" onclick="closeAdModal()" class="text-slate-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-full transition-colors relative z-[60] cursor-pointer">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
             </button>
         </div>

         <form method="POST" action="api/advertisement_action.php" enctype="multipart/form-data" class="p-8">
             <input type="hidden" name="action" value="create">
             
             <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                 <div class="col-span-full">
                     <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Display Image <span class="text-red-500">*</span></label>
                     <label for="file-upload" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-2xl hover:border-primary/50 transition-colors bg-slate-50 cursor-pointer block w-full">
                         <div class="space-y-1 text-center">
                             <svg id="uploadIcon" class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                             
                             <div id="imagePreviewContainer" class="hidden my-4">
                                 <img id="imagePreview" src="" class="mx-auto h-32 object-cover rounded-2xl shadow-md border-4 border-white">
                             </div>

                             <div class="flex text-sm text-slate-600 justify-center">
                                 <span class="relative font-bold text-primary hover:text-primary-dark outline-none">
                                     <span id="fileNameDisplay">Upload a file</span>
                                     <input id="file-upload" name="image" type="file" class="sr-only" accept="image/*" onchange="handleImagePreview(this)">
                                 </span>
                             </div>
                             <p class="text-xs text-slate-500 mt-2">PNG, JPG, WEBP up to 5MB</p>
                         </div>
                     </label>
                 </div>

                 <!-- EN Fields -->
                 <div class="space-y-4">
                     <div>
                         <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Campaign Title (EN) <span class="text-red-500">*</span></label>
                         <input type="text" name="title_en" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold px-4 py-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50 transition-all placeholder:font-normal" placeholder="e.g. Try Premium Taxi">
                     </div>
                     <div>
                         <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Description (EN)</label>
                         <textarea name="description_en" rows="2" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm px-4 py-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50 transition-all" placeholder="Optional details..."></textarea>
                     </div>
                     <div>
                         <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Button Label (EN)</label>
                         <input type="text" name="button_text_en" value="Explore" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold px-4 py-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50" placeholder="Explore">
                     </div>
                 </div>

                 <!-- AR Fields -->
                 <div class="space-y-4" dir="rtl">
                     <div>
                         <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">العنوان (عربي) <span class="text-red-500">*</span></label>
                         <input type="text" name="title_ar" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold px-4 py-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50 transition-all placeholder:font-normal" placeholder="مثلاً: خصم ٥٠٪ على أول رحلة">
                     </div>
                     <div>
                         <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">الوصف (عربي)</label>
                         <textarea name="description_ar" rows="2" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm px-4 py-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50 transition-all" placeholder="نص تسويقي قصير..."></textarea>
                     </div>
                     <div>
                         <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">نص الزر (عربي)</label>
                         <input type="text" name="button_text_ar" value="استكشاف" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold px-4 py-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50" placeholder="استكشاف">
                     </div>
                 </div>

                 <div class="col-span-full">
                     <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Action URL</label>
                     <input type="url" name="click_action" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold px-4 py-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50" placeholder="https://zygotaxi.com/promo">
                 </div>
             </div>

             <div class="flex items-center justify-between pt-6 border-t border-slate-100">
                <label class="flex items-center cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" name="is_active" class="sr-only" checked>
                        <div class="block bg-slate-200 w-10 h-6 rounded-full transition-colors"></div>
                        <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform translate-x-4 border border-slate-200 shadow-sm"></div>
                    </div>
                    <div class="ml-3 text-sm font-bold text-slate-600">Publish Immediately</div>
                </label>
                <style>
                    input:checked ~ .block { background-color: #10B981; }
                    input:checked ~ .dot { transform: translateX(100%); }
                </style>

                 <div class="space-x-3">
                     <button type="button" onclick="closeAdModal()" class="px-6 py-2.5 rounded-xl font-bold uppercase tracking-widest text-xs text-slate-500 hover:bg-slate-100 transition-colors">Cancel</button>
                     <button type="submit" class="px-8 py-2.5 rounded-xl font-bold uppercase tracking-widest text-xs text-white bg-primary hover:bg-primary-dark shadow-premium transition-all">Save Campaign</button>
                 </div>
             </div>
         </form>
    </div>
</div>
