<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'ordered_quantity',
        'medication_id',
        'buy_order_id'
    ];

    /**
     * My FK belongs to
     */
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function buy_order(): BelongsTo
    {
        return $this->belongsTo(BuyOrder::class);
    }
}
