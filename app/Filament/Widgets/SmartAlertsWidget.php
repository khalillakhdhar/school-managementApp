<?php
namespace App\Filament\Widgets;

use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Incident;
use App\Models\Payment;
use App\Models\Payroll;
use Filament\Widgets\Widget;

class SmartAlertsWidget extends Widget
{
    protected static bool $isLazy = false;
    protected static ?int $sort = 4;
    protected string $view = 'filament.widgets.smart-alerts-widget';

    public function getColumnSpan(\Filament\Support\Enums\MaxWidth|int|string|null $maxWidth = null): int|string
    {
        return 'full';
    }

    protected function getViewData(): array
    {
        $notifications = [];

        // ── CRITICAL: Overdue payments ─────────────────────────────────────
        $overdueCount = Payment::where('status', 'pending')
            ->whereNotNull('due_date')->whereDate('due_date', '<', now())->count();

        if ($overdueCount > 0) {
            $overdueTotal = (float) Payment::where('status', 'pending')
                ->whereNotNull('due_date')->whereDate('due_date', '<', now())->sum('amount');

            $oldest = Payment::where('status', 'pending')
                ->whereNotNull('due_date')->whereDate('due_date', '<', now())
                ->orderBy('due_date')->value('due_date');

            $daysLate = $oldest ? now()->diffInDays($oldest) : 0;

            $notifications[] = [
                'level'       => 'critical',
                'icon'        => 'banknote',
                'title'       => __(':count paiement(s) en retard', ['count' => $overdueCount]),
                'description' => __(':amount TND impayés · le plus ancien depuis :days jours', ['amount' => number_format($overdueTotal, 3), 'days' => $daysLate]),
                'action_url'  => \App\Filament\Resources\PaymentResource::getUrl('index'),
                'action_label'=> __('Voir les paiements'),
                'time'        => __('Mis à jour maintenant'),
            ];
        }

        // ── CRITICAL: High-severity unnotified incidents ────────────────────
        $highIncidents = Incident::where('parent_notified', false)
            ->where('severity', 'high')->count();

        if ($highIncidents > 0) {
            $notifications[] = [
                'level'       => 'critical',
                'icon'        => 'triangle-alert',
                'title'       => __(':count incident(s) grave(s) non notifié(s)', ['count' => $highIncidents]),
                'description' => __("Les parents concernés n'ont pas encore été informés"),
                'action_url'  => \App\Filament\Resources\IncidentResource::getUrl('index'),
                'action_label'=> __('Notifier les parents'),
                'time'        => __('Action requise'),
            ];
        }

        // ── WARNING: Medium/low unnotified incidents ────────────────────────
        $otherIncidents = Incident::where('parent_notified', false)
            ->whereIn('severity', ['medium', 'low'])->count();

        if ($otherIncidents > 0) {
            $notifications[] = [
                'level'       => 'warning',
                'icon'        => 'alert-circle',
                'title'       => __(':count incident(s) en attente de notification', ['count' => $otherIncidents]),
                'description' => __('Incidents mineurs ou moyens sans notification parentale'),
                'action_url'  => \App\Filament\Resources\IncidentResource::getUrl('index'),
                'action_label'=> __('Voir les incidents'),
                'time'        => __("Aujourd'hui"),
            ];
        }

        // ── WARNING: Classes without teacher ───────────────────────────────
        $classesNoTeacher = Classroom::whereNull('teacher_id')->count();

        if ($classesNoTeacher > 0) {
            $notifications[] = [
                'level'       => 'warning',
                'icon'        => 'building-2',
                'title'       => __(':count classe(s) sans enseignant', ['count' => $classesNoTeacher]),
                'description' => __("Ces classes n'ont pas encore d'enseignant titulaire assigné"),
                'action_url'  => \App\Filament\Resources\ClassroomResource::getUrl('index'),
                'action_label'=> __('Assigner un enseignant'),
                'time'        => __('À planifier'),
            ];
        }

        // ── WARNING: Finalized payroll not paid ────────────────────────────
        $pendingPayroll = Payroll::where('status', 'finalized')->count();

        if ($pendingPayroll > 0) {
            $pendingAmount = (float) Payroll::where('status', 'finalized')->sum('net_salary');
            $notifications[] = [
                'level'       => 'warning',
                'icon'        => 'wallet',
                'title'       => __(':count fiche(s) de paie finalisée(s) non payée(s)', ['count' => $pendingPayroll]),
                'description' => __(':amount TND à virer aux employés', ['amount' => number_format($pendingAmount, 3)]),
                'action_url'  => \App\Filament\Resources\PayrollResource::getUrl('index'),
                'action_label'=> __('Voir les fiches de paie'),
                'time'        => __('En attente'),
            ];
        }

        // ── INFO: Draft payroll items ───────────────────────────────────────
        $draftPayroll = Payroll::where('status', 'draft')->count();

        if ($draftPayroll > 0) {
            $notifications[] = [
                'level'       => 'info',
                'icon'        => 'file-text',
                'title'       => __(':count fiche(s) de paie en brouillon', ['count' => $draftPayroll]),
                'description' => __('À finaliser avant envoi en paiement'),
                'action_url'  => \App\Filament\Resources\PayrollResource::getUrl('index'),
                'action_label'=> __('Finaliser'),
                'time'        => __('Brouillon'),
            ];
        }

        // ── WARNING: CDD contracts expiring within 30 days ────────────────
        $expiringContracts = Employee::where('contract_type', 'temporary')
            ->where('is_active', true)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>=', now())
            ->whereDate('end_date', '<=', now()->addDays(30))
            ->count();

        if ($expiringContracts > 0) {
            $soonest = Employee::where('contract_type', 'temporary')
                ->where('is_active', true)
                ->whereNotNull('end_date')
                ->whereDate('end_date', '>=', now())
                ->whereDate('end_date', '<=', now()->addDays(30))
                ->orderBy('end_date')
                ->value('end_date');

            $daysLeft = $soonest ? now()->diffInDays($soonest) : 0;

            $notifications[] = [
                'level'        => 'warning',
                'icon'         => 'calendar-clock',
                'title'        => __(':count contrat(s) CDD expirant dans 30 jours', ['count' => $expiringContracts]),
                'description'  => __('Le plus proche expire dans :days jour(s) — renouvelez ou terminez le contrat', ['days' => $daysLeft]),
                'action_url'   => \App\Filament\Resources\EmployeeResource::getUrl('index'),
                'action_label' => __('Voir les employés'),
                'time'         => __('Échéance proche'),
            ];
        }

        // ── INFO: Inactive employees with classrooms ────────────────────────
        $inactiveTeachers = Employee::where('is_active', false)
            ->where('is_teacher', true)
            ->whereHas('classrooms')
            ->count();

        if ($inactiveTeachers > 0) {
            $notifications[] = [
                'level'       => 'info',
                'icon'        => 'user-x',
                'title'       => __(':count enseignant(s) inactif(s) avec classes assignées', ['count' => $inactiveTeachers]),
                'description' => __('Ces enseignants inactifs sont encore affectés à des classes'),
                'action_url'  => \App\Filament\Resources\ClassroomResource::getUrl('index'),
                'action_label'=> __('Vérifier les classes'),
                'time'        => __('À vérifier'),
            ];
        }

        $criticalCount = count(array_filter($notifications, fn ($n) => $n['level'] === 'critical'));
        $warningCount  = count(array_filter($notifications, fn ($n) => $n['level'] === 'warning'));
        $infoCount     = count(array_filter($notifications, fn ($n) => $n['level'] === 'info'));

        return [
            'notifications' => $notifications,
            'totalCount'    => count($notifications),
            'criticalCount' => $criticalCount,
            'warningCount'  => $warningCount,
            'infoCount'     => $infoCount,
        ];
    }
}
