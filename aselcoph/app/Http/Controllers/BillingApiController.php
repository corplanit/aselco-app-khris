<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BillingApiController extends Controller
{
    private $loginUrl = 'https://paybills.aselcoinc.net/index.php/api/login';
    private $billingUrl = 'https://paybills.aselcoinc.net/index.php/api/getBillingData';

    private $clientKey = 'ea7199b48aa8adc6364598f04b4001e761d53ad537a4fa1a9dd06e4024bef6d2';

    public function getBillingData(Request $request)
    {
        $accountNumber = $request->input('account_number');
        if (!$accountNumber) {
            return response()->json(['error' => 'Account number is required'], 422);
        }

        // Step 1: Authenticate and get Bearer token
        $authResponse = Http::withHeaders(['Accept' => 'application/json'])
            ->post($this->loginUrl, [
                'client_key' => $this->clientKey
            ]);

        if (!$authResponse->ok() || !isset($authResponse['access_token'])) {
            return response()->json(['error' => 'Authentication failed', 'response' => $authResponse->json()], 401);
        }

        $bearerToken = $authResponse['access_token'];

        // Step 2: Use token to fetch billing data
        $billingResponse = Http::withToken($bearerToken)
            ->post($this->billingUrl, [
                'AccountNumber' => $accountNumber
            ]);

        if (!$billingResponse->ok()) {
            return response()->json(['error' => 'Failed to fetch billing data', 'response' => $billingResponse->json()], 500);
        }

        return response()->json($billingResponse->json());
    }
}
