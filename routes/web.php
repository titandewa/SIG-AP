<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PdfSignerController;

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
Route::get('/verify/{hash}', [DocumentController::class, 'verifyDocument'])->name('public.verify');
Route::get('/sign-pdf', [PdfSignerController::class, 'index']);
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

    Route::get('/dashboard', function () {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->role === 'aspri') {
            return redirect()->route('app.aspri.dashboard');
        }

        if ($user->role === 'petinggi') {
            return redirect()->route('app.petinggi.dashboard');
        }

        if ($user->role === 'dirut') {
            return redirect()->route('app.dirut.dashboard');
        }

        return redirect()->route('login');
    })->name('dashboard');

    Route::post('/app/document/{id}/comment', [DocumentController::class, 'addComment'])->name('document.comment');
    Route::post('/fcm/save-token', [App\Http\Controllers\FcmController::class, 'saveToken']);
    Route::get('/notifications/history', [App\Http\Controllers\NotificationHistoryController::class, 'index'])->name('notifications.history');
    
    // Route khusus Aspri
    Route::middleware(['role:aspri'])->group(function () {
        Route::post('/app/aspri/revisi/{id}', [DocumentController::class, 'uploadRevision'])->name('aspri.revisi');
        Route::post('/app/aspri/store', [DocumentController::class, 'store'])->name('aspri.store');
        Route::post('/app/aspri/reminder/{id}', [DocumentController::class, 'sendReminder'])->name('aspri.reminder');
        Route::get('/app/aspri/dashboard', [DocumentController::class, 'indexAspri'])->name('app.aspri.dashboard');
        Route::get('/app/aspri/riwayat', [DocumentController::class, 'riwayatAspri'])->name('aspri.riwayat');
        Route::get('/app/aspri/dokumen/{id}', [DocumentController::class, 'detailAspri'])->name('aspri.detail');
        Route::get('/app/aspri/dokumen/{id}/versi', [DocumentController::class, 'versionHistory'])->name('aspri.version-history');
    });

    // Route khusus Petinggi

    Route::middleware(['role:petinggi'])->group(function () {
        Route::get('/app/petinggi/dashboard', [DocumentController::class, 'indexPetinggi'])->name('app.petinggi.dashboard');
        Route::post('/app/petinggi/review/{id}', [DocumentController::class, 'reviewPetinggi'])->name('petinggi.review');
        Route::get('/app/petinggi/riwayat', [DocumentController::class, 'riwayatPetinggi'])->name('petinggi.riwayat');
    });

    // Route khusus Dirut
    Route::middleware(['role:dirut'])->group(function () {
        Route::get('/app/dirut/dashboard', [DocumentController::class, 'indexDirut'])->name('app.dirut.dashboard');
        Route::post('/app/dirut/sign/{id}', [DocumentController::class, 'signDirut'])->name('dirut.sign');
        Route::get('/app/dirut/signature', [DocumentController::class, 'showSignaturePage'])->name('dirut.signature.page');
        Route::post('/app/dirut/signature', [DocumentController::class, 'saveSignature'])->name('dirut.signature.save');
        Route::get('/app/dirut/ttd/{id}', [DocumentController::class, 'showTtdEditor'])->name('dirut.ttd.editor');
        Route::get('/app/dirut/riwayat', [DocumentController::class, 'riwayatDirut'])->name('dirut.riwayat');
    });

});

Route::get('/test-firebase', function () {
    $messaging = app('firebase.messaging');
    dd('Firebase connected!', $messaging);
});

require __DIR__.'/auth.php';