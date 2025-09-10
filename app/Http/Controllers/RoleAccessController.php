<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\RolePermission;

class RoleAccessController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();

        // Ambil sub-role dari users yang bukan supermanager
        $roles = User::where('main_role', 'umum')
            ->distinct()
            ->pluck('sub_role')
            ->filter() // hilangkan null
            ->values()
            ->toArray();

        // Ambil hak akses masing-masing role
        $rolePermissions = RolePermission::all()->groupBy('role');

        return view('Auth.role_access', compact('permissions', 'roles', 'rolePermissions'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'role' => 'required|string',
            'permissions' => 'array',
        ]);

        $role = $request->role;
        $permissions = $request->permissions ?? [];

        // Hapus semua akses lama
        RolePermission::where('role', $role)->delete();

        // Simpan akses baru
        foreach ($permissions as $permId) {
            RolePermission::create([
                'role' => $role,
                'permission_id' => $permId,
            ]);
        }

        return redirect()->back()->with('success', "Akses untuk role '$role' berhasil diperbarui.");
    }
}
