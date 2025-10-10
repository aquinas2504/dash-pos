<?php

namespace App\Helpers;

class formatRp
{
    // Helper untuk format angka ke format Rupiah
    public static function rupiah($value)
    {
        // Jika value berupa angka bulat, tampilkan tanpa koma
        if (fmod($value, 1) == 0) {
            return 'Rp ' . number_format($value, 0, ',', '.');
        } else {
            // Jika ada koma, tampilkan dengan 2 angka dibelakang koma
            return 'Rp ' . number_format($value, 2, ',', '.');
        }
    }

    // Hitung nilai setelah diskon (nested, ex: "50+50")
    public static function applyDiscount($amount, $discountString)
    {
        if (!$discountString) return $amount;

        $discounts = explode('+', $discountString); // pisah jadi array
        foreach ($discounts as $d) {
            $d = floatval($d);
            $amount -= ($amount * $d / 100);
        }

        return $amount;
    }

    // Hitung Value = QTY * PRICE - DISCOUNT
    public static function calculateValue($qty, $price, $discountString = null)
    {
        $subtotal = $qty * $price;
        return self::applyDiscount($subtotal, $discountString);
    }
}
