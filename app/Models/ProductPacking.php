<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductPacking extends Model
{
    use HasFactory;

    protected $table = 'product_packings';

    protected $fillable = [
        'product_id',
        'packing_id',
        'unit_id',
        'conversion_value'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function packing()
    {
        return $this->belongsTo(Packing::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
