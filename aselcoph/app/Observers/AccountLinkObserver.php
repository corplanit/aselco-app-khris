<?php

namespace App\Observers;

use App\Models\AccountLink;
use App\Services\NotificationDispatchService;
use Illuminate\Support\Facades\Log;
use Throwable;

class AccountLinkObserver
{
    public function __construct(private NotificationDispatchService $dispatcher)
    {
    }

    public function updated(AccountLink $link): void
    {
        if (! $link->wasChanged('validated_at') || $link->validated_at === null) {
            return;
        }

        try {
            $this->dispatcher->notifyAlert(
                $link->user_id,
                'Account linked',
                "Your electric account {$link->account_number} has been validated.",
                [
                    'account_number' => (string) $link->account_number,
                    'deep_link' => '/tabs/profile',
                ]
            );
        } catch (Throwable $e) {
            Log::warning('account_link.notify_failed', [
                'link_id' => $link->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
