<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratJalan extends Model
{
    protected $table = 'surat_jalans';
    protected $primaryKey = 'sj_number';
    public $incrementing = false;
    protected $keyType = 'string';
    

    protected $fillable = [
        'sj_number',
        'ship_date',
        'ppn_status',
        'customer_code',
        'top',
        'ship_1',
        'ship_2',
        'note',
        'status'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_code', 'customer_code');
    }

    public function SJdetails()
    {
        return $this->hasMany(SuratJalanDetail::class, 'sj_number', 'sj_number');
    }

    public function shipping1()
    {
        return $this->belongsTo(Shipping::class, 'ship_1', 'shipping_code');
    }

    public function shipping2()
    {
        return $this->belongsTo(Shipping::class, 'ship_2', 'shipping_code');
    }
}
