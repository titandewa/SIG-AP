<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $guarded = ['id'];

    // Relasi ke Aspri (Pembuat)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke daftar Persetujuan Petinggi
    public function approvals()
    {
        return $this->hasMany(DocumentApproval::class);
    }

    // Relasi ke Riwayat Versi File
    public function versions()
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function latestVersion()
    {
        return $this->hasOne(DocumentVersion::class)->latestOfMany();
    }

    // Relasi ke Komentar
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function signaturePlacements()
    {
        return $this->hasMany(SignaturePlacement::class);
    }
}