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

    /**
     * Status yang ditampilkan ke UI. Semua status "masih berjalan" (DITINJAU,
     * DITINJAU_DIREKSI, SIAP_TTD, DISETUJUI_TANPA_TTD) disatukan tampilannya
     * jadi "DITINJAU" saja, supaya validator/direksi yang tidak wajib TTD
     * tidak salah paham dikira dia yang harus menempel TTD. Status PENDING,
     * PENDING_DIREKSI, DISETUJUI, dan DITOLAK tetap tampil apa adanya karena
     * sudah jelas & final/butuh aksi nyata dari Admin.
     *
     * Status asli tetap utuh di kolom `status`, ini murni untuk tampilan.
     */
    public function getDisplayStatusAttribute(): string
    {
        $finalOrActionable = ['PENDING', 'PENDING_DIREKSI', 'DISETUJUI', 'DITOLAK'];

        return in_array($this->status, $finalOrActionable, true)
            ? $this->status
            : 'DITINJAU';
    }

    /**
     * Keterangan siapa saja yang belum menyelesaikan tugasnya untuk dokumen
     * ini: nama validator/direksi yang masih MENUNGGU, dan/atau info kalau
     * dokumen sedang menunggu Admin menempel TTD. Return null kalau tidak
     * ada keterangan yang relevan (misal dokumen sudah selesai).
     */
    public function getPendingPartiesLabelAttribute(): ?string
    {
        $approvals = $this->relationLoaded('approvals')
            ? $this->approvals
            : $this->approvals()->with('user')->get();

        $pendingNames = $approvals
            ->where('status', 'MENUNGGU')
            ->pluck('user.name')
            ->filter()
            ->unique()
            ->values();

        $parts = [];

        if ($pendingNames->isNotEmpty()) {
            $parts[] = 'Menunggu: ' . $pendingNames->implode(', ');
        }

        if ($this->status === 'DISETUJUI_TANPA_TTD') {
            $parts[] = 'Menunggu tempel TTD dari Admin';
        }

        return $parts ? implode(' • ', $parts) : null;
    }
}