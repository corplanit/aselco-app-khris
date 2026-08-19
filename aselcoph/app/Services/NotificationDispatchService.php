<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\DeviceToken;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationDispatchService
{
    public function __construct(private FcmPushService $fcm)
    {
    }

    /**
     * Persist an in-app notification and optionally push via FCM.
     *
     * @param  array<string, mixed>  $data
     */
    public function notifyUser(
        User|int $user,
        string $category,
        string $title,
        string $body,
        array $data = [],
        bool $push = true
    ): ?AppNotification {
        $userId = $user instanceof User ? $user->id : $user;
        $category = strtolower($category);
        if (! in_array($category, ['billing', 'service', 'alert'], true)) {
            $category = 'alert';
        }

        $prefs = NotificationPreference::forUser($userId);
        if (! $prefs->allows($category)) {
            Log::info('notifications.skipped_pref', [
                'user_id' => $userId,
                'category' => $category,
            ]);

            return null;
        }

        $notification = AppNotification::query()->create([
            'user_id' => $userId,
            'category' => $category,
            'title' => $title,
            'body' => $body,
            'data' => $data === [] ? null : $data,
        ]);

        if ($push) {
            $tokens = DeviceToken::query()
                ->where('user_id', $userId)
                ->pluck('token');

            $payload = array_merge($data, [
                'notification_id' => (string) $notification->id,
                'category' => $category,
            ]);

            $this->fcm->sendToTokens($tokens, $title, $body, $payload);
        }

        return $notification;
    }

    public function notifyBilling(User|int $user, string $title, string $body, array $data = []): ?AppNotification
    {
        return $this->notifyUser($user, 'billing', $title, $body, array_merge([
            'deep_link' => '/tabs/ledger',
        ], $data));
    }

    public function notifyService(User|int $user, string $title, string $body, array $data = []): ?AppNotification
    {
        return $this->notifyUser($user, 'service', $title, $body, array_merge([
            'deep_link' => '/tabs/home',
        ], $data));
    }

    public function notifyAlert(User|int $user, string $title, string $body, array $data = []): ?AppNotification
    {
        return $this->notifyUser($user, 'alert', $title, $body, array_merge([
            'deep_link' => '/notifications',
        ], $data));
    }
}
