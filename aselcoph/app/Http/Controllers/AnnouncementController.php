<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use App\Services\AnnouncementAudienceResolver;
use App\Services\AnnouncementPublisher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::query()
            ->with('creator:id,name,email')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('pages.staff.announcements.index', compact('announcements'));
    }

    public function create(): View
    {
        $users = User::query()
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'name', 'email']);

        return view('pages.staff.announcements.create', compact('users'));
    }

    public function store(Request $request, AnnouncementPublisher $publisher): RedirectResponse
    {
        $validated = $this->validateAnnouncement($request);
        $action = $request->input('action', 'draft'); // draft | publish

        $announcement = Announcement::query()->create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'category' => $validated['category'],
            'audience_type' => $validated['audience_type'],
            'audience_user_ids' => $validated['audience_type'] === Announcement::AUDIENCE_USERS
                ? array_values($validated['audience_user_ids'] ?? [])
                : null,
            'meter_numbers' => $validated['audience_type'] === Announcement::AUDIENCE_METER
                ? $this->parseMeterList($validated['meter_numbers'] ?? '')
                : null,
            'status' => Announcement::STATUS_DRAFT,
            'created_by' => Auth::id(),
        ]);

        if ($action === 'publish') {
            $result = $publisher->publish($announcement);

            return redirect()
                ->route('announcements.index')
                ->with(
                    'success',
                    "Announcement published. Sent to {$result['sent']} of {$result['audience']} audience member(s)."
                );
        }

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Announcement saved as draft.');
    }

    public function show(Announcement $announcement, AnnouncementAudienceResolver $resolver): View
    {
        $preview = $resolver->previewUsers($announcement, 50);
        $audienceCount = count($resolver->resolveUserIds($announcement));

        return view('pages.staff.announcements.show', compact('announcement', 'preview', 'audienceCount'));
    }

    public function publish(Announcement $announcement, AnnouncementPublisher $publisher): RedirectResponse
    {
        if ($announcement->isPublished()) {
            return redirect()
                ->route('announcements.show', $announcement)
                ->with('success', 'Already published.');
        }

        $result = $publisher->publish($announcement);

        return redirect()
            ->route('announcements.show', $announcement)
            ->with(
                'success',
                "Published. Sent to {$result['sent']} of {$result['audience']} audience member(s)."
            );
    }

    /**
     * Preview how many users a draft audience would hit (AJAX).
     */
    public function previewAudience(Request $request, AnnouncementAudienceResolver $resolver): JsonResponse
    {
        $validated = $request->validate([
            'audience_type' => ['required', 'in:all,users,meter'],
            'audience_user_ids' => ['nullable', 'array'],
            'audience_user_ids.*' => ['integer'],
            'meter_numbers' => ['nullable', 'string'],
        ]);

        $temp = new Announcement([
            'audience_type' => $validated['audience_type'],
            'audience_user_ids' => $validated['audience_user_ids'] ?? [],
            'meter_numbers' => $this->parseMeterList($validated['meter_numbers'] ?? ''),
        ]);

        $ids = $resolver->resolveUserIds($temp);
        $preview = $resolver->previewUsers($temp, 15);

        return response()->json([
            'count' => count($ids),
            'preview' => $preview->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])->values(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAnnouncement(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:5000'],
            'category' => ['required', 'in:billing,service,alert'],
            'audience_type' => ['required', 'in:all,users,meter'],
            'audience_user_ids' => ['nullable', 'array'],
            'audience_user_ids.*' => ['integer', 'exists:users,id'],
            'meter_numbers' => ['nullable', 'string', 'max:2000'],
        ], [
            'audience_type.in' => 'Choose All members, Specific users, or Meter number(s).',
        ]);

        if ($validated['audience_type'] === Announcement::AUDIENCE_USERS
            && empty($validated['audience_user_ids'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'audience_user_ids' => 'Select at least one user.',
            ]);
        }

        if ($validated['audience_type'] === Announcement::AUDIENCE_METER
            && trim((string) ($validated['meter_numbers'] ?? '')) === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'meter_numbers' => 'Enter at least one meter number.',
            ]);
        }

        return $validated;
    }

    /**
     * @return list<string>
     */
    private function parseMeterList(string $raw): array
    {
        $parts = preg_split('/[\s,;]+/', $raw) ?: [];

        return collect($parts)
            ->map(fn ($part) => trim((string) $part))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
