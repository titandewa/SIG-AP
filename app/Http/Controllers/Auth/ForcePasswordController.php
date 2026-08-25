<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForcePasswordController extends Controller
{
    public function edit()
    {
        return view('auth.force-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
            'force_password_reset' => false,
        ]);

        return redirect()->route('dashboard');
    }
}