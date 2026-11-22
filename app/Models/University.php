<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['show_on_home' => 'boolean'];
    }
}
