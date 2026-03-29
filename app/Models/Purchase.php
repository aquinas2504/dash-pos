<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'lock_reason',
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
        switch ($this->status) {

            case 'Pending':
                return $this->grandtotal;

            case 'Diterima Semua':
                return 0;

            case 'Diterima Sebagian':
                return $this->calculateRemainingPrice();

            case 'Locked':
                return $this->calculateLockedRemainingPrice();

            default:
                return 0;
        }
    }

    private function calculateRemainingPrice()
    {
        $totalSisa = 0;

        foreach ($this->purchaseDetail as $detail) {

            $productId = $detail->id_product
                ?? $detail->saleDetail->id_product
                ?? null;

            if (!$productId || !$detail->unit) {
                continue;
            }

            $qtyDiterima = PenerimaanDetail::where('po_number', $this->order_number)
                ->where('id_product', $productId)
                ->where('unit', $detail->unit)
                ->sum('qty_unit');

            $qtyPO = $detail->qty_unit ?? 0;
            $sisaQty = $qtyPO - $qtyDiterima;

            if ($sisaQty <= 0) {
                continue;
            }

            $finalPrice = $this->applyDiscount(
                $detail->price,
                $detail->discount
            );

            $totalSisa += $sisaQty * $finalPrice;
        }

        return $totalSisa;
    }

    private function calculateLockedRemainingPrice()
    {
        if ($this->purchaseDetail->isEmpty()) {
            return 0;
        }

        $totalDiterima = PenerimaanDetail::where('po_number', $this->order_number)
            ->sum('qty_unit');

        if ($totalDiterima == 0) {
            return $this->grandtotal;
        }

        return $this->calculateRemainingPrice();
    }
}
