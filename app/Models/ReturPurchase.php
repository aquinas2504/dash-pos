<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPurchase extends Model
{
    use HasFactory;

    protected $table = 'retur_purchases';
    protected $primaryKey = 'retur_number';
    public $incrementing = false; // karena bukan auto increment
    protected $keyType = 'string'; // retur_number tipe string

    protected $fillable = [
        'retur_number',
        'date',
        'supplier_code',
        'note',
    ];

    // Relasi ke detail
    public function details()
    {
        return $this->hasMany(ReturPurchaseDetail::class, 'retur_number', 'retur_number');
    }

    // Relasi ke supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_code', 'supplier_code');
    }
}
