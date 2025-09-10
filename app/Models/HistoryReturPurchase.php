<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryReturPurchase extends Model
{
    protected $fillable = [
        'date', 'invoice_number', 'id_product', 'supplier_code', 'qty_retur', 'unit'
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
