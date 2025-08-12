<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'description',
        'content_type',
        'content_url',
        'content_text',
        'duration_minutes',
        'order',
        'is_active',
        'is_free'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_free' => 'boolean',
        'order' => 'integer',
        'duration_minutes' => 'integer'
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function userProgress(): HasOne
    {
        return $this->hasOne(UserProgress::class)->where('user_id', auth()->id());
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->approved()->topLevel()->with(['user', 'replies.user'])->orderBy('created_at', 'desc');
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

    public function isCompleted($userId = null)
    {
        if (!$userId) {
            $userId = auth()->id();
        }

        return UserProgress::where('user_id', $userId)
            ->where('lesson_id', $this->id)
            ->where('is_completed', true)
            ->exists();
    }

    public function getProgressPercentage($userId = null)
    {
        if (!$userId) {
            $userId = auth()->id();
        }

        $progress = UserProgress::where('user_id', $userId)
            ->where('lesson_id', $this->id)
            ->first();

        if (!$progress) return 0;

        if ($this->content_type === 'video' && $this->duration_minutes > 0) {
            $totalSeconds = $this->duration_minutes * 60;
            return min(100, round(($progress->watched_seconds / $totalSeconds) * 100));
        }

        return $progress->is_completed ? 100 : 0;
    }

    public function getFormattedDuration()
    {
        if ($this->duration_minutes === 0) return 'N/A';
        
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0) {
            return sprintf('%dh %02dm', $hours, $minutes);
        }
        
        return sprintf('%dm', $minutes);
    }

    public function getFormattedFileSize()
    {
        if ($this->content_type !== 'file' || !$this->content_url) {
            return 'N/A';
        }

        $filePath = storage_path('app/' . $this->content_url);
        if (!file_exists($filePath)) {
            return 'Arquivo não encontrado';
        }

        $size = filesize($filePath);
        
        if ($size < 1024) {
            return $size . ' B';
        } elseif ($size < 1024 * 1024) {
            return round($size / 1024, 1) . ' KB';
        } elseif ($size < 1024 * 1024 * 1024) {
            return round($size / (1024 * 1024), 1) . ' MB';
        } else {
            return round($size / (1024 * 1024 * 1024), 1) . ' GB';
        }
    }

    public function getFileExtension()
    {
        if ($this->content_type !== 'file' || !$this->content_url) {
            return 'N/A';
        }

        return strtoupper(pathinfo($this->content_url, PATHINFO_EXTENSION));
    }
}
