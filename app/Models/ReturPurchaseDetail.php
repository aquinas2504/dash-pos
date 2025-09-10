<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPurchaseDetail extends Model
{
    // untuk testing nanti ini biar bisa pake factory (data dummy)
    use HasFactory;

    protected $table = 'retur_purchase_details';

    protected $fillable = [
        'retur_number',
        'id_product',
        'qty',
        'unit',
        'price',
        'discount',
        'value',
    ];

    // Relasi ke retur (header)
    public function returPurchase()
    {
        return $this->belongsTo(ReturPurchase::class, 'retur_number', 'retur_number');
    }

    // Relasi ke produk
    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product', 'id');
    }
}
