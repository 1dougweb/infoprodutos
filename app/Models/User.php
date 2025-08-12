<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'last_activity',
        'current_page',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'is_admin' => 'boolean',
            'last_activity' => 'datetime',
        ];
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(UserPurchase::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function hasPurchased($productId): bool
    {
        return $this->purchases()->where('digital_product_id', $productId)->exists();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function canManageProducts(): bool
    {
        return $this->hasPermissionTo('manage products');
    }

    public function canManageUsers(): bool
    {
        return $this->hasPermissionTo('manage users');
    }

    public function canViewOrders(): bool
    {
        return $this->hasPermissionTo('view orders');
    }

    public function canViewDashboard(): bool
    {
        return $this->hasPermissionTo('view dashboard');
    }
}
