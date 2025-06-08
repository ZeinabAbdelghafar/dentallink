<?php

namespace App\Http\Controllers;

use App\Settings\HomePageSettings;
use Illuminate\Http\Request;

class HomePageSettingsController extends Controller
{
    public function show(HomePageSettings $settings)
    {
        return response()->json([
            'home_title' => $settings->home_title,
            'home_subtitle' => $settings->home_subtitle,
            'home_banner' => $settings->home_banner,
        ]);
    }



    public function update(Request $request, HomePageSettings $settings)
    {
        $validated = $request->validate([
            'home_title' => 'required|string',
            'home_subtitle' => 'required|string',
            'home_banner' => 'nullable|string',
        ]);

        $settings->home_title = $validated['home_title'];
        $settings->home_subtitle = $validated['home_subtitle'];
        $settings->home_banner = $validated['home_banner'] ?? null;

        $settings->save();

        return response()->json(['message' => 'Settings updated']);
    }
}
