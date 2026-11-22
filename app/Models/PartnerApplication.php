<?php

namespace App\Models;

use App\Models\Concerns\HasLeadStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PartnerApplication extends Model
{
    use HasLeadStatus;

    public const ORG_TYPES = [
        'School / Junior College',
        'Degree College / University',
        'Coaching / Training Centre',
        'Corporate / Company HR',
        'Education Consultant / Agency',
        'NGO / Skill Development Body',
        'Other',
    ];

    public const VOLUMES = ['Less than 50', '50 – 150', '150 – 300', '300 – 500', '500+'];

    public const INTERESTS = [
        'Germany (dMAT / Public Universities)',
        'Finland Public Universities',
        'Ireland Universities',
        'Ausbildung / Vocational Programs',
        "Bachelor's Programs",
        "Master's Programs",
        'MBA / Management',
        'Campus Workshops & Webinars',
        'Co-branded Collateral',
        'Referral Partnership',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['interests' => 'array'];
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(fn (Builder $q) => $q
            ->where('org_name', 'like', "%{$term}%")
            ->orWhere('contact_person', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->orWhere('city', 'like', "%{$term}%"));
    }
}
