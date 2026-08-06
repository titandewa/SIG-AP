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

        FcmToken::updateOrCreate(
            ['token' => $request->token],
            ['user_id' => auth()->id()]
        );

        return response()->json(['status' => 'success']);
    }
}