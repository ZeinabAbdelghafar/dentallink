<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class HomePageSettings extends Settings
{
    public string $home_title;
    public string $home_subtitle;
    // public array $home_banner;
    public ?string $home_banner = null;

    public static function group(): string
    {
        return 'home_page';
    }

    // protected $casts = [
    //     'home_banner' => 'array',
    // ];
}
