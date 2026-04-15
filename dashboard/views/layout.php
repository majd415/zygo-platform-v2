<?php
// C:\xampp\htdocs\dashboardtaxi\views\layout.php
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'en'; ?>" dir="<?php echo ($_SESSION['lang'] ?? 'en') === 'ar' ? 'rtl' : 'ltr'; ?>" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Taxi Admin'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Tajawal:wght@300;400;700;900&display=swap" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#003384',
                        secondary: '#B7D6FF',
                        accent: '#E74C3C',
                        bg: '#F8F9FA',
                        panel: '#FFFFFF',
                        text: '#2C3E50',
                    },
                    fontFamily: {
                        sans: [<?php echo ($_SESSION['lang'] ?? 'en') === 'ar' ? "'Tajawal'" : "'Outfit'"; ?>, 'sans-serif'],
                    },
                    boxShadow: {
                        premium: '0 4px 20px rgba(0, 51, 132, 0.08)',
                        glass: '0 8px 32px 0 rgba(183, 214, 255, 0.2)',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #F8F9FA;
            color: #2C3E50;
            overflow-x: hidden;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(0, 51, 132, 0.05);
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.03);
        }
        .sidebar-item-active {
            background: rgba(0, 51, 132, 0.05);
            border-inline-start: 4px solid #003384;
            color: #003384 !important;
        }
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #F1F5F9;
        }
        ::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #003384;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body x-data="{ sidebarOpen: true }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside 
            :class="sidebarOpen ? 'w-64' : 'w-20'"
            class="transition-all duration-300 glass-card h-full flex flex-col z-50">
            
            <div class="p-6 flex items-center justify-between">
                <span x-show="sidebarOpen" class="text-2xl font-bold tracking-tighter text-primary animate__animated animate__fadeIn">
                    ZYGO <span class="text-slate-800">TAXI</span>
                </span>
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 hover:bg-slate-100 rounded-lg text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 px-4 space-y-2 py-4 overflow-y-auto">
                <?php include 'sidebar_items.php'; ?>
            </nav>

            <div class="p-4 border-t border-slate-100">
                <div class="flex items-center space-x-3 p-2 rounded-lg hover:bg-slate-50 cursor-pointer">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center border border-primary/20">
                        <span class="text-primary font-bold">A</span>
                    </div>
                    <div x-show="sidebarOpen" class="animate__animated animate__fadeIn">
                        <p class="text-sm font-semibold text-slate-800">Admin Panel</p>
                        <p class="text-xs text-slate-400">Manage Systems</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto relative bg-[#F8F9FA]">
            <!-- Top bar -->
            <header class="bg-white sticky top-0 z-40 px-8 py-4 flex items-center justify-between border-b border-slate-100">
                <h1 class="text-xl font-semibold capitalize text-slate-800"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                <div class="flex items-center space-x-6">
                    <div class="relative">
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div class="h-8 w-px bg-slate-100"></div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-slate-800">Administrator</p>
                        <p class="text-xs text-primary font-semibold uppercase tracking-widest">online</p>
                    </div>
                    <div class="h-8 w-px bg-slate-100"></div>
                    <a href="logout.php" class="flex items-center justify-center p-2 text-red-500 hover:bg-red-50 rounded-xl transition-all group" title="<?php echo __('logout'); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </a>
                </div>
            </header>

            <!-- Page View -->
            <div class="p-8">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl flex items-center shadow-sm animate__animated animate__fadeIn">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm font-bold uppercase tracking-tight"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-center shadow-sm animate__animated animate__shakeX">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm font-bold uppercase tracking-tight"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                    </div>
                <?php endif; ?>

                <?php echo $content ?? ''; ?>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            gsap.from(".glass-card:not(.h-full)", {
                duration: 0.4,
                opacity: 0,
                y: 20,
                stagger: 0.05,
                ease: "power2.out"
            });
        });
    </script>
</body>
</html>
