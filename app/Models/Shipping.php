<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipping extends Model
{
    use HasFactory;

    protected $table = 'shippings';

    protected $fillable = [
        'shipping_code', // keknya gaguna
        'shipping_name',
        'address',
    ];

    // Custom ID Format
    public function getIdShippingAttribute($value)
    {
        return 'shipping-' . str_pad($this->id, 3, '0', STR_PAD_LEFT);
    }
}
