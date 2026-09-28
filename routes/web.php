<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Aspri\SkController as AspriSkController;
use App\Http\Controllers\Petinggi\SkController as PetinggiSkController;
use App\Http\Controllers\Dirut\SkController as DirutSkController;
use App\Http\Controllers\PdfSignerController;
use App\Http\Controllers\NotificationHistoryController;
use App\Http\Controllers\Admin\AkunController;
use App\Http\Controllers\Auth\ForcePasswordController;

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

// FCM - Simpan token (bisa diakses tanpa login, untuk halaman login)
Route::post('/fcm/save-token', [App\Http\Controllers\FcmController::class, 'saveToken']);

// FCM - Kirim notifikasi test (bisa diakses tanpa login, untuk halaman login)
Route::post('/fcm/test-notification', function () {
    $request = request();
    $token = $request->input('token');

    if (!$token) {
        return response()->json(['status' => 'error', 'message' => 'Token tidak ditemukan.'], 400);
    }

    try {
        $messaging = app('firebase.messaging');
        $message = \Kreait\Firebase\Messaging\CloudMessage::new()->withData([
            'title' => 'Tes Notifikasi Push',
            'body' => 'Notifikasi push berhasil dikirim dari SIGAP!',
            'type' => 'test_notification',
            'url' => '/',
        ]);

        $messaging->send($message->withToken($token));

        return response()->json(['status' => 'success', 'message' => 'Notifikasi test berhasil dikirim!']);
    } catch (\Throwable $e) {
        return response()->json(['status' => 'error', 'message' => 'Gagal mengirim: ' . $e->getMessage()], 500);
    }
});
/*
|--------------------------------------------------------------------------
| 2. ROUTE APLIKASI UTAMA (Sistem Eksperimen Real)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
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

Route::post('/reset-forced-password', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'resetForced'])->name('password.reset-forced');

Route::get('/dokumen/{id}/download/{type}', [DocumentController::class, 'download'])
    ->name('document.download')
    ->whereIn('type', ['signed', 'pdf']);

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

        if ($user->role === 'admin') {
            return redirect()->route('app.admin.dashboard');
        }

        return redirect()->route('login');
    })->name('dashboard');

    Route::post('/app/document/{id}/comment', [DocumentController::class, 'addComment'])->name('document.comment');
    Route::get('/app/document/{id}/comments', [DocumentController::class, 'fetchComments'])->name('document.comments.fetch');
    Route::post('/app/document/{id}/comments/read', [DocumentController::class, 'markCommentsRead'])->name('document.comments.read');
    Route::get('/app/documents/unread-counts', [DocumentController::class, 'unreadCommentCounts'])->name('document.comments.unread-counts');

    Route::get('/notifications/history', [App\Http\Controllers\NotificationHistoryController::class, 'index'])->name('notifications.history');
    Route::post('/notifications/{id}/read', [NotificationHistoryController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/clear', [NotificationHistoryController::class, 'clearAll'])->name('notifications.clear');

    // Route khusus Aspri
    Route::middleware(['role:aspri'])->group(function () {
        Route::post('/app/aspri/revisi/{id}', [AspriSkController::class, 'uploadRevision'])->name('aspri.revisi');
        Route::post('/app/aspri/store', [AspriSkController::class, 'store'])->name('aspri.store');
        Route::post('/app/aspri/reminder/{id}', [DocumentController::class, 'sendReminder'])->name('aspri.reminder');
        Route::get('/app/aspri/dashboard', [AspriSkController::class, 'index'])->name('app.aspri.dashboard');
        Route::get('/app/aspri/riwayat', [AspriSkController::class, 'riwayat'])->name('aspri.riwayat');
        Route::get('/app/aspri/dokumen/{id}', [AspriSkController::class, 'detail'])->name('aspri.detail');
        Route::get('/app/aspri/dokumen/{id}/versi', [AspriSkController::class, 'versionHistory'])->name('aspri.version-history');
        Route::delete('/app/aspri/dokumen/{id}', [AspriSkController::class, 'destroy'])->name('aspri.destroy');
        Route::get('/app/aspri/ttd/{id}', [AspriSkController::class, 'showTtdEditor'])->name('aspri.ttd.editor');
        Route::post('/app/aspri/ttd/{id}', [AspriSkController::class, 'saveTtd'])->name('aspri.ttd.save');
    });

    // Route khusus Petinggi
    Route::middleware(['role:petinggi'])->group(function () {
        Route::get('/app/petinggi/dashboard', [PetinggiSkController::class, 'index'])->name('app.petinggi.dashboard');
        Route::post('/app/petinggi/review/{id}', [PetinggiSkController::class, 'review'])->name('petinggi.review');
        Route::post('/app/petinggi/sign-without-signature/{id}', [PetinggiSkController::class, 'signWithoutSignature'])->name('petinggi.sign-without-signature');
        Route::get('/app/petinggi/riwayat', [PetinggiSkController::class, 'riwayat'])->name('petinggi.riwayat');
        Route::get('/app/petinggi/ttd/{id}', [PetinggiSkController::class, 'showTtdEditor'])->name('petinggi.ttd.editor');
        Route::post('/app/petinggi/ttd/{id}', [PetinggiSkController::class, 'saveSignaturePlacement'])->name('petinggi.ttd.save');
        Route::get('/app/petinggi/signature', [PetinggiSkController::class, 'showSignaturePage'])->name('petinggi.signature.page');
        Route::post('/app/petinggi/signature', [PetinggiSkController::class, 'saveSignature'])->name('petinggi.signature.save');
        Route::get('/app/petinggi/pending-count', [PetinggiSkController::class, 'pendingCount'])->name('petinggi.pending-count');
    });

    // Route khusus Dirut
    Route::middleware(['role:dirut'])->group(function () {
        Route::get('/app/dirut/dashboard', [DirutSkController::class, 'index'])->name('app.dirut.dashboard');
        Route::post('/app/dirut/review/{id}', [DirutSkController::class, 'review'])->name('dirut.review');
        Route::post('/app/dirut/sign-without-signature/{id}', [DirutSkController::class, 'signWithoutSignature'])->name('dirut.sign-without-signature');
        Route::get('/app/dirut/riwayat', [DirutSkController::class, 'riwayat'])->name('dirut.riwayat');
        Route::get('/app/dirut/ttd/{id}', [DirutSkController::class, 'showTtdEditor'])->name('dirut.ttd.editor');
        Route::post('/app/dirut/ttd/{id}', [DirutSkController::class, 'saveSignaturePlacement'])->name('dirut.ttd.save');
        Route::get('/app/dirut/signature', [DirutSkController::class, 'showSignaturePage'])->name('dirut.signature.page');
        Route::post('/app/dirut/signature', [DirutSkController::class, 'saveSignature'])->name('dirut.signature.save');
    });

    // Route khusus Admin (manajemen akun)
    Route::middleware(['role:admin'])->prefix('app/admin')->name('app.admin.')->group(function () {
        Route::get('/dashboard', [AkunController::class, 'index'])->name('dashboard');
        Route::post('/akun', [AkunController::class, 'store'])->name('akun.store');
        Route::put('/akun/{user}', [AkunController::class, 'update'])->name('akun.update');
        Route::delete('/akun/{user}', [AkunController::class, 'destroy'])->name('akun.destroy');
        Route::post('/akun/{user}/reset-password', [AkunController::class, 'resetPassword'])->name('akun.reset-password');
        Route::post('/akun/{user}/ttd', [AkunController::class, 'updateTtd'])->name('akun.ttd');
        Route::get('/activity-logs', [AkunController::class, 'activityLogs'])->name('activity-logs');
        Route::get('/fonnte-quota', [AkunController::class, 'fonnteQuota'])->name('fonnte-quota');
    });

});

Route::get('/test-firebase', function () {
    $messaging = app('firebase.messaging');
    dd('Firebase connected!', $messaging);
});

// Test route untuk mengirim notifikasi FCM ke user yang sedang login
Route::get('/test-fcm-notification', function () {
    if (!Auth::check()) {
        return 'Silakan login terlebih dahulu.';
    }

    $user = Auth::user();
    $tokens = $user->fcmTokens()->pluck('token')->toArray();

    if (empty($tokens)) {
        return 'User tidak memiliki token FCM. Pastikan Anda sudah memberikan izin notifikasi di browser.';
    }

    try {
        $fcmService = app(\App\Services\FcmService::class);
        $fcmService->sendToUser(
            $user,
            'Tes Notifikasi Push',
            'Ini adalah notifikasi push percobaan dari SIGAP. Jika Anda melihat ini, FCM berfungsi!',
            [
                'type' => 'test_notification',
                'url' => route('app.aspri.dashboard'),
            ]
        );

        return 'Notifikasi FCM berhasil dikirim ke ' . count($tokens) . ' device token(s).';
    } catch (\Throwable $e) {
        return 'Error mengirim FCM: ' . $e->getMessage();
    }
});

require __DIR__.'/auth.php';