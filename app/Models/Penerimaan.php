<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penerimaan extends Model
{

    use HasFactory;

    protected $table = 'penerimaans';
    protected $primaryKey = 'penerimaan_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['penerimaan_number', 'date', 'supplier_code', 'ppn_status', 'note', 'status'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_code', 'supplier_code');
    }

    public function details()
    {
        return $this->hasMany(PenerimaanDetail::class, 'penerimaan_number', 'penerimaan_number');
    }

}
