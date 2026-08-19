<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAccountLinkRequest;
use App\Http\Resources\Api\V1\AccountLinkResource;
use App\Http\Resources\Api\V1\LinkedAccountResource;
use App\Models\AccountLink;
use App\Models\TAccountRaw;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class MembershipController extends Controller
{
    public const MAX_ACCOUNT_LINKS = 10;

    /**
     * Dashboard unlocks after the member has submitted at least 1 account link
     * (up to 2). Staff validation is not required to enter the dashboard.
     */
    public function status(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $pendingCount = AccountLink::query()
            ->where('user_id', $userId)
            ->whereNull('validated_at')
            ->count();

        $validatedCount = AccountLink::query()
            ->where('user_id', $userId)
            ->whereNotNull('validated_at')
            ->count();

        $linkRequestCount = $pendingCount + $validatedCount;

        $linkedCount = 0;
        try {
            $linkedCount = TAccountRaw::query()
                ->where('user_id', $userId)
                ->count();
        } catch (Throwable) {
            // Raw ledger table may be unavailable; account_links alone unlocks the app.
        }

        $hasAnyLinkRequest = $linkRequestCount > 0;
        $hasLinkedAccount = $linkedCount > 0;

        // At least 1 submitted account link (or an already-linked raw account) unlocks dashboard.
        $needsStepper = ! $hasAnyLinkRequest && ! $hasLinkedAccount;

        return response()->json([
            'needs_membership_stepper' => $needsStepper,
            'has_pending_link' => $pendingCount > 0,
            'has_validated_link' => $validatedCount > 0 || $hasLinkedAccount,
            'pending_count' => $pendingCount,
            'validated_count' => $validatedCount,
            'link_count' => $linkRequestCount,
            'max_links' => self::MAX_ACCOUNT_LINKS,
            'can_add_another_link' => $linkRequestCount < self::MAX_ACCOUNT_LINKS,
        ]);
    }

    public function privacy(): JsonResponse
    {
        return response()->json([
            'title' => 'Data Privacy Policy',
            'summary' => 'In compliance with the Data Privacy Act of 2012 (R.A. 10173).',
            'body' => 'ASELCO collects and processes your account number and owner name to verify and link your electric service account to your member portal profile. Your information will be validated against cooperative records. By continuing, you consent to this processing for account linking and related member services.',
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $links = AccountLink::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => AccountLinkResource::collection($links)->resolve(),
        ]);
    }

    public function store(StoreAccountLinkRequest $request): JsonResponse
    {
        $userId = $request->user()->id;
        $accountNumber = $request->string('account_number')->toString();
        $ownerName = strtoupper(trim($request->string('owner_name')->toString()));

        $existingCount = AccountLink::query()
            ->where('user_id', $userId)
            ->count();

        if ($existingCount >= self::MAX_ACCOUNT_LINKS) {
            return response()->json([
                'message' => 'You may link up to '.self::MAX_ACCOUNT_LINKS.' electric accounts.',
                'errors' => [
                    'account_number' => ['You may link up to '.self::MAX_ACCOUNT_LINKS.' electric accounts.'],
                ],
            ], 422);
        }

        $duplicate = AccountLink::query()
            ->where('user_id', $userId)
            ->where('account_number', $accountNumber)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'message' => 'You already submitted a link request for this account number.',
                'errors' => [
                    'account_number' => ['You already submitted a link request for this account number.'],
                ],
            ], 422);
        }

        $link = AccountLink::create([
            'user_id' => $userId,
            'account_number' => $accountNumber,
            'owner_name' => $ownerName,
            'validated_at' => null,
            'validated_by' => null,
        ]);

        $linkCount = $existingCount + 1;

        return response()->json([
            'message' => 'Account link request submitted. Pending staff validation.',
            'account_link' => (new AccountLinkResource($link))->resolve(),
            'link_count' => $linkCount,
            'max_links' => self::MAX_ACCOUNT_LINKS,
            'can_add_another_link' => $linkCount < self::MAX_ACCOUNT_LINKS,
            'needs_membership_stepper' => false,
        ], 201);
    }

    public function linkedAccounts(Request $request): JsonResponse
    {
        try {
            $accounts = TAccountRaw::query()
                ->where('user_id', $request->user()->id)
                ->get();
        } catch (Throwable) {
            $accounts = collect();
        }

        return response()->json([
            'data' => LinkedAccountResource::collection($accounts)->resolve(),
        ]);
    }
}
