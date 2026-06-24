<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    use HasFactory;

    protected $primaryKey = 'receipt_id';
    public $incrementing = false;

    protected $fillable = ['receipt_id'];
}
