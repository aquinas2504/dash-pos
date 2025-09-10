<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{

    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'product_code',
        'product_name',
        'description'
    ];

    public function productPackings()
    {
        return $this->hasMany(ProductPacking::class);
    }

    function convertToPieces($qty, $unit)
    {
        switch (strtolower($unit)) {
            case 'lusin':
                return $qty * 12;
            case 'gross':
                return $qty * 144;
            case 'set':
            case 'pieces':
            default:
                return $qty;
        }
    }
}
