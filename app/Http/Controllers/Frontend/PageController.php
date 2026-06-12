<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function aboutUs()
    {
        return view('frontend.pages.about-us');
    }

    public function termsAndConditions()
    {
        return view('frontend.pages.terms-and-conditions');
    }

    public function privacyPolicy()
    {
        return view('frontend.pages.privacy-policy');
    }
}
