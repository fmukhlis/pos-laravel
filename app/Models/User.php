<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'password',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    /**
     * The model's default values for attributes.
     * 
     * @var array
     */
    protected $attributes = [
        'role' => 'Free'
    ];

    public function canceledProducts(): HasMany
    {
        return $this->hasMany(OrderProductVariant::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(EmployeeInvite::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function ownedStores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }
}
