<?php
$monthlyRevenue = $model->getMonthlyRevenue(7);
$monthlyRiders = $model->getMonthlyRiders(7);

$revenueLabels = [];
$revenueDataObj = [];
$rideCountDataObj = [];
$riderDataObj = [];

// Initialize last 7 months to guarantee continuous chart line connection
for ($i = 6; $i >= 0; $i--) {
    $monthName = date('M', strtotime("-$i months"));
    $revenueLabels[] = $monthName;
    $revenueDataObj[$monthName] = 0;
    $rideCountDataObj[$monthName] = 0;
    $riderDataObj[$monthName] = 0;
}

// Fill with actual database results where applicable
foreach($monthlyRevenue as $row) {
    if (isset($revenueDataObj[$row['month']])) {
        $revenueDataObj[$row['month']] = (float)$row['total'];
        $rideCountDataObj[$row['month']] = (int)$row['ride_count'];
    }
}

foreach($monthlyRiders as $row) {
    if (isset($riderDataObj[$row['month']])) {
        $riderDataObj[$row['month']] = (int)$row['total'];
    }
}

// Convert associative maps back to indexed arrays for Chart.js
$revenueData = array_values($revenueDataObj);
$rideCountData = array_values($rideCountDataObj);
$riderLabels = $revenueLabels;
$riderData = array_values($riderDataObj);
?>
<div class="space-y-8 animate__animated animate__fadeIn">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <?php 
        $statCards = [
            [
                'title' => 'total_riders', 
                'value' => number_format($stats['total_users']), 
                'color' => 'bg-blue-500', 
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />'
            ],
            [
                'title' => 'total_captains', 
                'value' => number_format($stats['total_drivers']), 
                'color' => 'bg-indigo-500', 
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />'
            ],
            [
                'title' => 'online_captains', 
                'value' => number_format($stats['online_drivers']), 
                'color' => 'bg-green-500', 
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />'
            ],
            [
                'title' => 'pending_reviews', 
                'value' => number_format($stats['pending_drivers']), 
                'color' => 'bg-yellow-500', 
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />'
            ],
            [
                'title' => 'active_rides_stat', 
                'value' => number_format($stats['active_rides']), 
                'color' => 'bg-purple-500', 
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />'
            ],
            [
                'title' => 'completed_rides_stat', 
                'value' => number_format($stats['completed_rides']), 
                'color' => 'bg-teal-500', 
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />'
            ],
            [
                'title' => 'canceled_rides_stat', 
                'value' => number_format($stats['canceled_rides']), 
                'color' => 'bg-red-500', 
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />'
            ],
            [
                'title' => 'total_generated_cards', 
                'value' => number_format($stats['total_generated_cards']), 
                'color' => 'bg-fuchsia-500', 
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />'
            ],
            [
                'title' => 'total_generated_value', 
                'value' => number_format($stats['total_generated_value']) . ' <span class="text-xs font-normal text-slate-400 italic">SYP</span>', 
                'color' => 'bg-cyan-500', 
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />'
            ],
            [
                'title' => 'revenue_24h', 
                'value' => number_format($stats['total_revenue']) . ' <span class="text-xs font-normal text-slate-400 italic">SYP</span>', 
                'color' => 'bg-emerald-500', 
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
            ],
            [
                'title' => 'platform_earnings', 
                'value' => number_format($stats['platform_earnings']) . ' <span class="text-xs font-normal text-slate-400 italic">SYP</span>', 
                'color' => 'bg-amber-500', 
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
            ],
        ];

        foreach($statCards as $card): 
            $titleStr = __($card['title']) !== $card['title'] ? __($card['title']) : $card['title'];
        ?>
            <div class="glass-card p-6 rounded-[32px] relative overflow-hidden group">
                <!-- Icon Background -->
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-slate-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <?php echo $card['icon']; ?>
                    </svg>
                </div>
                
                <!-- LED Glow Indicator -->
                <div class="flex items-center justify-between mb-4">
                    <div class="relative flex items-center justify-center">
                        <div class="w-3 h-3 rounded-full <?php echo $card['color']; ?> animate-ping absolute opacity-75"></div>
                        <div class="w-2 h-2 rounded-full <?php echo $card['color']; ?> relative"></div>
                    </div>
                </div>

                <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest mb-1"><?php echo $titleStr; ?></p>
                <h3 id="stat-<?php echo $card['title']; ?>" class="text-3xl font-black text-slate-800 tabular-nums">
                    <?php echo $card['value']; ?>
                </h3>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Revenue Chart -->
        <div class="lg:col-span-2 glass-card p-8 rounded-[40px]">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h4 class="text-xl font-bold text-slate-800 tracking-tight"><?php echo __('revenue_analytics'); ?></h4>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1"><?php echo __('earnings_dist'); ?></p>
                </div>
                <select class="bg-slate-50 border border-slate-100 rounded-xl px-4 py-2 text-[10px] font-black text-slate-500 focus:outline-none focus:border-primary uppercase tracking-widest">
                    <option><?php echo __('last_30_days'); ?></option>
                    <option><?php echo __('last_6_months'); ?></option>
                </select>
            </div>
            <div class="h-80">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="glass-card p-8 rounded-[40px] flex flex-col">
            <h4 class="text-xl font-bold mb-6 text-slate-800"><?php echo __('user_growth_stat'); ?></h4>
            <div class="flex-1 h-48">
                <canvas id="userChart"></canvas>
            </div>
            <div class="mt-8 pt-8 border-t border-slate-100">
                <div class="flex items-center justify-between text-[10px] font-black uppercase text-slate-400 tracking-widest mb-4">
                    <span><?php echo __('recent_activity_stat'); ?></span>
                    <span class="text-primary hover:underline cursor-pointer"><?php echo __('live_feed'); ?></span>
                </div>
                <div class="space-y-4">
                    <?php foreach (array_slice($recentRides, 0, 3) as $ride): ?>
                    <div class="flex items-center space-x-3 group cursor-pointer">
                        <div class="w-2 h-2 rounded-full bg-primary group-hover:scale-150 transition-transform"></div>
                        <p class="text-xs text-slate-500 truncate flex-1 font-medium italic">
                            <span class="text-slate-800 font-bold not-italic"><?php echo htmlspecialchars($ride['rider_name'] ?? 'Guest'); ?></span> 
                            <?php echo __('requested_ride_msg'); ?>
                        </p>
                        <span class="text-[10px] text-slate-300 font-bold"><?php echo date('H:i', strtotime($ride['created_at'])); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const i18n = {
            mon: '<?php echo __('mon'); ?>',
            tue: '<?php echo __('tue'); ?>',
            wed: '<?php echo __('wed'); ?>',
            thu: '<?php echo __('thu'); ?>',
            fri: '<?php echo __('fri'); ?>',
            sat: '<?php echo __('sat'); ?>',
            sun: '<?php echo __('sun'); ?>',
            revenue: '<?php echo __('revenue'); ?>',
            new_riders: '<?php echo __('new_riders'); ?>',
            week: '<?php echo __('week'); ?>'
        };

        // Revenue Chart
        const revCtx = document.getElementById('revenueChart').getContext('2d');
        const revGradient = revCtx.createLinearGradient(0, 0, 0, 400);
        revGradient.addColorStop(0, 'rgba(0, 51, 132, 0.08)');
        revGradient.addColorStop(1, 'rgba(0, 51, 132, 0)');

        const rideGradient = revCtx.createLinearGradient(0, 0, 0, 400);
        rideGradient.addColorStop(0, 'rgba(16, 185, 129, 0.08)'); // emerald gradient
        rideGradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($revenueLabels); ?>,
                datasets: [
                    {
                        label: i18n.revenue,
                        data: <?php echo json_encode($revenueData); ?>,
                        borderColor: '#003384',
                        borderWidth: 4,
                        pointBackgroundColor: '#FFFFFF',
                        pointBorderColor: '#003384',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        fill: true,
                        backgroundColor: revGradient,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Completed Rides',
                        data: <?php echo json_encode($rideCountData); ?>,
                        borderColor: '#10B981',
                        borderWidth: 4,
                        pointBackgroundColor: '#FFFFFF',
                        pointBorderColor: '#10B981',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        fill: true,
                        backgroundColor: rideGradient,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: true, position: 'top', labels: { usePointStyle: true, boxWidth: 8, font: { weight: 'bold' } } } 
                },
                scales: {
                    y: { 
                        type: 'linear', 
                        display: true, 
                        position: 'left',
                        grid: { color: 'rgba(0, 0, 0, 0.03)' }, 
                        ticks: { color: '#94A3B8', font: { size: 10, weight: 'bold' } } 
                    },
                    y1: { 
                        type: 'linear', 
                        display: true, 
                        position: 'right',
                        grid: { drawOnChartArea: false }, 
                        ticks: { color: '#10B981', font: { size: 10, weight: 'bold' }, stepSize: 1 } 
                    },
                    x: { 
                        grid: { display: false }, 
                        ticks: { color: '#94A3B8', font: { size: 10, weight: 'bold' } } 
                    }
                }
            }
        });

        // User Growth Chart
        const userCtx = document.getElementById('userChart').getContext('2d');
        new Chart(userCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($riderLabels); ?>,
                datasets: [{
                    label: i18n.new_riders,
                    data: <?php echo json_encode($riderData); ?>,
                    backgroundColor: '#B7D6FF',
                    borderRadius: 12,
                    hoverBackgroundColor: '#003384'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { display: false },
                    x: { grid: { display: false }, ticks: { color: '#94A3B8', font: { size: 10, weight: 'bold' } } }
                }
            }
        });
        // Setup Polling for Real-Time Stats matching exact keys in DB
        setInterval(() => {
            fetch('?ajax=stats')
                .then(res => res.json())
                .then(data => {
                    const mappings = {
                        'total_captains': data.total_drivers,
                        'online_captains': data.online_drivers,
                        'pending_reviews': data.pending_drivers,
                        'active_rides_stat': data.active_rides,
                        'completed_rides_stat': data.completed_rides,
                        'canceled_rides_stat': data.canceled_rides,
                        'total_generated_cards': data.total_generated_cards,
                        'total_generated_value': new Intl.NumberFormat().format(data.total_generated_value) + ' <span class="text-xs font-normal text-slate-400 italic">SYP</span>',
                        'revenue_24h': new Intl.NumberFormat().format(data.total_revenue) + ' <span class="text-xs font-normal text-slate-400 italic">SYP</span>',
                        'platform_earnings': new Intl.NumberFormat().format(data.platform_earnings) + ' <span class="text-xs font-normal text-slate-400 italic">SYP</span>'
                    };
                    
                    for (const [key, value] of Object.entries(mappings)) {
                        const el = document.getElementById('stat-' + key);
                        if (el) {
                            if (typeof value === 'number') {
                                el.innerHTML = new Intl.NumberFormat().format(value);
                            } else {
                                el.innerHTML = value;
                            }
                        }
                    }

                    // Optional: animate active rides specifically if we want a bump effect
                    const activeEl = document.getElementById('stat-active_rides_stat');
                    if (activeEl && !activeEl.dataset.initialized) { 
                        activeEl.dataset.prevVal = data.active_rides;
                        activeEl.dataset.initialized = 'true';
                    } else if (activeEl && activeEl.dataset.prevVal != data.active_rides) {
                        activeEl.classList.add('animate__animated', 'animate__rubberBand', 'text-primary');
                        setTimeout(() => activeEl.classList.remove('animate__animated', 'animate__rubberBand', 'text-primary'), 1000);
                        activeEl.dataset.prevVal = data.active_rides;
                    }

                })
                .catch(err => console.error("Stats sync error", err));
        }, 5000);
    });
</script>

