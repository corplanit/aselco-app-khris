<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AccountLink;
use App\Models\TAccountRaw;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AccountLinkController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_number' => 'required|numeric',
            'owner_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        AccountLink::create([
            'user_id' => Auth::id(),
            'account_number' => $request->account_number,
            'owner_name' => strtoupper($request->owner_name),
            'validated_at' => null,
            'validated_by' => null,
        ]);

        return redirect()->back()->with('success', 'Account successfully linked and validated.');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:account_links,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $account = AccountLink::findOrFail($request->id);

        // Update the model
        $account->update([
            'validated_at' => Carbon::now(),
            'validated_by' => Auth::user()->name,
        ]);

        // Update the related raw account
        TAccountRaw::where('account_no', $account->account_number)->update(['user_id' => $account->user_id, 'status' => 'Linked']);

        return redirect()->back()->with('success', 'Account successfully linked and validated.');
    }
}
