<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';
    protected $primaryKey = 'invoice_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'invoice_number',
        'date',
        'penerimaan_number',
        'dpp',
        'ppn',
        'grandtotal',
    ];

    public function penerimaan()
    {
        return $this->belongsTo(Penerimaan::class, 'penerimaan_number', 'penerimaan_number');
    }

    public function details()
    {
        return $this->hasMany(InvoiceDetail::class, 'invoice_number', 'invoice_number');
    }
}
