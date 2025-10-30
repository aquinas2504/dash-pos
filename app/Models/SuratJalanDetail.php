<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratJalanDetail extends Model
{
    protected $table = 'surat_jalan_details';

    protected $fillable = [
        'sj_number',
        'so_number',
        'id_product',
        'product_name',
        'qty_packing',
        'packing',
        'qty_unit',
        'unit',
        'status'
    ];


    public function sale()
    {
        return $this->belongsTo(SaleDetail::class, 'so_number', 'order_number');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product', 'id');
    }

    public function getMatchedSaleDetail()
    {
        if (!$this->so_number || !$this->id_product) {
            return null;
        }

        return SaleDetail::where('order_number', $this->so_number)
            ->where('id_product', $this->id_product)
            ->where('packing', $this->packing)
            ->where('unit', $this->unit)
            ->first();
    }
}
