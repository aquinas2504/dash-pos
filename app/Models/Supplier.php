<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Supplier extends Authenticatable
{
    use Notifiable;

    protected $table = 'suppliers';
    protected $primaryKey = 'supplier_code';
    public $incrementing = false;  // Karena ID kita tidak menggunakan auto-increment
    protected $keyType = 'string'; // Mengatur tipe primary key sebagai string

    protected $fillable = [
        'supplier_code',
        'supplier_name',
        'supplier_email',
        'supplier_phone',
        'npwp',
        'city',
        'country',
        'address',
        'status',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'supplier_code', 'supplier_code');
    }
}
