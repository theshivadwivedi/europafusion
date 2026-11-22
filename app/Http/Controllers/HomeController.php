<?php

namespace App\Http\Controllers;

use App\Models\University;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('pages.home', [
            'universities' => University::where('show_on_home', true)->orderBy('sort_order')->get(),
        ]);
    }
}
