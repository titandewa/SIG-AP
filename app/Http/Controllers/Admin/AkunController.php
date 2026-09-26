<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;

class AkunController extends Controller
{
    protected $waService;

    public function __construct(WhatsAppNotificationService $waService)
    {
        $this->waService = $waService;
    }

    public function index(Request $request)
    {
        $query = User::where('role', '!=', 'admin');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        $akun = $query->latest()->paginate(15)->withQueryString();

        return view('admin.akun.index', compact('akun'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:aspri,petinggi,dirut',
            'password' => 'required|string|min:8',
        ]);

        $plainPassword = $data['password'];
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        $this->waService->sendWaRegistrasiAkun($user, $plainPassword);

        return back()->with('status', 'Akun berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:aspri,petinggi,dirut',
            'password' => 'nullable|string|min:8',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return back()->with('status', 'Akun berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->role === 'admin') {
            abort(403);
        }

        $user->delete();

        return back()->with('status', 'Akun berhasil dihapus.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $plainPassword = $data['password'];

        $user->update([
            'password' => Hash::make($plainPassword),
            // 'force_password_reset' => true, // aktifkan kalau mau user wajib ganti password sendiri saat login berikutnya
            'force_password_reset' => false,
        ]);

        $this->waService->sendWaRegistrasiAkun($user, $plainPassword);

        return back()->with('status', "Password {$user->name} berhasil direset.");
    }

    public function updateTtd(Request $request, User $user)
    {
        $request->validate([
            'signature_image' => 'required|string',
        ]);

        $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $request->input('signature_image'));
        $imageData = base64_decode($imageData);

        $path = 'signatures/' . $user->id . '_' . time() . '.png';
        Storage::disk('public')->put($path, $imageData);

        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
        }

        $user->update(['signature_path' => $path]);

        return back()->with('status', 'Tanda tangan berhasil diperbarui.');
    }

    public function activityLogs(Request $request)
    {
        $query = \App\Models\ActivityLog::with(['actor', 'document'])->latest();

        if ($request->filled('role')) {
            $query->where('actor_role', $request->role);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                ->orWhereHas('actor', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('admin.activity-logs', compact('logs'));
    }

    public function fonnteQuota()
    {
        $response = Http::withHeaders([
            'Authorization' => config('services.fonnte.token'),
        ])->post('https://api.fonnte.com/device');

        return response()->json($response->json());
    }
}