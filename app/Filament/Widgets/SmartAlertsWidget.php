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
    protected static bool $isLazy = true;
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
                'title'       => "{$overdueCount} paiement" . ($overdueCount > 1 ? 's' : '') . " en retard",
                'description' => number_format($overdueTotal, 3) . ' TND impayés · le plus ancien depuis ' . $daysLate . ' jours',
                'action_url'  => \App\Filament\Resources\PaymentResource::getUrl('index'),
                'action_label'=> 'Voir les paiements',
                'time'        => 'Mis à jour maintenant',
            ];
        }

        // ── CRITICAL: High-severity unnotified incidents ────────────────────
        $highIncidents = Incident::where('parent_notified', false)
            ->where('severity', 'high')->count();

        if ($highIncidents > 0) {
            $notifications[] = [
                'level'       => 'critical',
                'icon'        => 'triangle-alert',
                'title'       => "{$highIncidents} incident" . ($highIncidents > 1 ? 's' : '') . " grave non notifié" . ($highIncidents > 1 ? 's' : ''),
                'description' => 'Les parents concernés n\'ont pas encore été informés',
                'action_url'  => \App\Filament\Resources\IncidentResource::getUrl('index'),
                'action_label'=> 'Notifier les parents',
                'time'        => 'Action requise',
            ];
        }

        // ── WARNING: Medium/low unnotified incidents ────────────────────────
        $otherIncidents = Incident::where('parent_notified', false)
            ->whereIn('severity', ['medium', 'low'])->count();

        if ($otherIncidents > 0) {
            $notifications[] = [
                'level'       => 'warning',
                'icon'        => 'alert-circle',
                'title'       => "{$otherIncidents} incident" . ($otherIncidents > 1 ? 's' : '') . " en attente de notification",
                'description' => 'Incidents mineurs ou moyens sans notification parentale',
                'action_url'  => \App\Filament\Resources\IncidentResource::getUrl('index'),
                'action_label'=> 'Voir les incidents',
                'time'        => 'Aujourd\'hui',
            ];
        }

        // ── WARNING: Classes without teacher ───────────────────────────────
        $classesNoTeacher = Classroom::whereNull('teacher_id')->count();

        if ($classesNoTeacher > 0) {
            $notifications[] = [
                'level'       => 'warning',
                'icon'        => 'building-2',
                'title'       => "{$classesNoTeacher} classe" . ($classesNoTeacher > 1 ? 's' : '') . " sans enseignant",
                'description' => 'Ces classes n\'ont pas encore d\'enseignant titulaire assigné',
                'action_url'  => \App\Filament\Resources\ClassroomResource::getUrl('index'),
                'action_label'=> 'Assigner un enseignant',
                'time'        => 'À planifier',
            ];
        }

        // ── WARNING: Finalized payroll not paid ────────────────────────────
        $pendingPayroll = Payroll::where('status', 'finalized')->count();

        if ($pendingPayroll > 0) {
            $pendingAmount = (float) Payroll::where('status', 'finalized')->sum('net_salary');
            $notifications[] = [
                'level'       => 'warning',
                'icon'        => 'wallet',
                'title'       => "{$pendingPayroll} fiche" . ($pendingPayroll > 1 ? 's' : '') . " de paie finalisée" . ($pendingPayroll > 1 ? 's' : '') . " non payée" . ($pendingPayroll > 1 ? 's' : ''),
                'description' => number_format($pendingAmount, 3) . ' TND à virer aux employés',
                'action_url'  => \App\Filament\Resources\PayrollResource::getUrl('index'),
                'action_label'=> 'Voir les fiches de paie',
                'time'        => 'En attente',
            ];
        }

        // ── INFO: Draft payroll items ───────────────────────────────────────
        $draftPayroll = Payroll::where('status', 'draft')->count();

        if ($draftPayroll > 0) {
            $notifications[] = [
                'level'       => 'info',
                'icon'        => 'file-text',
                'title'       => "{$draftPayroll} fiche" . ($draftPayroll > 1 ? 's' : '') . " de paie en brouillon",
                'description' => 'À finaliser avant envoi en paiement',
                'action_url'  => \App\Filament\Resources\PayrollResource::getUrl('index'),
                'action_label'=> 'Finaliser',
                'time'        => 'Brouillon',
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
                'title'        => "{$expiringContracts} contrat" . ($expiringContracts > 1 ? 's' : '') . " CDD expirant dans 30 jours",
                'description'  => 'Le plus proche expire dans ' . $daysLeft . ' jour' . ($daysLeft > 1 ? 's' : '') . ' — renouvelez ou terminez le contrat',
                'action_url'   => \App\Filament\Resources\EmployeeResource::getUrl('index'),
                'action_label' => 'Voir les employés',
                'time'         => 'Échéance proche',
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
                'title'       => "{$inactiveTeachers} enseignant" . ($inactiveTeachers > 1 ? 's' : '') . " inactif" . ($inactiveTeachers > 1 ? 's' : '') . " avec classes assignées",
                'description' => 'Ces enseignants inactifs sont encore affectés à des classes',
                'action_url'  => \App\Filament\Resources\ClassroomResource::getUrl('index'),
                'action_label'=> 'Vérifier les classes',
                'time'        => 'À vérifier',
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
