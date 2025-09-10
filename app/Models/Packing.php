<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Packing extends Model
{
    use HasFactory;

    protected $table = 'packings';

    protected $fillable = ['packing_name'];

    public function productPackings()
    {
        return $this->hasMany(ProductPacking::class);
    }
}
