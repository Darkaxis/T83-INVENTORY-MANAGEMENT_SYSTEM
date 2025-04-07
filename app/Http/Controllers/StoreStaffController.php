<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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

    // Update all other methods to use $this->getStore($request) instead of $request->store

    /**
     * Show the form for creating a new staff member.
     */
    public function create(Request $request)
    {
        $store = $this->getStore($request);
        return view('stores.staff.create', compact('store'));
    }

    /**
     * Store a newly created staff member in storage.
     */
    public function store(Request $request)
    {
        $store = $this->getStore($request);
        
        // Rest of the method remains the same
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', 'string', Rule::in(['manager', 'staff'])],
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:100',
        ]);
        
        // Create new user with store association
        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->role = $validated['role'];
        $user->phone = $request->phone;
        $user->position = $request->position;
        $user->store_id = $store->id;
        $user->save();
        
        return redirect()->route('staff.index', ['store_id' => $store->id])
            ->with('success', 'Staff member created successfully');
    }

    // Update all other methods similarly
    // For example:

    public function resetPassword(Request $request, $id)
    {
        $store = $this->getStore($request);
        $staff = User::where('id', $id)
                     ->where('store_id', $store->id)
                     ->firstOrFail();
        
        return view('stores.staff.reset-password', compact('store', 'staff'));
    }
    
    public function updatePassword(Request $request, $id)
    {
        $store = $this->getStore($request);
        $staff = User::where('id', $id)
                     ->where('store_id', $store->id)
                     ->firstOrFail();
        
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $staff->password = Hash::make($validated['password']);
        $staff->save();
        
        return redirect()->route('staff.show', ['id' => $staff->id, 'store_id' => $store->id])
            ->with('success', 'Password updated successfully');
    }
}