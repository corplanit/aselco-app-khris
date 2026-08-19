<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BillingUpload;
use App\Models\AccountLink;
use App\Models\TAccountRaw;
use Illuminate\Support\Facades\Storage;

class BillingUploadController extends Controller
{
    public function dashboard()
    {
        return view('pages.staff.dashboard');
    }
    public function create()
    {
        // You may want to limit this to only staff-accessible accounts
        $accounts = AccountLink::where('validated_by', '<>', null)->get();
        $billings = BillingUpload::with(['accountLink', 'uploader'])->latest()->get();

        return view('pages.staff.index', compact('accounts', 'billings'));
    }

    public function validation()
    {
        // You may want to limit this to only staff-accessible accounts
        $accounts = AccountLink::where('validated_by', '<>', null)->get();

        return view('pages.staff.accounts', compact('accounts'));
    }
    public function accounts()
    {
        try {
            $accounts = AccountLink::with('user')
                ->whereNull('validated_by')
                ->orderBy('validated_at', 'desc')
                ->get();

            // Load all t_accounts_raw entries for matching
            $rawAccounts = TAccountRaw::all();

            // Transform for DataTables
            $accounts->transform(function ($item) use ($rawAccounts) {
                $match = $rawAccounts->first(function ($raw) use ($item) {
                    return $raw->account_no == $item->account_number &&
                        strtoupper(trim($raw->customer)) == strtoupper(trim($item->owner_name));
                });

                $status = $match ? 'Matched' : 'No Match Found';

                return [
                    'id'             => $item->id,
                    'account_number' => $item->account_number,
                    'owner_name'     => $item->owner_name,
                    'validated_by'   => $item->validated_by,
                    'validated_at'   => optional($item->validated_at)->toDayDateTimeString(),
                    'user_name'      => $item->user->name ?? 'N/A',
                    'status'         => $status,
                    'created_at'     => optional($item->created_at)->toDayDateTimeString(),
                ];
            });

            return response()->json(['data' => $accounts], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function accounts_all()
    {
        try {
            $accounts = AccountLink::with('user')
                ->orderBy('validated_at', 'desc')
                ->get();

            // Load all t_accounts_raw entries for matching
            $rawAccounts = TAccountRaw::all();

            // Transform for DataTables
            $accounts->transform(function ($item) use ($rawAccounts) {
                $match = $rawAccounts->first(function ($raw) use ($item) {
                    return $raw->account_no == $item->account_number &&
                        strtoupper(trim($raw->customer)) == strtoupper(trim($item->owner_name));
                });

                $status = $match ? 'Matched' : 'No Match Found';

                return [
                    'account_number' => $item->account_number,
                    'owner_name'     => $item->owner_name,
                    'validated_by'   => $item->validated_by,
                    'validated_at'   => optional($item->validated_at)->toDayDateTimeString(),
                    'user_name'      => $item->user->name ?? 'N/A',
                    'status'         => $status,
                    'created_at'     => optional($item->created_at)->toDayDateTimeString(),
                ];
            });

            return response()->json(['data' => $accounts], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public static function list()
    {
        try {
            $billings = BillingUpload::with(['accountLink', 'uploader'])
                ->orderBy('billing_date', 'DESC')
                ->get();

            // Transform the data for DataTables compatibility
            $billings->transform(function ($item) {
                return [
                    'account_number' => $item->accountLink->account_number ?? '',
                    'owner_name'     => $item->accountLink->owner_name ?? '',
                    'billing_date'   => \Carbon\Carbon::parse($item->billing_date)->toFormattedDateString(),
                    'amount'         => number_format($item->amount, 2),
                    'file_path'      => $item->file_path,
                    'uploaded_by'    => $item->uploader->name ?? '',
                    'status'         => $item->status ?? 'Pending',
                    'created_at'     => $item->created_at->diffForHumans(),
                ];
            });

            return response()->json(['data' => $billings], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request)
    {
        $request->validate([
            'account_link_id' => 'required|exists:account_links,id',
            'amount' => 'required|numeric|min:0',
            'billing_date' => 'required|date',
            'pdf_file' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        $path = null;

        if ($request->hasFile('pdf_file')) {
            $file = $request->file('pdf_file');
            $filename = $file->getClientOriginalName();
            $storagePath = 'billing_pdfs/' . $filename;

            if (!Storage::disk('public')->exists($storagePath)) {
                $path = $file->storeAs('billing_pdfs', $filename, 'public');
            } else {
                $path = $storagePath;
            }
        }

        BillingUpload::create([
            'account_link_id' => $request->account_link_id,
            'file_path' => $path,
            'amount' => $request->amount,
            'billing_date' => $request->billing_date,
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Billing PDF uploaded successfully.');
    }
}
