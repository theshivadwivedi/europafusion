<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    public const GROUPS = [
        'academic' => 'Academic Guidance',
        'beyond' => 'Beyond Admission',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['body' => 'array'];
    }

    public function url(): string
    {
        return route('services').'#'.$this->slug;
    }
}
