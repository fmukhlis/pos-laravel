<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrderProductVariant extends Model
{
    use HasFactory;

    protected $table = 'order_product_variant';

    protected $fillable = ['cancel_reason'];

    protected $attributes = ['is_canceled' => false];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function productModifiers(): BelongsToMany
    {
        return $this->belongsToMany(ProductModifier::class);
    }
}
