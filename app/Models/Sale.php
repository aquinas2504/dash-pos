<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{

    use HasFactory;

    protected $table = 'sales';
    protected $primaryKey = 'order_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'order_number',
        'order_date',
        'customer_code',
        'ppn_status',
        'subtotal',
        'ppn',
        'grandtotal',
        'top',
        'ship_1',
        'ship_2',
        'note',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_code', 'customer_code');
    }

    public function saleDetail()
    {
        return $this->hasMany(SaleDetail::class, 'order_number', 'order_number');
    }

}
