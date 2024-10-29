<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductModifier extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public $timestamps = false;

    public function productModifierCategory(): BelongsTo
    {
        return $this->belongsTo(ProductModifierCategory::class);
    }

    public function orderProductVariants(): BelongsToMany
    {
        return $this->belongsToMany(OrderProductVariant::class);
    }
}
