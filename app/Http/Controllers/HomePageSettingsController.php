<?php

namespace App\Http\Controllers;

use App\Settings\HomePageSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;

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

        return response()->json(['message' => 'Settings updated', 'home_banner' => $settings->home_banner]);
    }


    // public function update(Request $request, HomePageSettings $settings)
    // {
    //     $validated = $request->validate([
    //         'home_title' => 'required|string',
    //         'home_subtitle' => 'required|string',
    //         'home_banner.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    //     ]);

    //     $bannerUrls = [];

    //     if ($request->hasFile('home_banner')) {
    //         $cloudinary = new Cloudinary();
    //         $files = $request->file('home_banner');
    //         if (!is_array($files)) {
    //             $files = [$files];
    //         }
    //         foreach ($files as $file) {
    //             $uploadedFile = $cloudinary->uploadApi()->upload(
    //                 $file->getRealPath(),
    //                 ['folder' => 'banners']
    //             );
    //             $bannerUrls[] = $uploadedFile['secure_url'];
    //         }
    //     }

    //     $settings->home_title = $validated['home_title'];
    //     $settings->home_subtitle = $validated['home_subtitle'];

    //     if (!empty($bannerUrls)) {
    //         $existingBanners = is_array($settings->home_banner) ? $settings->home_banner : [];
    //         $settings->home_banner = array_merge($existingBanners, $bannerUrls);
    //     }

    //     $settings->save();

    //     return response()->json(['message' => 'Settings updated', 'bannerUrls' => $settings->home_banner]);
    // }
}
