<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $guarded = ['id'];

    public function scopeGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group)->orderBy('sort_order');
    }
}
