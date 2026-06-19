<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\Grade;
use App\Models\Incident;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\Student;
use App\Policies\EmployeePolicy;
use App\Policies\GradePolicy;
use App\Policies\IncidentPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PayrollPolicy;
use App\Policies\StudentPolicy;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Resources\Resource;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Filament v5: skip ALL resource authorization — single-role admin ERP
        // This is the correct Filament v5 API (overrides get_authorization_response)
        Resource::skipAuthorization();

        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Payroll::class, PayrollPolicy::class);
        Gate::policy(Grade::class, GradePolicy::class);
        Gate::policy(Incident::class, IncidentPolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);

        Gate::before(fn ($user) => $user?->role === 'admin' ? true : null);

        // Observers — notifications in-app
        Incident::observe(\App\Observers\IncidentObserver::class);
        Payroll::observe(\App\Observers\PayrollObserver::class);

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
