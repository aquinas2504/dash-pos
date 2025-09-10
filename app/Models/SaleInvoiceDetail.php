<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleInvoiceDetail extends Model
{

    protected $table = 'sale_invoice_details';

    protected $fillable = [
        'invoice_number',
        'surat_jalan_detail',
        'price',
        'discount',
        'total',
    ];

    public function Saleinvoice()
    {
        return $this->belongsTo(SaleInvoice::class, 'invoice_number', 'invoice_number');
    }

    public function SJDetail()
    {
        return $this->belongsTo(SuratJalanDetail::class, 'surat_jalan_detail');
    }
}
