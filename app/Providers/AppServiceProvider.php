<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\BlogPost;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Grade;
use App\Models\Holiday;
use App\Models\Incident;
use App\Models\Level;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\Service;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\TimetableEntry;
use App\Policies\AttendancePolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\BlogPostPolicy;
use App\Policies\ClassroomPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\ExpenseCategoryPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\GradePolicy;
use App\Policies\HolidayPolicy;
use App\Policies\IncidentPolicy;
use App\Policies\LevelPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PayrollPolicy;
use App\Policies\ServicePolicy;
use App\Policies\StudentAttendancePolicy;
use App\Policies\StudentPolicy;
use App\Policies\SubjectPolicy;
use App\Policies\TimetableEntryPolicy;
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
        Gate::policy(Level::class, LevelPolicy::class);
        Gate::policy(Service::class, ServicePolicy::class);
        Gate::policy(Expense::class, ExpensePolicy::class);
        Gate::policy(ExpenseCategory::class, ExpenseCategoryPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(BlogPost::class, BlogPostPolicy::class);
        Gate::policy(Holiday::class, HolidayPolicy::class);
        Gate::policy(Attendance::class, AttendancePolicy::class);
        Gate::policy(StudentAttendance::class, StudentAttendancePolicy::class);
        Gate::policy(Classroom::class, ClassroomPolicy::class);
        Gate::policy(Subject::class, SubjectPolicy::class);
        Gate::policy(TimetableEntry::class, TimetableEntryPolicy::class);

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

        // Recover from stale/expired Livewire requests (bfcache restore, expired
        // session, cached endpoint after deploy) instead of showing the raw
        // 403/404/419 error modal. Registered globally → applies to every panel.
        FilamentView::registerRenderHook(
            'panels::head.end',
            fn (): string => view('filament.livewire-error-recovery')->render(),
        );

        // Impersonation banner — shown on tenant panels while a platform_admin is
        // logged in as a school admin (Phase 7). Offers a one-click return.
        FilamentView::registerRenderHook(
            'panels::body.start',
            function (): string {
                if (! session()->has('impersonator_id')) {
                    return '';
                }

                $url  = route('impersonate.leave');
                $back = e(__('Revenir à la plateforme'));
                $who  = e(auth()->user()?->name ?? '');
                $as   = e(__('Vous êtes connecté en tant que :name (mode plateforme).', ['name' => $who]));

                return <<<HTML
                <div style="position:sticky;top:0;z-index:9999;display:flex;align-items:center;justify-content:center;gap:14px;
                            padding:8px 16px;background:#7C3AED;color:#fff;font-size:13px;font-weight:600;">
                    <span>{$as}</span>
                    <a href="{$url}" style="background:rgba(255,255,255,.2);padding:4px 12px;border-radius:8px;color:#fff;text-decoration:none;">
                        {$back}
                    </a>
                </div>
                HTML;
            }
        );
    }
}
