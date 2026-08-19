<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerComplaint;
use Illuminate\Support\Facades\Auth;

class CustomerComplaintController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_number' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'contact' => 'nullable|string|max:20',
            'complaint' => 'required|string|max:1000',
            'attachment' => 'nullable|image|max:2048', // optional image validation
        ]);

        // Handle file upload if present
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('complaint_attachments', 'public');
            $validated['attachment'] = $path;
        }

        $validated['user_id'] = Auth::user()->id;

        CustomerComplaint::create($validated);

        return redirect()->back()->with('success', 'Complaint submitted successfully.');
    }

    public function complaint()
    {
        return view('pages.staff.complaint');
    }

    public function list()
    {
        try {
            $complaints = CustomerComplaint::where('user_id', Auth::user()->id)
                ->latest()
                ->get();

            $complaints->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'account_number' => $item->account_number,
                    'name' => $item->name,
                    'contact' => $item->contact ?? '—',
                    'complaint' => $item->complaint,
                    'attachment' => $item->attachment,
                    'status' => $item->status,
                    'created_at' => $item->created_at->format('M d, Y h:i A'),
                ];
            });

            return response()->json(['data' => $complaints], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function list_all()
    {
        try {
            $complaints = CustomerComplaint::latest()->get();

            $complaints->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'account_number' => $item->account_number,
                    'name' => $item->name,
                    'contact' => $item->contact ?? '—',
                    'complaint' => $item->complaint,
                    'attachment' => $item->attachment,
                    'status' => $item->status,
                    'created_at' => $item->created_at->format('M d, Y h:i A'),
                ];
            });

            return response()->json(['data' => $complaints], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:customer_complaints,id',
            'status' => 'required|string|in:Pending,In Progress,Resolved,Rejected',
        ]);

        $complaint = CustomerComplaint::find($request->id);
        $complaint->status = $request->status;
        $complaint->save();

        return response()->json(['message' => 'Status updated successfully.']);
    }
}
