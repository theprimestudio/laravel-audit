<?php

namespace ThePrimeStudio\Audit\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = $this->loadSettings();
        return view('audit::settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = $request->only([
            'product_name',
            'primary_color',
            'accent_color',
            'theme_mode',
            'timezone',
            'date_format',
            'crawler_concurrency',
            'crawler_max_urls',
            'admin_panel_url',
            'exclude_urls',
        ]);

        Storage::disk('local')->put('laravel-audit/settings.json', json_encode($settings, JSON_PRETTY_PRINT));

        return back()->with('success', 'Settings updated successfully.');
    }

    protected function loadSettings()
    {
        if (Storage::disk('local')->exists('laravel-audit/settings.json')) {
            return json_decode(Storage::disk('local')->get('laravel-audit/settings.json'), true);
        }

        return [
            'product_name' => 'Laravel Audit',
            'primary_color' => '#000000',
            'accent_color' => '#333333',
            'theme_mode' => 'light',
            'timezone' => config('app.timezone'),
            'date_format' => 'Y-m-d H:i:s',
            'crawler_concurrency' => config('audit.crawler.concurrency'),
            'crawler_max_urls' => config('audit.crawler.max_urls'),
            'admin_panel_url' => config('audit.admin_panel_url', '/admin'),
            'exclude_urls' => config('audit.crawler.exclude_urls', ''),
        ];
    }
}
