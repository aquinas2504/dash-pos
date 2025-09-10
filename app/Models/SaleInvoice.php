<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleInvoice extends Model
{
    protected $table = 'sale_invoices';
    protected $primaryKey = 'invoice_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'invoice_number',
        'date',
        'sj_number',
        'dpp',
        'ppn',
        'grandtotal',
        'payment_id',
        'retur_used',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }


    public function suratjalan()
    {
        return $this->belongsTo(SuratJalan::class, 'sj_number', 'sj_number');
    }

    public function details()
    {
        return $this->hasMany(SaleInvoiceDetail::class, 'invoice_number', 'invoice_number');
    }
}
