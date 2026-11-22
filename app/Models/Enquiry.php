<?php

namespace App\Models;

use App\Models\Concerns\HasLeadStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    use HasLeadStatus;

    public const TYPES = [
        'counselling' => 'Counselling session',
        'evaluation' => 'Profile evaluation',
        'contact' => 'Contact page enquiry',
    ];

    public const QUALIFICATIONS = ['Diploma', 'Higher Secondary', "Bachelor's Degree", "Master's Degree"];

    public const COURSES = [
        'Computer Science & AI',
        'Mechanical & Automotive Engineering',
        'Electrical & Electronics',
        'Data Science & Analytics',
        'Germany Ausbildung (Vocational)',
        'Nursing & Healthcare',
        'MBA & International Business',
        "dMAT Guidance (Master's in Germany)",
    ];

    public const INTAKES = ['Winter Intake', 'Summer Intake'];

    protected $guarded = ['id'];

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst((string) $this->type);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(fn (Builder $q) => $q
            ->where('full_name', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->orWhere('phone', 'like', "%{$term}%"));
    }
}
