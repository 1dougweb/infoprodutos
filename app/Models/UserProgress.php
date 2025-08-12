<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'is_completed',
        'watched_seconds',
        'completed_at'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'watched_seconds' => 'integer',
        'completed_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopeInProgress($query)
    {
        return $query->where('is_completed', false)->where('watched_seconds', '>', 0);
    }

    public function markAsCompleted()
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now()
        ]);
    }

    public function updateWatchedSeconds($seconds)
    {
        $this->update(['watched_seconds' => $seconds]);
        
        // Se assistiu mais de 90% do vídeo, marca como completo
        if ($this->lesson->content_type === 'video' && $this->lesson->duration_minutes > 0) {
            $totalSeconds = $this->lesson->duration_minutes * 60;
            $percentage = ($seconds / $totalSeconds) * 100;
            
            if ($percentage >= 90 && !$this->is_completed) {
                $this->markAsCompleted();
            }
        }
    }
}
