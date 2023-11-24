<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medication extends Model
{
    use HasFactory;

    protected $fillable = [
        'scientific_name',
        'trade_name',
        'medical_classification_id',
        'manufacturer',
        'available_quantity',
        'expiration_date',
        'price'
    ];

    protected $casts = [
        'expiration_date' => 'datetime',
    ];

    /**
     * My FK belongs to
     */
    public function medical_classification(): BelongsTo
    {
        return $this->belongsTo(MedicalClassification::class);
    }

    /**
     * Who has my PK
     */
    public function buy_order_items(): HasMany
    {
        return $this->hasMany(BuyOrderItem::class);
    }
}
