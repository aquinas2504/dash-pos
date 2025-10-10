<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Purchase extends Model
{

    use HasFactory;

    protected $table = 'purchases';
    protected $primaryKey = 'order_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'order_number',
        'order_date',
        'supplier_code',
        'ppn_status',
        'subtotal',
        'ppn',
        'grandtotal',
        'status',
        'note',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_code', 'supplier_code');
    }

    public function purchaseDetail()
    {
        return $this->hasMany(PurchaseDetail::class, 'order_number', 'order_number');
    }

}
