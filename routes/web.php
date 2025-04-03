<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Auth;

// Public/Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return view('login');
    })->name('login');
    
    // Add POST route for manual login
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    
    Route::get('/register', function () {
        return view('register');
    })->name('register');
});

// Google authentication routes (accessible to anyone)
Route::get('auth/google', [LoginController::class, 'redirectToGoogle'])->name('login.google');
Route::get('auth/google/callback', [LoginController::class, 'handleGoogleCallback']);

// Admin routes with authentication middleware
Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/stores', function () {
        return view('stores.index');
    })->name('stores');
    
    Route::get('/users', function () {
        return view('users.index');
    })->name('users');
});

// Logout route (use the controller method)
Route::middleware('auth')->get('/logout', [LoginController::class, 'logout'])->name('logout');