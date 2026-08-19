<?php

namespace App\Services;

use App\Models\Announcement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AnnouncementPublisher
{
    public function __construct(
        private AnnouncementAudienceResolver $audience,
        private NotificationDispatchService $dispatcher,
    ) {
    }

    /**
     * Mark published and push in-app + FCM notifications to the resolved audience.
     *
     * @return array{sent: int, audience: int}
     */
    public function publish(Announcement $announcement): array
    {
        if ($announcement->isPublished()) {
            return [
                'sent' => (int) $announcement->sent_count,
                'audience' => count($this->audience->resolveUserIds($announcement)),
            ];
        }

        $userIds = $this->audience->resolveUserIds($announcement);
        $sent = 0;

        DB::transaction(function () use ($announcement, $userIds, &$sent) {
            $announcement->status = Announcement::STATUS_PUBLISHED;
            $announcement->published_at = now();
            $announcement->save();

            foreach ($userIds as $userId) {
                try {
                    $result = $this->dispatcher->notifyUser(
                        $userId,
                        $announcement->category ?: 'alert',
                        $announcement->title,
                        $announcement->body,
                        [
                            'deep_link' => '/notifications',
                            'announcement_id' => (string) $announcement->id,
                            'audience_type' => $announcement->audience_type,
                        ],
                        true
                    );
                    if ($result !== null) {
                        $sent++;
                    }
                } catch (Throwable $e) {
                    Log::warning('announcement.notify_failed', [
                        'announcement_id' => $announcement->id,
                        'user_id' => $userId,
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            $announcement->sent_count = $sent;
            $announcement->save();
        });

        return [
            'sent' => $sent,
            'audience' => count($userIds),
        ];
    }
}
