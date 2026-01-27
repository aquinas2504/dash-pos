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

    public function saleDetail()
    {
        return $this->belongsTo(SaleDetail::class, 'so_detail', 'id');
    }

    public function penerimaanDetails()
    {
        return $this->hasMany(PenerimaanDetail::class, 'po_number', 'order_number');
    }

    private function applyDiscount($price, $discount)
    {
        if (!$discount) {
            return $price;
        }

        $discounts = explode('+', $discount);

        foreach ($discounts as $disc) {
            $disc = (float) $disc;
            $price -= ($price * $disc / 100);
        }

        return $price;
    }

    public function getSisaHargaAttribute()
    {
        if ($this->status === 'Pending') {
            return $this->grandtotal;
        }

        if ($this->status === 'Diterima Semua') {
            return 0;
        }

        if ($this->status === 'Diterima Sebagian') {

            $totalSisa = 0;

            foreach ($this->purchaseDetail as $detail) {

                // 🔥 resolve product id (manual vs SO)
                $productId = $detail->id_product
                    ?? $detail->saleDetail->id_product
                    ?? null;

                if (!$productId || !$detail->unit) {
                    continue;
                }

                // qty diterima
                $qtyDiterima = PenerimaanDetail::where('po_number', $this->order_number)
                    ->where('id_product', $productId)
                    ->where('unit', $detail->unit)
                    ->sum('qty_unit');

                $qtyPO = $detail->qty_unit ?? 0;
                $sisaQty = $qtyPO - $qtyDiterima;

                if ($sisaQty <= 0) {
                    continue;
                }

                // 🔥 hitung harga setelah diskon bertingkat
                $finalPrice = $this->applyDiscount(
                    $detail->price,
                    $detail->discount
                );

                $totalSisa += $sisaQty * $finalPrice;
            }

            return $totalSisa;
        }

        return 0;
    }
}
