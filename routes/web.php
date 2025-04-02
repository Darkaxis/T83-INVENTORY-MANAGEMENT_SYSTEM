<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;

// Public routes
Route::domain('localhost')->group(function () {
    Route::get('/', function () {
        return view('login');
    })->name('login');

    Route::get('/register', function () {
        return view('register');
    })->name('register');
    
    // Google login (keep these on main domain)
    Route::get('auth/google', [LoginController::class, 'redirectToGoogle'])->name('login.google');
    Route::get('auth/google/callback', [LoginController::class, 'handleGoogleCallback']);
});

// Admin routes on subdomain
Route::domain('localhost.admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/stores', function () {
        return view('stores.index');
    })->name('stores');
    
    Route::get('/users', function () {
        return view('users.index');
    })->name('users');
    
    // Add other admin routes here
});

//logout route
Route::get('/logout', function () {
    Auth::logout();
    return redirect()->route('login');
})->name('logout');
// Route::get('/dashboard', function () {