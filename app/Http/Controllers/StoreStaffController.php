<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Staff\StoreStaffMemberRequest;
use App\Http\Requests\Staff\UpdateStaffMemberRequest;
use App\Http\Requests\Staff\UpdateStaffPasswordRequest;

class StoreStaffController extends Controller
{
    /**
     * Get the current store from request or auth user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Models\Store
     */
    protected function getStore(Request $request)
    {
        // If store is set in request (from middleware)
        if (isset($request->store) && $request->store instanceof Store) {
            return $request->store;
        }
        
        // If user is admin and store_id is provided in the request
        if (Auth::user()->role === 'admin' && $request->has('store_id')) {
            return Store::findOrFail($request->store_id);
        }
        
        // If user belongs to a store
        if (Auth::user()->store_id) {
            return Store::findOrFail(Auth::user()->store_id);
        }
        
        // If using route model binding
        if ($request->route('store')) {
            return $request->route('store');
        }
        
        abort(404, 'Store not found');
    }

    /**
     * Display a listing of the staff for the current store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $store = $this->getStore($request);
        $staff = User::where('store_id', $store->id)
                    ->where('role', '!=', 'admin')
                    ->get();
        
        return view('stores.staff.index', compact('store', 'staff'));
    }

    /**
     * Show the form for creating a new staff member.
     */
    public function create(Request $request)
    {
        $store = $this->getStore($request);
        return view('staff.create', compact('store'));
    }

    /**
     * Store a newly created staff member in storage.
     */
    public function store(StoreStaffMemberRequest $request)
    {
        $store = $this->getStore($request);
        
        // Validation is now handled by the form request
        $validated = $request->validated();
        
        // Create new user with store association
        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->role = $validated['role'];
        $user->phone = $validated['phone'] ?? null;
        $user->position = $validated['position'] ?? null;
        $user->store_id = $store->id;
        $user->save();
        
        return redirect()->route('staff.index', ['store_id' => $store->id])
            ->with('success', 'Staff member created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $store = $this->getStore($request);
        $staff = User::where('id', $id)
                    ->where('store_id', $store->id)
                    ->firstOrFail();
                    
        return view('stores.staff.show', compact('store', 'staff'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $store = $this->getStore($request);
        $staff = User::where('id', $id)
                    ->where('store_id', $store->id)
                    ->firstOrFail();
                    
        return view('stores.staff.edit', compact('store', 'staff'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStaffMemberRequest $request, $id)
    {
        $store = $this->getStore($request);
        $staff = User::where('id', $id)
                    ->where('store_id', $store->id)
                    ->firstOrFail();
        
        // Validation is now handled by the form request
        $validated = $request->validated();
        
        $staff->name = $validated['name'];
        $staff->email = $validated['email'];
        $staff->role = $validated['role'];
        $staff->phone = $validated['phone'] ?? $staff->phone;
        $staff->position = $validated['position'] ?? $staff->position;
        
        if (isset($validated['status'])) {
            $staff->status = $validated['status'];
        }
        
        $staff->save();
        
        return redirect()->route('staff.show', ['id' => $staff->id, 'store_id' => $store->id])
            ->with('success', 'Staff member updated successfully');
    }

    /**
     * Show form to reset password.
     */
    public function resetPassword(Request $request, $id)
    {
        $store = $this->getStore($request);
        $staff = User::where('id', $id)
                     ->where('store_id', $store->id)
                     ->firstOrFail();
        
        return view('stores.staff.reset-password', compact('store', 'staff'));
    }
    
    /**
     * Update the staff member's password.
     */
    public function updatePassword(UpdateStaffPasswordRequest $request, $id)
    {
        $store = $this->getStore($request);
        $staff = User::where('id', $id)
                     ->where('store_id', $store->id)
                     ->firstOrFail();
        
        // Validation is handled by the form request
        $validated = $request->validated();
        
        $staff->password = Hash::make($validated['password']);
        $staff->save();
        
        return redirect()->route('staff.show', ['id' => $staff->id, 'store_id' => $store->id])
            ->with('success', 'Password updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $store = $this->getStore($request);
        $staff = User::where('id', $id)
                    ->where('store_id', $store->id)
                    ->firstOrFail();
        
        // Prevent deleting yourself
        if (Auth::id() == $staff->id) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account');
        }
        
        $staff->delete();
        
        return redirect()->route('staff.index', ['store_id' => $store->id])
            ->with('success', 'Staff member deleted successfully');
    }
}