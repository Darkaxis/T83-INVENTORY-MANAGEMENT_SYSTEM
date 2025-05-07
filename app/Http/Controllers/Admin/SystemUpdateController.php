<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\SystemUpdate;

class SystemUpdateController extends Controller
{
    public function index()
    {
        $updates = SystemUpdate::orderByDesc('created_at')->paginate(10);
        $currentVersion = config('app.version');
        
        return view('admin.system.updates', compact('updates', 'currentVersion'));
    }

    public function check()
    {
        Artisan::call('update:check');
        return redirect()->route('admin.system.updates')->with('success', 'Update check initiated.');
    }

    public function download($id)
    {
        $update = SystemUpdate::findOrFail($id);
        
        Artisan::call('update:download', [
            'version' => $update->version
        ]);
        
        return redirect()->route('admin.system.updates')->with('success', 'Update download initiated.');
    }

    public function install($id)
    {
        $update = SystemUpdate::findOrFail($id);
        
        Artisan::call('update:install', [
            'version' => $update->version
        ]);
        
        return redirect()->route('admin.system.updates')->with('success', 'Update installation initiated.');
    }
}
