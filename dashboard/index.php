<?php
// C:\xampp\htdocs\dashboardtaxi\index.php

require_once 'config.php';
require_once 'models/Model.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Simple Router
$page = $_GET['p'] ?? 'dashboard';
$pageTitle = 'Dashboard';

// Load Controllers & Models as needed
if (isset($_GET['ajax']) && $_GET['ajax'] === 'stats') {
    require_once 'models/DashboardModel.php';
    $model = new DashboardModel();
    header('Content-Type: application/json');
    echo json_encode($model->getStats());
    exit;
}

switch ($page) {
    case 'dashboard':
        require_once 'models/DashboardModel.php';
        $model = new DashboardModel();
        $stats = $model->getStats();
        $recentRides = $model->getRecentRides();
        $pageTitle = 'System Overview';
        ob_start();
        include 'views/dashboard.php';
        $content = ob_get_clean();
        break;
    
    case 'users':
        require_once 'models/UserModel.php';
        $userModel = new UserModel();
        ob_start();
        include 'views/users.php';
        $content = ob_get_clean();
        $pageTitle = 'User Management';
        break;

    case 'drivers':
        require_once 'models/DriverModel.php';
        $driverModel = new DriverModel();
        ob_start();
        include 'views/drivers_approval.php';
        $content = ob_get_clean();
        $pageTitle = 'Driver Verification';
        break;

    case 'driver_detail':
        require_once 'models/DriverModel.php';
        $driverModel = new DriverModel();
        $driverId = (int)($_GET['id'] ?? 0);
        $data = $driverModel->getDriverDetails($driverId);
        $driver = $data['user'];
        $docs = $data['docs'];
        ob_start();
        include 'views/driver_detail.php';
        $content = ob_get_clean();
        $pageTitle = 'Driver Dossier';
        break;

    case 'rides':
        require_once 'models/RideModel.php';
        $rideModel = new RideModel();
        ob_start();
        include 'views/rides.php';
        $content = ob_get_clean();
        $pageTitle = 'Ride Tracking';
        break;

    case 'notifications':
        require_once 'models/NotificationModel.php';
        $notificationModel = new NotificationModel();
        ob_start();
        include 'views/notifications.php';
        $content = ob_get_clean();
        $pageTitle = 'Broadcast Center';
        break;

    case 'wallet':
        require_once 'models/WalletModel.php';
        $walletModel = new WalletModel();
        ob_start();
        include 'views/wallet.php';
        $content = ob_get_clean();
        $pageTitle = 'Financial Hub';
        break;

    case 'settings':
        $model = new Model(); // Using base model for simple all() query
        ob_start();
        include 'views/settings.php';
        $content = ob_get_clean();
        $pageTitle = 'System Config';
        break;

    case 'coupons':
        require_once 'models/CouponModel.php';
        $couponModel = new CouponModel();
        ob_start();
        include 'views/coupons.php';
        $content = ob_get_clean();
        $pageTitle = 'Promo Codes';
        break;

    case 'advertisements':
        require_once 'models/AdvertisementModel.php';
        $advertisementModel = new AdvertisementModel();
        ob_start();
        include 'views/advertisements.php';
        $content = ob_get_clean();
        $pageTitle = 'App Sliders';
        break;
    
    case 'ratings':
        require_once 'models/RatingModel.php';
        $ratingModel = new RatingModel();
        ob_start();
        include 'views/ratings.php';
        $content = ob_get_clean();
        $pageTitle = 'Captain Ratings';
        break;

    case 'live_map':
        require_once 'models/DashboardModel.php';
        require_once 'models/RideModel.php';
        $model = new DashboardModel();
        $rideModel = new RideModel();
        $onlineDrivers = $model->getOnlineDrivers();
        $activeRides = $rideModel->getActiveRides();
        
        $selectedRide = null;
        if (isset($_GET['ride_id'])) {
            $selectedRide = $rideModel->getRideDetails((int)$_GET['ride_id']);
        }
        
        ob_start();
        include 'views/live_map.php';
        $content = ob_get_clean();
        $pageTitle = $selectedRide ? 'Live Ride Tracking' : 'Live Fleet Map';
        break;

    case 'support':
        $model = new Model();
        ob_start();
        include 'views/support.php';
        $content = ob_get_clean();
        $pageTitle = 'Tech Support';
        break;
    
    default:
        $pageTitle = '404 - Not Found';
        $content = '<div class="text-center py-20"><h2 class="text-4xl font-bold text-primary">404</h2><p class="text-slate-400">Page under construction or not found.</p></div>';
        break;
}

// Render Layout
include 'views/layout.php';
?>
