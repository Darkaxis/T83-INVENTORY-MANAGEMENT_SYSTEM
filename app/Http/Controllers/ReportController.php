<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;

class ReportController extends Controller
{
    /**
     * Display the reports page (Pro tier only)
     */
    public function index(Request $request)
    {
        $store = Store::where('slug', $request->route('subdomain'))->firstOrFail();
        
        return view('reports.index', compact('store'));
    }
}