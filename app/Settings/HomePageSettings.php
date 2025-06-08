<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class HomePageSettings extends Settings
{
    public string $home_title;
    public string $home_subtitle;
    public ?string $home_banner;

    public static function group(): string
    {
        return 'home_page';
    }
}
