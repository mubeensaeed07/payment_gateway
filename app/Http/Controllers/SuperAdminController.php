<?php

namespace App\Http\Controllers;

use App\Mail\UserInvitationMail;
use App\Models\ExternalProvider;
use App\Models\Slab;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            ->with(['slabs', 'externalProvider'])
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

    /**
     * Get slabs for an admin
     */
    public function getSlabs($adminId)
    {
        $admin = User::where('role', 'admin')->findOrFail($adminId);
        $slabs = $admin->slabs;

        return response()->json([
            'success' => true,
            'slabs' => $slabs
        ]);
    }

    /**
     * Store or update slabs for an admin
     */
    public function storeSlabs(Request $request, $adminId)
    {
        $admin = User::where('role', 'admin')->findOrFail($adminId);

        $request->validate([
            'slabs' => 'required|array|min:1|max:6',
            'slabs.*.slab_number' => 'required|integer|min:1|max:6',
            'slabs.*.from_amount' => 'required|numeric|min:0',
            'slabs.*.to_amount' => 'nullable|numeric|min:0',
            'slabs.*.charge' => 'required|numeric|min:0',
        ]);

        // Additional validation: to_amount must be greater than from_amount
        foreach ($request->slabs as $index => $slab) {
            if ($slab['to_amount'] !== null && $slab['to_amount'] <= $slab['from_amount']) {
                return response()->json([
                    'success' => false,
                    'message' => "Slab " . ($index + 1) . ": To Amount must be greater than From Amount."
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Delete existing slabs for this admin
            Slab::where('admin_id', $adminId)->delete();

            // Create new slabs
            foreach ($request->slabs as $slabData) {
                Slab::create([
                    'admin_id' => $adminId,
                    'slab_number' => $slabData['slab_number'],
                    'from_amount' => $slabData['from_amount'],
                    'to_amount' => $slabData['to_amount'] ?? null,
                    'charge' => $slabData['charge'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Slabs saved successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save slabs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get external provider for an admin
     */
    public function getExternalProvider($adminId)
    {
        $admin = User::where('role', 'admin')->findOrFail($adminId);
        $externalProvider = $admin->externalProvider;

        return response()->json([
            'success' => true,
            'external_provider' => $externalProvider
        ]);
    }

    /**
     * Store or update external provider for an admin
     */
    public function storeExternalProvider(Request $request, $adminId)
    {
        $admin = User::where('role', 'admin')->findOrFail($adminId);

        $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'bill_enquiry_url' => 'required|url|max:500',
            'bill_payment_url' => 'required|url|max:500',
        ]);

        DB::beginTransaction();
        try {
            ExternalProvider::updateOrCreate(
                ['admin_id' => $adminId],
                [
                    'username' => $request->username,
                    'password' => $request->password, // Will be encrypted by model
                    'bill_enquiry_url' => $request->bill_enquiry_url,
                    'bill_payment_url' => $request->bill_payment_url,
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'External provider credentials saved successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save external provider credentials: ' . $e->getMessage()
            ], 500);
        }
    }
}

