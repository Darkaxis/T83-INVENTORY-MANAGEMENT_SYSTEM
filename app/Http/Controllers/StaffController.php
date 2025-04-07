<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Store;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    // Staff management for a specific store
    public function index(Request $request)
    {
        $store = $request->store;
        $staff = $store->users()->paginate(15);
        return view('stores.staff.index', compact('store', 'staff'));
    }
    
    // Other resource methods...
}