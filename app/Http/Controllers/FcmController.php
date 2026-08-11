<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FcmToken;

class FcmController extends Controller
{
    public function saveToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        // Simpan token dengan user_id nullable (untuk halaman login)
        // Jika user sudah login, simpan dengan user_id-nya
        FcmToken::updateOrCreate(
            ['token' => $request->token],
            ['user_id' => auth()->id()]
        );

        return response()->json(['status' => 'success']);
    }
}