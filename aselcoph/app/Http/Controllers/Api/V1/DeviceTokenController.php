<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['nullable', 'string', 'max:32'],
        ]);

        $token = trim($validated['token']);
        $platform = isset($validated['platform']) ? strtolower(trim($validated['platform'])) : null;

        // One physical device token belongs to one user — reassign if reused after re-login.
        DeviceToken::query()->where('token', $token)->delete();

        $device = DeviceToken::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'token' => $token,
            ],
            [
                'platform' => $platform,
            ]
        );

        return response()->json([
            'message' => 'Device token registered.',
            'device' => [
                'id' => $device->id,
                'platform' => $device->platform,
                'updated_at' => $device->updated_at?->toIso8601String(),
            ],
        ], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:512'],
        ]);

        DeviceToken::query()
            ->where('user_id', $request->user()->id)
            ->where('token', trim($validated['token']))
            ->delete();

        return response()->json([
            'message' => 'Device token removed.',
        ]);
    }
}
