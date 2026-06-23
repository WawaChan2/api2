<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $order_id
 * @property int $user_id
 * @property string $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
    ];

    protected $primaryKey = 'order_id';
}
