<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $primaryKey = 'cart_item_id';
    protected $fillable = ['quantity'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(
            Product::class,
            'product_id',
            'product_id'
        );
    }
}
