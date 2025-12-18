<?php

namespace Katema\WhatsApp\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsAppFlow extends Model
{
    use HasFactory;

    protected $fillable = [
        'flow_id',
        'name',
        'version',
        'status',
        'json_definition',
        'metadata',
        'published_at',
    ];

    protected $casts = [
        'json_definition' => 'array',
        'metadata' => 'array',
        'published_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function unpublish(): void
    {
        $this->update(['status' => 'draft']);
    }

    public function getScreen(string $screenId): ?array
    {
        return collect($this->json_definition['screens'] ?? [])
            ->firstWhere('id', $screenId);
    }
}