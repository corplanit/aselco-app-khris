<?php

namespace App\Observers;

use App\Models\BillingUpload;
use App\Services\NotificationDispatchService;
use Illuminate\Support\Facades\Log;
use Throwable;

class BillingUploadObserver
{
    public function __construct(private NotificationDispatchService $dispatcher)
    {
    }

    public function created(BillingUpload $upload): void
    {
        $this->maybeNotify($upload, 'created');
    }

    public function updated(BillingUpload $upload): void
    {
        if (! $upload->wasChanged('status') && ! $upload->wasChanged('amount')) {
            return;
        }
        $this->maybeNotify($upload, 'updated');
    }

    private function maybeNotify(BillingUpload $upload, string $event): void
    {
        try {
            $upload->loadMissing('accountLink');
            $link = $upload->accountLink;
            if (! $link || ! $link->user_id) {
                return;
            }

            $status = strtolower((string) ($upload->status ?? ''));
            if ($status !== '' && $status !== 'pending') {
                return;
            }

            $amount = number_format((float) $upload->amount, 2);
            $account = $link->account_number;
            $title = 'New bill available';
            $body = "A pending bill of ₱{$amount} was posted for account {$account}.";

            $this->dispatcher->notifyBilling($link->user_id, $title, $body, [
                'account_number' => (string) $account,
                'amount' => (string) $upload->amount,
                'billing_upload_id' => (string) $upload->id,
                'event' => $event,
            ]);
        } catch (Throwable $e) {
            Log::warning('billing_upload.notify_failed', [
                'upload_id' => $upload->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
