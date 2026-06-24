<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    use HasFactory;

    protected $primaryKey  = null;
    public    $incrementing = false;

    protected $fillable = [
        'inventory_id',
        'transaction_id',
        'transaction_type',
        'quantity_delta',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }
}