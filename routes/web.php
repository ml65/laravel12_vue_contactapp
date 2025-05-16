<?php

use App\Models\Contact;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

require __DIR__.'/auth.php';

// Защищенный маршрут главной страницы
Route::get('/', function () {
    return Inertia::render('Contacts', [
        'contacts' => Contact::all(),
    ]);
})->middleware('auth');

// Страница информации о проекте
Route::get('/information', function () {
    return Inertia::render('Information');
});