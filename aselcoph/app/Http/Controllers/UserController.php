<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function store(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:User,Administrator,Customer Service,Content Manager',
        ]);

        // Generate random password
        $rawPassword = Str::random(10);

        // Create user
        $newUser = $user->create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($rawPassword),
        ]);

        // Send email with the generated password
        Mail::raw("Welcome to the system.\n\nYour login credentials:\nEmail: {$newUser->email}\nPassword: {$rawPassword}", function ($message) use ($newUser) {
            $message->to($newUser->email)->subject('Your Account Details');
        });

        return redirect()->back()->with('success', 'Account created and credentials sent to email.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'role' => 'required|in:User,Administrator,Customer Service,Content Manager',
        ]);

        $user->update($request->only('name', 'email', 'role'));
        return redirect()->back()->with('success', 'Account Updated successfully.');
    }

    public function datatable(Request $request)
    {
        return response()->json([
            'data' => User::select(['id', 'name', 'email', 'role', 'created_at', 'email_verified_at'])
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at->format('Y-m-d'),
                        'status' => 'Active', // or logic based on your system
                    ];
                }),
        ]);
    }
}
