<?php

namespace App\Http\Controllers;

use App\Mail\UserInvitationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    /**
     * Show dashboard
     */
    public function dashboard()
    {
        $admins = User::where('role', 'admin')->count();
        $resellers = User::where('role', 'reseller')->count();
        
        return view('superadmin.dashboard', compact('admins', 'resellers'));
    }

    /**
     * Show admins management page
     */
    public function admins(Request $request)
    {
        $users = User::where('role', 'admin')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('superadmin.admins', compact('users'));
    }

    /**
     * Show resellers management page
     */
    public function resellers(Request $request)
    {
        $users = User::where('role', 'reseller')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('superadmin.resellers', compact('users'));
    }

    /**
     * Create new admin or reseller
     */
    public function createUser(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,reseller',
            'prefix_number' => [
                'nullable',
                'string',
                'max:10',
                'regex:/^[0-9]{4,6}$/',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->role === 'admin' && $value) {
                        $exists = User::where('role', 'admin')
                            ->where('prefix_number', $value)
                            ->exists();
                        if ($exists) {
                            $fail('The prefix number has already been taken by another admin.');
                        }
                    }
                },
            ],
        ];

        $request->validate($rules);

        $invitationToken = Str::random(60);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'prefix_number' => $request->prefix_number,
            'invitation_token' => $invitationToken,
            'invited_at' => now(),
        ]);

        // Send invitation email
        $loginUrl = route('google.redirect') . '?token=' . $invitationToken;
        Mail::to($user->email)->send(new UserInvitationMail($user, $loginUrl));

        // Redirect to the appropriate page based on role
        if ($request->role === 'admin') {
            return redirect()->route('superadmin.admins')
                ->with('success', 'Admin created successfully. Invitation email sent.');
        } else {
            return redirect()->route('superadmin.resellers')
                ->with('success', 'Reseller created successfully. Invitation email sent.');
        }
    }

    /**
     * Update admin prefix number
     */
    public function updatePrefix(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (!$user->isAdmin()) {
            return redirect()->back()->with('error', 'Can only update prefix for admins.');
        }

        $rules = [
            'prefix_number' => [
                'nullable',
                'string',
                'max:10',
                'regex:/^[0-9]{4,6}$/',
                function ($attribute, $value, $fail) use ($id) {
                    if ($value) {
                        $exists = User::where('role', 'admin')
                            ->where('prefix_number', $value)
                            ->where('id', '!=', $id)
                            ->exists();
                        if ($exists) {
                            $fail('The prefix number has already been taken by another admin.');
                        }
                    }
                },
            ],
        ];

        $request->validate($rules);

        $user->update([
            'prefix_number' => $request->prefix_number,
        ]);

        return redirect()->route('superadmin.admins')
            ->with('success', 'Admin prefix number updated successfully.');
    }

    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Cannot delete superadmin.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}

