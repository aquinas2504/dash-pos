<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    protected $fillable = [
        'date',
        'type',
        'reason',
        'total_value'
    ];

    public function details()
    {
        return $this->hasMany(InventoryAdjustmentDetail::class, 'adj_id');
    }
}
