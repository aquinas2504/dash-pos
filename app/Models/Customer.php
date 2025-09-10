<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use Notifiable; // kalo nirim notif

    protected $table = 'customers';
    protected $primaryKey = 'customer_code';
    public $incrementing = false;  // Karena ID kita tidak menggunakan auto-increment
    protected $keyType = 'string'; // Mengatur tipe primary key sebagai string

    protected $fillable = [
        'customer_code',
        'customer_name',
        'customer_email',
        'customer_phone',
        'npwp',
        'city',
        'country',
        'address',
        'status',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class, 'customer_code', 'customer_code');
    }
}
