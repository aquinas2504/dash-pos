<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    use HasFactory;

    protected $table = 'sale_details';
    
    public $timestamps = false;
    
    protected $fillable = [
        'order_number',
        'id_product',
        'quantity',
        'unit',
        'price',
        'discount',
        'total',
        'qty_packing',
        'packing',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'order_number', 'order_number');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product', 'id');
    }

    public function purchaseDetail()
    {
        return $this->hasOne(PurchaseDetail::class, 'so_detail', 'id');
    }
}
