<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationHistoryController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notificationHistories()->paginate(20);

        return view('notifications.history', compact('notifications'));
    }
}
