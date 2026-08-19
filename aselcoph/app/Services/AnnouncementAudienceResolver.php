<?php

namespace App\Services;

use App\Models\AccountLink;
use App\Models\Announcement;
use App\Models\TAccountRaw;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class AnnouncementAudienceResolver
{
    /**
     * Resolve distinct member user IDs for an announcement audience.
     *
     * @return list<int>
     */
    public function resolveUserIds(Announcement $announcement): array
    {
        return match ($announcement->audience_type) {
            Announcement::AUDIENCE_USERS => $this->fromUserIds($announcement->audience_user_ids ?? []),
            Announcement::AUDIENCE_METER => $this->fromMeterNumbers($announcement->meter_numbers ?? []),
            default => $this->allMembers(),
        };
    }

    /**
     * @param  list<mixed>  $ids
     * @return list<int>
     */
    private function fromUserIds(array $ids): array
    {
        $clean = collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        if ($clean->isEmpty()) {
            return [];
        }

        return User::query()
            ->whereIn('id', $clean->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Resolve users by meter number (testing against a specific consumer base).
     *
     * @param  list<mixed>  $meters
     * @return list<int>
     */
    private function fromMeterNumbers(array $meters): array
    {
        $normalized = collect($meters)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return [];
        }

        $userIds = collect();

        try {
            $fromRaw = TAccountRaw::query()
                ->whereIn('meter_no', $normalized->all())
                ->whereNotNull('user_id')
                ->pluck('user_id');
            $userIds = $userIds->merge($fromRaw);

            // Also match account_no if staff pastes an account number into the meter field by mistake.
            $fromAccountNo = TAccountRaw::query()
                ->whereIn('account_no', $normalized->all())
                ->whereNotNull('user_id')
                ->pluck('user_id');
            $userIds = $userIds->merge($fromAccountNo);
        } catch (Throwable $e) {
            Log::warning('announcement.meter_raw_lookup_failed', ['message' => $e->getMessage()]);
        }

        $accountNumbers = collect();
        try {
            $accountNumbers = TAccountRaw::query()
                ->whereIn('meter_no', $normalized->all())
                ->pluck('account_no')
                ->merge(
                    TAccountRaw::query()
                        ->whereIn('account_no', $normalized->all())
                        ->pluck('account_no')
                )
                ->map(fn ($no) => trim((string) $no))
                ->filter()
                ->unique();
        } catch (Throwable) {
            $accountNumbers = $normalized;
        }

        if ($accountNumbers->isNotEmpty()) {
            $fromLinks = AccountLink::query()
                ->whereIn('account_number', $accountNumbers->all())
                ->pluck('user_id');
            $userIds = $userIds->merge($fromLinks);
        }

        return $userIds
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * All verified members (excludes common staff roles).
     *
     * @return list<int>
     */
    private function allMembers(): array
    {
        $staffRoles = [
            'Administrator',
            'administrator',
            'staff',
            'support',
            'Content Manager',
            'Customer Service',
        ];

        return User::query()
            ->whereNotNull('email_verified_at')
            ->where(function ($query) use ($staffRoles) {
                $query->whereNull('role')
                    ->orWhereNotIn('role', $staffRoles);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Preview helper for the staff UI.
     *
     * @return Collection<int, User>
     */
    public function previewUsers(Announcement $announcement, int $limit = 25): Collection
    {
        $ids = $this->resolveUserIds($announcement);
        if ($ids === []) {
            return collect();
        }

        return User::query()
            ->whereIn('id', array_slice($ids, 0, $limit))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'contact_no']);
    }
}
