<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Shared status workflow for leads (enquiries and partner applications).
 */
trait HasLeadStatus
{
    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'in_progress' => 'In progress',
        'closed' => 'Closed',
    ];

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status && array_key_exists($status, self::STATUSES)
            ? $query->where('status', $status)
            : $query;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }
}
