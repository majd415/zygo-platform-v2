<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\FirebaseService;

class BroadcastPushCommand extends Command
{
    /**
     * The name and signature of the console command.
     * signature format: broadcast:push {--target=} {--title=} {--message=} {--type=} {--user_id=}
     */
    protected $signature = 'broadcast:push 
                            {--target=all : all, drivers, riders, specific}
                            {--title_en= : Notification title (EN)}
                            {--title_ar= : Notification title (AR)}
                            {--message_en= : Notification message body (EN)}
                            {--message_ar= : Notification message body (AR)}
                            {--type=system_alert : Alert type}
                            {--user_id= : Specific user ID if target is specific}';

    protected $description = 'Dispatches an FCM Push Notification to targeted cohorts.';

    public function handle()
    {
        $target = $this->option('target');
        $title_en = $this->option('title_en');
        $title_ar = $this->option('title_ar');
        $message_en = $this->option('message_en');
        $message_ar = $this->option('message_ar');
        $type = $this->option('type');
        $userId = $this->option('user_id');

        $query = User::whereNotNull('fcm_token');

        if ($target === 'drivers') {
            $query->where('role', 'driver');
        } elseif ($target === 'riders') {
            $query->where('role', 'rider');
        } elseif ($target === 'specific' && $userId) {
            $query->where('id', $userId);
        }

        $users = $query->get();
        $sentCount = 0;
        $this->info("Found {$users->count()} users targeting [{$target}]. Commencing broadcast...");

        foreach ($users as $user) {
            try {
                $userLang = $user->language ?? 'en';
                $finalTitle = ($userLang === 'ar' && !empty($title_ar)) ? $title_ar : ($title_en ?? $title_ar);
                $finalMessage = ($userLang === 'ar' && !empty($message_ar)) ? $message_ar : ($message_en ?? $message_ar);

                $dataPayload = [
                    'type' => 'system_alert',
                    'alert_type' => $type,
                    'title' => $finalTitle,
                    'message' => $finalMessage,
                    'target_screen' => 'notification',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ];

                FirebaseService::sendNotification(
                    $user->fcm_token,
                    $finalTitle,
                    $finalMessage,
                    $dataPayload
                );
                $sentCount++;
            } catch (\Exception $e) {
                $this->error("Failed to send to User {$user->id}: " . $e->getMessage());
            }
        }

        $this->info("Broadcast complete. Sent {$sentCount} notifications successfully.");
        return 0;
    }
}
