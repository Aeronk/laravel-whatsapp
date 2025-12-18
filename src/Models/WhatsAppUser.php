<?php

namespace Katema\WhatsApp\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsAppUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'profile_name',
        'language',
        'metadata',
        'is_blocked',
        'last_interaction_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_blocked' => 'boolean',
        'last_interaction_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'whatsapp_user_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(WhatsAppSession::class, 'whatsapp_user_id');
    }

    public function activeSession(): HasOne
    {
        return $this->hasOne(WhatsAppSession::class, 'whatsapp_user_id')
            ->where('status', 'active')
            ->latest();
    }

    public function scopeNotBlocked($query)
    {
        return $query->where('is_blocked', false);
    }

    public function block(): void
    {
        $this->update(['is_blocked' => true]);
    }

    public function unblock(): void
    {
        $this->update(['is_blocked' => false]);
    }
}