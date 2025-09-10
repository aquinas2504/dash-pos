<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'main_role',
        'sub_role',
        'is_active',
    ];

    protected $hidden = ['password'];


    public function permissions()
    {
        return $this->hasManyThrough(
            Permission::class,
            RolePermission::class,
            'role', // foreign key on RolePermission
            'id', // foreign key on Permission
            'sub_role', // local key on User
            'permission_id' // local key on RolePermission
        );
    }

    public function hasPermission($perm)
    {
        if ($this->main_role === 'supermanager') return true;

        return $this->permissions()->where('name', $perm)->exists();
    }
}
