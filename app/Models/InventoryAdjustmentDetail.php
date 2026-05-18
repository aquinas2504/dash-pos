<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAdjustmentDetail extends Model
{
    protected $fillable = [
        'adj_id',
        'product_id',
        'ppn',
        'qty',
        'unit',
        'price'
    ];

    public function adjustment()
    {
        return $this->belongsTo(InventoryAdjustment::class, 'adj_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
