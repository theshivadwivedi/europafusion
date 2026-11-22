<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('pages.services', [
            'groups' => Service::orderBy('sort_order')->get()->groupBy('group'),
        ]);
    }
}
