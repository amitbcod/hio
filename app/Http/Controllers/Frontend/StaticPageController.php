<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;

class StaticPageController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $page = StaticPage::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $locale = $request->get('locale') ?: app()->getLocale();
        if ($locale === 'fr') {
            $content = $page->content_fr ?: $page->content_en;
        } else {
            $content = $page->content_en ?: $page->content_fr;
        }

        $heroSlides = [
            ['image' => asset('images/mauritius.jpg')],
        ];

        $content = Blade::render($content, compact('page', 'heroSlides'));

        return view('frontend.pages.static-page', compact('page', 'content'));
    }
}
