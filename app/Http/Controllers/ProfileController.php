<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()->load('role')]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'    => ['nullable', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'password' => ['nullable', 'string', 'min:3', 'confirmed'],
        ], [
            'email.required' => 'Email wajib ditautkan.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email sudah dipakai akun lain.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, tanda + atau -.',
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;

        if (! empty($data['password'])) {
            $user->password = $data['password']; // di-hash otomatis oleh cast
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
