<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DigitalProduct extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'file_path',
        'file_name',
        'file_size',
        'category',
        'product_type',
        'order',
        'is_active',
        'price',
        'mercadopago_id',
        'is_free'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'price' => 'decimal:2',
        'is_free' => 'boolean'
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(UserPurchase::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }

    public function activeModules(): HasMany
    {
        return $this->hasMany(Module::class)->where('is_active', true)->orderBy('order');
    }

    public function getTotalLessonsCount()
    {
        return $this->activeModules()
            ->withCount(['activeLessons'])
            ->get()
            ->sum('active_lessons_count');
    }

    public function getUserProgressPercentage($userId)
    {
        $totalLessons = $this->getTotalLessonsCount();
        if ($totalLessons === 0) return 0;

        $completedLessons = UserProgress::where('user_id', $userId)
            ->whereHas('lesson.module', function ($query) {
                $query->where('digital_product_id', $this->id);
            })
            ->where('is_completed', true)
            ->count();

        return round(($completedLessons / $totalLessons) * 100);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopePaid($query)
    {
        return $query->where('is_free', false);
    }
}
