<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';

    protected $fillable = ['unit_code', 'unit_name']; // sama kek shipping, keknya gaguna itu unit_code

    public function productPackings()
    {
        return $this->hasMany(ProductPacking::class);
    }
}
