<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('role')->withCount('rooms')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('username', 'like', $term));
            })
            ->when($request->filled('role'), fn ($q) => $q->where('role_id', $request->role))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => Role::orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        return view('users.form', ['user' => new User(), 'roles' => Role::orderBy('id')->get()]);
    }

    public function store(Request $request)
    {
        User::create($this->validated($request));

        return redirect()->route('users.index')->with('success', 'Pengguna ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('users.form', ['user' => $user, 'roles' => Role::orderBy('id')->get()]);
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Admin tidak boleh menurunkan role-nya sendiri sampai tidak ada admin tersisa
        if ($user->id === $request->user()->id && (int) $data['role_id'] !== User::ROLE_ADMIN) {
            return back()->withInput()->withErrors(['role_id' => 'Anda tidak dapat mengubah role akun Anda sendiri.']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Data pengguna diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Akun yang sedang dipakai tidak bisa dihapus.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna dihapus.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);

        return $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($user?->id)],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:3'],
            'role_id'  => ['required', 'exists:roles,id'],
            'phone'    => ['nullable', 'required_if:role_id,'.User::ROLE_PIC, 'regex:/^[0-9+\-\s]{8,20}$/'],
        ], [
            'email.required'    => 'Email wajib diisi untuk semua peran.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah dipakai akun lain.',
            'username.unique'   => 'Username sudah dipakai.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, strip, dan garis bawah.',
            'phone.required_if' => 'Nomor WhatsApp wajib diisi untuk PIC Ruang.',
            'phone.regex'       => 'Nomor hanya boleh berisi angka, spasi, tanda + atau -.',
        ]);
    }
}
