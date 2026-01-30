<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{

    use HasFactory;

    protected $table = 'sales';
    protected $primaryKey = 'order_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'order_number',
        'order_date',
        'customer_code',
        'ppn_status',
        'subtotal',
        'ppn',
        'grandtotal',
        'top',
        'ship_1',
        'ship_2',
        'note',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_code', 'customer_code');
    }

    public function saleDetail()
    {
        return $this->hasMany(SaleDetail::class, 'order_number', 'order_number');
    }

    public function suratJalanDetails()
    {
        return $this->hasMany(SuratJalanDetail::class, 'so_number', 'order_number');
    }


    private function applyDiscount($price, $discount)
    {
        if (!$discount) {
            return $price;
        }

        foreach (explode('+', $discount) as $disc) {
            $price -= $price * ((float) $disc / 100);
        }

        return $price;
    }


    public function getSisaHargaAttribute()
    {
        // 1️⃣ Pending → full grandtotal
        if ($this->status === 'Pending') {
            return $this->grandtotal;
        }

        // 2️⃣ Closed → 0
        if ($this->status === 'Closed') {
            return 0;
        }

        // 3️⃣ Sebagian Terproses
        if ($this->status === 'Sebagian Terproses') {

            // group pengiriman
            $shippedGrouped = $this->suratJalanDetails
                ->groupBy(fn($row) => $row->id_product . '|' . $row->unit);

            $totalSisa = 0;

            foreach ($this->saleDetail as $detail) {

                if (!$detail->id_product || !$detail->unit) {
                    continue;
                }

                $key = $detail->id_product . '|' . $detail->unit;

                $qtyDikirim = isset($shippedGrouped[$key])
                    ? $shippedGrouped[$key]->sum('qty_unit')
                    : 0;

                $qtySO = $detail->quantity;
                $sisaQty = $qtySO - $qtyDikirim;

                if ($sisaQty <= 0) {
                    continue;
                }

                // 🔥 apply discount bertingkat kalau ada
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
