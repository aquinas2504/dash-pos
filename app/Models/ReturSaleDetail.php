<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturSaleDetail extends Model
{
    protected $table = 'retur_sale_details';

    protected $fillable = [
        'retur_number',
        'id_product',
        'qty',
        'unit',
        'value',
        'note',
    ];

    // Relasi ke retur_sales
    public function retur()
    {
        return $this->belongsTo(ReturSale::class, 'retur_number', 'retur_number');
    }

    // Relasi ke product
    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }
}
