<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarEventController extends Controller
{
    public static function view()
    {
        return view('pages.staff.calendar');
    }

    public function index()
    {
        date_default_timezone_set('Asia/Manila');

        return CalendarEvent::where('user_id', Auth::id())
            ->orWhere(function ($query) {
                $query->whereNotNull('shared_with')->whereJsonContains('shared_with', Auth::id());
            })
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start_time' => $event->start_time?->toIso8601String(),
                    'end_time' => $event->end_time?->toIso8601String(),
                    'description' => $event->description,
                    'meeting_link' => $event->meeting_link,
                    'is_appointment' => $event->is_appointment,
                    'color' => $event->color,
                    'text_color' => $event->text_color,
                ];
            });
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'color' => 'nullable|string',
            'text_color' => 'nullable|string',
            'meeting_link' => 'nullable|url',
            'is_appointment' => 'boolean',
            'shared_with' => 'nullable|array',
        ]);

        $data['user_id'] = Auth::id();
        $data['start_time'] = Carbon::parse($data['start_time'])->setTimezone('Asia/Manila');
        $data['end_time'] = isset($data['end_time']) ? Carbon::parse($data['end_time'])->setTimezone('Asia/Manila') : null;

        return CalendarEvent::create($data);
    }

    public function update(Request $request, CalendarEvent $event)
    {
        if ($event->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->only(['title', 'description', 'start_time', 'end_time', 'color', 'meeting_link', 'is_appointment', 'shared_with']);

        if (isset($data['start_time'])) {
            $data['start_time'] = Carbon::parse($data['start_time'])->setTimezone('Asia/Manila');
        }
        if (isset($data['end_time'])) {
            $data['end_time'] = Carbon::parse($data['end_time'])->setTimezone('Asia/Manila');
        }

        $event->update(array_filter($data, fn($v) => !is_null($v)));

        return response()->json($event);
    }

    public function destroy(CalendarEvent $event)
    {
        if ($event->user_id !== Auth::user()->id) {
            abort(403);
        }
        $event->delete();
        return response()->json(['message' => 'Event deleted']);
    }
}
