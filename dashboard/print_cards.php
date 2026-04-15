<?php
// C:\xampp\htdocs\dashboardtaxi\views\print_cards.php
require_once 'config.php';
require_once 'models/Model.php';
require_once 'models/WalletModel.php';

$batchId = $_GET['batch'] ?? null;
$cardId = $_GET['id'] ?? null;
$idsParam = $_GET['ids'] ?? null;

if (!$batchId && !$cardId && !$idsParam) die('No authorization index specified');

$model = new WalletModel();
$cards = [];
$title = "Official Recharge Token";

if ($cardId) {
    $card = $model->getCardById($cardId);
    if ($card) {
        $cards = [$card];
        $title = "Redemption Token #" . str_pad($card['id'], 6, '0', STR_PAD_LEFT);
    } else {
        die('Token not found');
    }
} elseif ($batchId) {
    $cards = $model->getCardsByBatch($batchId);
    $title = "Forge Authority - Batch: " . $batchId;
} elseif ($idsParam) {
    $ids = explode(',', $idsParam);
    $cards = [];
    foreach ($ids as $id) {
        if (is_numeric($id)) {
            $card = $model->getCardById((int)$id);
            if ($card) $cards[] = $card;
        }
    }
    $title = "Selected Authority: " . count($cards) . " Tokens";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #fff; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
            .card { break-inside: avoid; }
        }
        .card {
            border: 2px solid #003384;
            padding: 24px;
            width: 380px;
            border-radius: 32px;
            margin: 15px;
            display: inline-block;
            position: relative;
            background: #fff;
            box-shadow: 0 10px 25px -5px rgba(0, 51, 132, 0.1);
        }
        .card-bg {
            position: absolute;
            top: 0; right: 0; bottom: 0; left: 0;
            opacity: 0.05;
            background-image: url('https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=ZYGO');
            background-size: 150px;
            background-repeat: no-repeat;
            background-position: center;
        }
        .qr-placeholder {
            width: 80px;
            height: 80px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>
<body class="p-8">
    <div class="no-print mb-12 flex justify-between items-center bg-slate-900 p-8 rounded-[40px] text-white shadow-2xl">
        <div>
            <h1 class="text-3xl font-black uppercase italic tracking-tighter"><?php echo count($cards) > 1 ? "Forge Authority: " . count($cards) . " Tokens" : "Single Token Authorization"; ?></h1>
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] mt-2">Authorization ID: <span class="text-primary"><?php echo $batchId ?: "TKN-".str_pad($cards[0]['id'], 6, '0', STR_PAD_LEFT); ?></span></p>
        </div>
        <div class="flex space-x-4">
            <button onclick="window.print()" class="bg-primary text-white px-10 py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-premium hover:scale-105 transition-all active:scale-95">
                EXECUTE PDF PRINT
            </button>
        </div>
    </div>

    <div class="flex flex-wrap justify-center gap-4">
        <?php foreach ($cards as $c): ?>
        <div class="card overflow-hidden">
            <div class="card-bg"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <span class="text-[12px] font-black text-primary uppercase tracking-tighter italic">ZYGO <span class="text-slate-800 not-italic">TAXI</span></span>
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Official Recharge Token</p>
                    </div>
                    <span class="text-[10px] font-black text-slate-300 tabular-nums">#<?php echo str_pad($c['id'], 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                
                <div class="flex items-center space-x-6 mb-6 pb-6 border-b border-slate-100">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo $c['code']; ?>" class="w-20 h-20 rounded-xl shadow-sm border border-slate-50">
                    <div class="flex-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Redemption Key</p>
                        <h2 class="text-2xl font-black text-slate-800 tracking-wider tabular-nums leading-none"><?php echo $c['code']; ?></h2>
                        <div class="flex mt-3 space-x-2">
                            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                            <span class="text-[8px] font-black text-green-600 uppercase tracking-widest">Digital Auth Ready</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-end">
                    <div class="bg-primary/5 px-4 py-3 rounded-2xl border border-primary/10">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Load Balance</p>
                        <p class="text-2xl font-black text-primary italic leading-none tabular-nums"><?php echo number_format($c['balance']); ?> <span class="text-[10px] uppercase not-italic">SYP</span></p>
                    </div>
                    <?php if ($c['expiry_date']): ?>
                    <div class="text-right pb-1">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Expiration</p>
                        <p class="text-[11px] font-black text-slate-800 uppercase tabular-nums"><?php echo date('d M, Y', strtotime($c['expiry_date'])); ?></p>
                    </div>
                    <?php else: ?>
                    <div class="text-right pb-1">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Validity</p>
                        <p class="text-[11px] font-black text-green-600 uppercase">PERMANENT</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
