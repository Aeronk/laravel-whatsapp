<?php

namespace Katema\WhatsApp\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsAppSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'whatsapp_user_id',
        'status',
        'context',
        'current_step',
        'metadata',
        'started_at',
        'ended_at',
        'expires_at',
    ];

    protected $casts = [
        'context' => 'array',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(WhatsAppUser::class, 'whatsapp_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'whatsapp_session_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '<=', now());
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function end(): void
    {
        $this->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);
    }

    public function extend(int $minutes = null): void
    {
        $timeout = $minutes ?? config('whatsapp.chatbot.session_timeout') / 60;
        $this->update([
            'expires_at' => now()->addMinutes($timeout),
        ]);
    }

    public function setContext(string $key, $value): void
    {
        $context = $this->context ?? [];
        $context[$key] = $value;
        $this->update(['context' => $context]);
    }

    public function getContext(string $key, $default = null)
    {
        return data_get($this->context, $key, $default);
    }
}