<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'authorization_code',
        'modify_bill',
        'refund',
    ];

    protected $attributes = [
        'modify_bill' => false,
        'refund' => false,
    ];

    protected function casts(): array
    {
        return ['authorization_code' => 'hashed'];
    }

    public $timestamps = false;

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
