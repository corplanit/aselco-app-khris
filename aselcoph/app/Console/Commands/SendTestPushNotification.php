<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationDispatchService;
use Illuminate\Console\Command;

class SendTestPushNotification extends Command
{
    protected $signature = 'aselco:notify
        {user : User id or email}
        {--category=alert : billing|service|alert}
        {--title=ASELCO alert : Notification title}
        {--body=This is a test mobile alert. : Notification body}';

    protected $description = 'Send an in-app (+ FCM) notification to a member for demos and QA';

    public function handle(NotificationDispatchService $dispatcher): int
    {
        $lookup = (string) $this->argument('user');
        $user = is_numeric($lookup)
            ? User::query()->find((int) $lookup)
            : User::query()->where('email', $lookup)->first();

        if (! $user) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        $category = strtolower((string) $this->option('category'));
        $title = (string) $this->option('title');
        $body = (string) $this->option('body');

        $notification = match ($category) {
            'billing' => $dispatcher->notifyBilling($user, $title, $body),
            'service' => $dispatcher->notifyService($user, $title, $body),
            default => $dispatcher->notifyAlert($user, $title, $body),
        };

        if (! $notification) {
            $this->warn('Notification skipped (user preference disabled).');

            return self::SUCCESS;
        }

        $this->info("Notification #{$notification->id} created for user {$user->id} ({$category}).");

        return self::SUCCESS;
    }
}
