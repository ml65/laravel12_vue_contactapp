<?php
use App\Models\Contact;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Contacts', [
        'contacts' => Contact::all(),
    ]);
});