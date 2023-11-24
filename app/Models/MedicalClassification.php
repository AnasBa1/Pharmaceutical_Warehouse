<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalClassification extends Model
{
    use HasFactory;

    protected $fillable = [
        'classification'
    ];

    /**
     * Who has my PK
     */
    public function medications(): HasMany
    {
        return $this->hasMany(Medication::class);
    }
}
