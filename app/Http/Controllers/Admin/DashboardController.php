<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\PartnerApplication;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $since = Carbon::now()->subDays(30);

        return view('admin.dashboard', [
            'stats' => [
                'new_enquiries' => Enquiry::where('status', 'new')->count(),
                'enquiries_30d' => Enquiry::where('created_at', '>=', $since)->count(),
                'new_partners' => PartnerApplication::where('status', 'new')->count(),
                'total_leads' => Enquiry::count() + PartnerApplication::count(),
            ],
            'byType' => Enquiry::selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type'),
            'byCountry' => Enquiry::whereNotNull('preferred_country')
                ->selectRaw('preferred_country, count(*) as total')
                ->groupBy('preferred_country')->orderByDesc('total')->limit(6)
                ->pluck('total', 'preferred_country'),
            'recentEnquiries' => Enquiry::latest()->limit(6)->get(),
            'recentPartners' => PartnerApplication::latest()->limit(4)->get(),
        ]);
    }
}
