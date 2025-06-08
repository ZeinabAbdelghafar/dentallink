<?php

namespace App\Http\Controllers;

use App\Settings\HomePageSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Cloudinary\Cloudinary;

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
            'home_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('home_banner')) {
            $cloudinary = new Cloudinary();

            $uploadedFile = $cloudinary->uploadApi()->upload(
                $request->file('home_banner')->getRealPath(),
                ['folder' => 'banners']
            );

            $settings->home_banner = $uploadedFile['secure_url'];
        }

        $settings->home_title = $validated['home_title'];
        $settings->home_subtitle = $validated['home_subtitle'];
        $settings->save();

        return response()->json(['message' => 'Settings updated']);
    }
}
