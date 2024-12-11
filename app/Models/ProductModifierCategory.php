<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductModifierCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status'];

    protected $attributes = [
        'status' => 'Active',
    ];

    public $timestamps = false;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productModifiers(): HasMany
    {
        return $this->hasMany(ProductModifier::class);
    }
}
