<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

/**
 * Tiga peran sistem bersifat tetap (dipakai untuk otorisasi),
 * jadi yang bisa dikelola hanya nama tampilannya.
 */
class RoleController extends Controller
{
    public function index()
    {
        return view('roles.index', ['roles' => Role::withCount('users')->orderBy('id')->get()]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:50']]);

        $role->update($data);

        return back()->with('success', 'Nama peran diperbarui.');
    }
}
