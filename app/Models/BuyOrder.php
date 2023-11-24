<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuyOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'pay_status',
        'order_status_id',
        'user_id'
    ];

    /**
     * My FK belongs to
     */
    public function order_status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Who has my PK
     */
    public function buy_order_items(): HasMany
    {
        return $this->hasMany(BuyOrderItem::class);
    }
}
