<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('home_page.home_title', '');
        $this->migrator->add('home_page.home_subtitle', '');
        $this->migrator->add('home_page.home_banner', null);
    }
};
