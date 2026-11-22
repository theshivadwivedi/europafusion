<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['tags' => 'array'];
    }
}
