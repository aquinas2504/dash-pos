<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    use HasFactory;

    protected $table = 'purchase_details';
    
    protected $fillable = [
        'order_number',
        'so_detail',
        'id_product',
        'qty_packing',
        'packing',
        'qty_unit',
        'unit'.
        'price',
        'discount',
        'total',
        'status',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'order_number', 'order_number');
    }

    public function saleDetail()
    {
        return $this->belongsTo(SaleDetail::class, 'so_detail', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product', 'id');
    }

}
