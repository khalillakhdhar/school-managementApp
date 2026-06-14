<?php

namespace App\Providers;

use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['ar', 'en', 'fr'])
                ->labels([
                    'ar' => 'العربية',
                    'en' => 'English',
                    'fr' => 'Français',
                ]);
        });

        FilamentView::registerRenderHook(
            'panels::head.end',
            function (): string {
                $locale = app()->getLocale();
                $dir    = $locale === 'ar' ? 'rtl' : 'ltr';
                return <<<HTML
                <script>
                    (function(){
                        document.documentElement.setAttribute('dir', '{$dir}');
                        document.documentElement.setAttribute('lang', '{$locale}');
                    })();
                </script>
                HTML;
            }
        );
    }
}
