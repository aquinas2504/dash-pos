<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{

    protected $table = 'invoice_details';

    protected $fillable = [
        'invoice_number',
        'penerimaan_detail',
        'price',
        'discount',
        'total',
        'qty_retur',
        'unit_retur',
        'status_retur',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_number', 'invoice_number');
    }

    public function penerimaanDetail()
    {
        return $this->belongsTo(PenerimaanDetail::class, 'penerimaan_detail');
    }
}
