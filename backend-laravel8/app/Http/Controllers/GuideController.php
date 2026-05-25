<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class GuideController extends Controller
{
    /**
     * Display the Product Research Methods Guide
     */
    public function index(Request $request)
    {
        $currentLang = App::getLocale();
        
        return view('guide.index', [
            'currentLang' => $currentLang,
            'isRtl' => $currentLang === 'ar'
        ]);
    }
}
