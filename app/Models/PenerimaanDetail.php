<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PenerimaanDetail extends Model
{

    use HasFactory;

    protected $table = 'penerimaan_details';

    protected $fillable = [
        'penerimaan_number',
        'po_number',
        'id_product',
        'qty_packing',
        'packing',
        'qty_unit',
        'unit',
        'status'
    ];

    // INI GUA UBAH
    public function purchase()
    {
        return $this->belongsTo(PurchaseDetail::class, 'po_number', 'order_number');
    }


    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product', 'id');
    }


    public function getMatchedPurchaseDetail()
    {
        $purchaseDetails = PurchaseDetail::where('order_number', $this->po_number)
            ->where('packing', $this->packing)
            ->where('unit', $this->unit)
            ->get();

        foreach ($purchaseDetails as $pd) {
            $actualProductId = $pd->so_detail ? ($pd->saleDetail->id_product ?? null) : $pd->id_product;

            if ($actualProductId == $this->id_product) {
                return $pd;
            }
        }

        return null;
    }
}
