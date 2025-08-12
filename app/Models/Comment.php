<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'parent_id',
        'content',
        'is_approved'
    ];

    protected $casts = [
        'is_approved' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function canBeEditedBy($user)
    {
        return $user->id === $this->user_id || $user->hasRole('admin');
    }

    public function canBeDeletedBy($user)
    {
        return $user->id === $this->user_id || $user->hasRole('admin');
    }
}
