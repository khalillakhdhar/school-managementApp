<?php
namespace App\Filament\Parent\Pages;

use App\Models\SchoolParent;
use Filament\Pages\Page;

class ParentDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?string $title = 'Mon Tableau de Bord';

    public function getView(): string { return 'filament.parent.pages.parent-dashboard'; }

    public ?SchoolParent $schoolParent = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->schoolParent = SchoolParent::where('user_id', $user->id)->first();

        if (!$this->schoolParent) {
            abort(403, 'Aucun profil parent associé à ce compte.');
        }
    }

    public function getStudentsWithDetails(): array
    {
        if (!$this->schoolParent) {
            return [];
        }

        return $this->schoolParent->students()->with(['payments', 'services', 'incidents'])->get()->map(function ($student) {
            $totalDue     = $student->services()->sum('services.amount');
            $totalPaid    = $student->payments()->where('status', 'paid')->sum('amount');
            $outstanding  = max(0, $totalDue - $totalPaid);
            $incidents    = $student->incidents()->orderBy('incident_date', 'desc')->take(3)->get();

            return [
                'student'     => $student,
                'totalDue'    => $totalDue,
                'totalPaid'   => $totalPaid,
                'outstanding' => $outstanding,
                'incidents'   => $incidents,
                'payments'    => $student->payments()->orderBy('payment_date', 'desc')->take(5)->get(),
            ];
        })->toArray();
    }
}
