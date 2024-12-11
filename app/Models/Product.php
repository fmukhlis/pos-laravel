<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    public $timestamps = false;

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function productModifierCategories(): HasMany
    {
        return $this->hasMany(ProductModifierCategory::class);
    }

    public function productModifiers(): HasManyThrough
    {
        return $this->hasManyThrough(
            ProductModifier::class,
            ProductModifierCategory::class
        );
    }

    public function productOptionCategories(): HasMany
    {
        return $this->hasMany(ProductOptionCategory::class);
    }

    public function productOptions(): HasManyThrough
    {
        return $this->hasManyThrough(
            ProductOption::class,
            ProductOptionCategory::class
        );
    }

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
