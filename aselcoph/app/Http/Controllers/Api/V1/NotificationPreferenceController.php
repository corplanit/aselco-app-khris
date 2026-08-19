<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $prefs = NotificationPreference::forUser($request->user()->id);

        return response()->json([
            'billing' => (bool) $prefs->billing,
            'service' => (bool) $prefs->service,
            'alert' => (bool) $prefs->alert,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'billing' => ['sometimes', 'boolean'],
            'service' => ['sometimes', 'boolean'],
            'alert' => ['sometimes', 'boolean'],
        ]);

        $prefs = NotificationPreference::forUser($request->user()->id);
        $prefs->fill($validated);
        $prefs->save();

        return response()->json([
            'billing' => (bool) $prefs->billing,
            'service' => (bool) $prefs->service,
            'alert' => (bool) $prefs->alert,
        ]);
    }
}
