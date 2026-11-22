<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'is_published' => 'boolean',
            'last_updated' => 'date',
        ];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOfKind(Builder $query, string $kind): Builder
    {
        return $query->where('kind', $kind);
    }

    public function url(): string
    {
        return url($this->slug);
    }

    /** Sections that carry an anchor id and a title, used for the in-page navigation. */
    public function anchors(): array
    {
        return collect($this->sections ?? [])
            ->filter(fn ($s) => ! empty($s['id']) && ! empty($s['title']) && ! in_array($s['type'], ['hero', 'cta'], true))
            ->map(fn ($s) => ['id' => $s['id'], 'label' => $s['eyebrow'] ?? $s['title']])
            ->values()
            ->all();
    }
}
