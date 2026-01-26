<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/catalog', function () {
    return Inertia::render('Catalog');
})->name('catalog');

Route::get('/atelier', function () {
    return Inertia::render('Atelier');
})->name('atelier');

Route::get('/blog', function () {
    return Inertia::render('Blog');
})->name('blog');

Route::get('/contacts', function () {
    return Inertia::render('Contacts');
})->name('contacts');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
