<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DocumentController;

/*
|--------------------------------------------------------------------------
| 1. ROUTE DEMO & VERIFIKASI PUBLIK (Bahan Presentasi Atasan)
|--------------------------------------------------------------------------
*/
Route::prefix('demo')->group(function () {
    Route::get('/aspri', [DocumentController::class, 'indexDemoAspri'])->name('demo.aspri');
    
    Route::get('/petinggi', function () {
        return view('demo.petinggi');
    })->name('demo.petinggi');
    
    Route::get('/dirut', function () {
        return view('demo.dirut');
    })->name('demo.dirut');

    Route::get('/verify', function () {
        return view('demo.verify');
    })->name('demo.verify');
});

// Public Verification (Bisa diakses publik tanpa login)
Route::get('/verify/{hash?}', [DocumentController::class, 'verifyDocument'])->name('public.verify');


/*
|--------------------------------------------------------------------------
| 2. ROUTE APLIKASI UTAMA (Sistem Eksperimen Real)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

// Route untuk mengecek data user yang sedang login saat ini
Route::get('/debug-user', function () {
    if (Auth::check()) {
        return response()->json(Auth::user());
    }
    return 'Belum ada user yang login.';
});

// Route untuk memaksa hapus sesi login (force logout)
Route::get('/force-logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
});

Route::middleware(['auth'])->group(function () {

    // Route khusus Aspri
    Route::middleware(['role:aspri'])->group(function () {
        Route::post('/app/aspri/store', [DocumentController::class, 'store'])->name('aspri.store');
        Route::get('/app/aspri/dashboard', [DocumentController::class, 'indexAspri'])->name('app.aspri.dashboard');
    });

    Route::middleware(['role:petinggi'])->group(function () {
        Route::get('/app/petinggi/dashboard', [DocumentController::class, 'indexPetinggi'])
            ->name('app.petinggi.dashboard');
        Route::post('/app/petinggi/review/{id}', [DocumentController::class, 'reviewPetinggi'])->name('petinggi.review');
    });

    // Route khusus Dirut
    Route::middleware(['role:dirut'])->group(function () {
        Route::get('/app/dirut/dashboard', [DocumentController::class, 'indexDirut'])->name('app.dirut.dashboard');
        Route::post('/app/dirut/sign/{id}', [DocumentController::class, 'signDirut'])->name('dirut.sign');
    });

});

require __DIR__.'/auth.php';