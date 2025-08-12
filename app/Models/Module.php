<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'digital_product_id',
        'title',
        'description',
        'order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    public function digitalProduct(): BelongsTo
    {
        return $this->belongsTo(DigitalProduct::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function activeLessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->where('is_active', true)->orderBy('order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    public function getProgressPercentage($userId)
    {
        $totalLessons = $this->activeLessons()->count();
        if ($totalLessons === 0) return 0;

        $completedLessons = UserProgress::where('user_id', $userId)
            ->whereIn('lesson_id', $this->activeLessons()->pluck('id'))
            ->where('is_completed', true)
            ->count();

        return round(($completedLessons / $totalLessons) * 100);
    }
}
