<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(string $action, string $description, ?int $documentId = null, array $meta = [])
    {
        ActivityLog::create([
            'actor_id' => Auth::id(),
            'actor_role' => Auth::user()->role ?? 'unknown', // sesuaikan nama kolom role Anda
            'action' => $action,
            'document_id' => $documentId,
            'description' => $description,
            'meta' => $meta,
        ]);
    }
}