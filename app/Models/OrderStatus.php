<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_statuses'
    ];

    /**
     * Who has my PK
     */
    public function buy_orders(): HasMany
    {
        return $this->hasMany(BuyOrder::class);
    }
}
