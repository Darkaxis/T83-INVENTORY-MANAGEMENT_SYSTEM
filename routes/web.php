<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
})->name('login');

Route::get('dashboard', function () {
    return view('layouts.app');
})->name('dashboard');
Route::get('/register', function () {
    return view('register');
})->name('register');

Route::get('/stores', function () {
    return view('stores.index');
})->name('stores');
Route::get('/users', function () {
    return view('stores.index');
})->name('users');
