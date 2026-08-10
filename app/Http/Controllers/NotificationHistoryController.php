<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationHistoryController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notificationHistories()->latest()->paginate(20);

        return view('notifications.history', compact('notifications'));
    }

    public function markRead($id)
    {
        $notification = Auth::user()->notificationHistories()->findOrFail($id);
        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function clearAll()
    {
        Auth::user()->notificationHistories()->delete();

        return redirect()->back()->with('success', 'Semua notifikasi berhasil dihapus.');
    }
}