<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destination extends Model
{
    /** Flag stripe colours, used for the accent band on country heroes. */
    public const FLAG_COLORS = [
        'de' => ['#000000', '#DD0000', '#FFCE00'],
        'at' => ['#C8102E', '#FFFFFF', '#C8102E'],
        'it' => ['#009246', '#FFFFFF', '#CE2B37'],
        'fi' => ['#FFFFFF', '#002F6C', '#FFFFFF'],
        'es' => ['#AA151B', '#F1BF00', '#AA151B'],
        'nl' => ['#AE1C28', '#FFFFFF', '#21468B'],
        'pl' => ['#FFFFFF', '#DC143C'],
        'lv' => ['#9E3039', '#FFFFFF', '#9E3039'],
        'lt' => ['#FDB913', '#006A44', '#C1272D'],
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'popular_study_areas' => 'array',
            'highlights' => 'array',
            'top_universities' => 'array',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    /** Public URL of the destination's landing page (Germany has a hub page for its pathways). */
    public function url(): string
    {
        return url('study-in-'.$this->slug);
    }

    public function flagColors(): array
    {
        return self::FLAG_COLORS[$this->code] ?? ['#15294d'];
    }

    /** Short work-rights text for cards (text before the first ";"). */
    public function shortWorkRights(): string
    {
        return trim(explode(';', $this->work_rights)[0]);
    }

    /** Short living-cost text for cards (text before the first "("). */
    public function shortLivingCost(): string
    {
        return trim(explode('(', $this->living_cost)[0]);
    }

    /** Label used in enquiry forms, e.g. "Germany". */
    public static function formOptions(): array
    {
        return static::active()->pluck('name')->all();
    }
}
