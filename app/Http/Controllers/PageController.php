<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function country(string $country): View
    {
        return $this->render(Page::published()->ofKind('country')->where('slug', "study-in-{$country}")->firstOrFail());
    }

    public function pathway(string $pathway): View
    {
        return $this->render(Page::published()->ofKind('pathway')->where('slug', "germany/{$pathway}")->firstOrFail());
    }

    public function legal(string $slug): View
    {
        $page = Page::published()->ofKind('legal')->where('slug', $slug)->firstOrFail();

        return view('pages.legal', [
            'page' => $page,
            'legalPages' => Page::published()->ofKind('legal')->orderBy('sort_order')->get(['slug', 'title']),
        ]);
    }

    private function render(Page $page): View
    {
        $page->load('destination');

        $related = Page::published()
            ->whereIn('kind', ['country', 'pathway'])
            ->where('id', '!=', $page->id)
            ->where('kind', $page->kind)
            ->orderBy('sort_order')
            ->get(['slug', 'title', 'nav_label', 'kind']);

        return view('pages.content', compact('page', 'related'));
    }
}
