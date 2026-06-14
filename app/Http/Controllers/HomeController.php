<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use App\Models\HomePageContent;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $locale = $this->locale($request);
        $content = HomePageContent::current();
        $versions = AppVersion::query()
            ->where('is_visible', true)
            ->where('status', 'active')
            ->orderByRaw("FIELD(platform, 'windows', 'android', 'macos', 'linux', 'ios')")
            ->orderBy('platform')
            ->get();

        return view('home', compact('content', 'versions', 'locale'));
    }

    private function locale(Request $request): string
    {
        $locale = strtolower((string) $request->get('lang', 'en'));

        return in_array($locale, ['en', 'ar', 'fa', 'ru'], true) ? $locale : 'en';
    }
}
