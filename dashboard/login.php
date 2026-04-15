<?php
// C:\xampp\htdocs\dashboardtaxi\login.php
require_once 'config.php';


$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // For this demonstration, we'll use a hardcoded admin or check the users table for role='admin'
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_name'] = $user['name'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid credentials or unauthorized access.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Zygo Taxi Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #F8F9FA; }
        .glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(8px); border: 1px solid rgba(0, 51, 132, 0.05); }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-6">
    <div class="w-full max-w-md animate__animated animate__fadeInUp">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-black tracking-tighter text-[#003384]">ZYGO <span class="text-slate-800">TAXI</span></h1>
            <p class="text-slate-400 mt-2 uppercase text-[10px] font-bold tracking-widest">Administrative Control Panel</p>
        </div>

        <div class="glass p-10 rounded-[40px] shadow-2xl">
            <h2 class="text-2xl font-bold mb-6 text-slate-800">Sign In</h2>
            
            <?php if ($error): ?>
                <div class="bg-red-500/10 border border-red-500/20 text-red-500 p-4 rounded-xl mb-6 text-sm">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 tracking-widest">Email Address</label>
                    <input type="email" name="email" required 
                           class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-slate-800 focus:outline-none focus:border-[#003384] transition-colors"
                           placeholder="admin@zygotaxi.com">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 tracking-widest">Secure Password</label>
                    <input type="password" name="password" required 
                           class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-slate-800 focus:outline-none focus:border-[#003384] transition-colors"
                           placeholder="••••••••">
                </div>
                <button type="submit" 
                        class="w-full bg-[#003384] text-white font-black py-5 rounded-2xl hover:shadow-[0_10px_30px_rgba(0,51,132,0.3)] transition-all transform active:scale-95 text-xs tracking-widest">
                    AUTHENTICATE
                </button>
            </form>
        </div>
        
        <p class="text-center text-slate-500 text-xs mt-8">
            &copy; 2026 ZYGO TECHNOLOGIES. SECURED ACCESS ONLY.
        </p>
    </div>
</body>
</html>
