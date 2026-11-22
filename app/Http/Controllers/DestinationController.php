<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Page;
use Illuminate\View\View;

class DestinationController extends Controller
{
    public function index(): View
    {
        return view('pages.destinations', [
            'destinations' => Destination::active()->get(),
        ]);
    }

    /** Germany hub: the reference site has no single Germany page, so this links its four pathway pages. */
    public function germany(): View
    {
        $germany = Destination::where('slug', 'germany')->firstOrFail();

        return view('pages.germany', [
            'destination' => $germany,
            'pathways' => Page::published()->ofKind('pathway')->where('destination_id', $germany->id)->orderBy('sort_order')->get(),
        ]);
    }
}
