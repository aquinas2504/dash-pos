<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListReturPurchase extends Model
{
    protected $table = 'list_retur_purchases';

    protected $fillable = [
        'id_product',
        'supplier_code',
        'qty',
        'unit',
        'price',
        'discount',
    ];

    // Relasi ke Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    // Relasi ke Supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_code', 'supplier_code');
    }
}
