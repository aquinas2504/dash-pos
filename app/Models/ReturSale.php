<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturSale extends Model
{
    protected $table = 'retur_sales';
    protected $primaryKey = 'retur_number';
    public $incrementing = false; // karena primary key-nya string
    protected $keyType = 'string';

    protected $fillable = [
        'retur_number',
        'invoice_number',
        'date',
        'total',
        'used_for',
    ];

    // Relasi ke SaleInvoice
    public function invoice()
    {
        return $this->belongsTo(SaleInvoice::class, 'invoice_number', 'invoice_number');
    }

    // Relasi ke detail retur
    public function details()
    {
        return $this->hasMany(ReturSaleDetail::class, 'retur_number', 'retur_number');
    }
}
