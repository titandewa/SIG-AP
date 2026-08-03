<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PdfSignerController extends Controller
{
    public function index()
    {
        return view('pdf-signer.index');
    }
}