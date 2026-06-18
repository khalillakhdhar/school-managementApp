<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Services\ReportCardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DocumentPdfController extends Controller
{
    /** Bulletin trimestriel d'un élève en PDF. */
    public function bulletin(Student $student, string $term): Response
    {
        abort_unless(in_array($term, ['T1', 'T2', 'T3'], true), 404);

        $user = Auth::user();
        // Sécurité : admin OK ; parent uniquement ses enfants.
        if ($user->role === 'parent') {
            $allowed = $user->parent?->students()->whereKey($student->id)->exists();
            abort_unless($allowed, 403);
        } elseif (! in_array($user->role, ['admin', 'teacher', 'employee'], true)) {
            abort(403);
        }

        $report = ReportCardService::forStudent($student->load('classroom'), $term);
        $pdf = Pdf::loadView('pdf.bulletin', [
            'report'     => $report,
            'schoolName' => SchoolSetting::get('school_name', 'EliteCampus'),
        ])->setPaper('a4');

        return $pdf->download('bulletin-' . str()->slug($student->full_name) . "-{$term}.pdf");
    }

    /** Fiche de paie d'un employé en PDF. */
    public function payslip(Payroll $payroll): Response
    {
        $user = Auth::user();
        // Sécurité : admin OK ; staff uniquement sa propre fiche.
        if (in_array($user->role, ['teacher', 'employee'], true)) {
            abort_unless($user->employee && $payroll->employee_id === $user->employee->id, 403);
        } elseif ($user->role !== 'admin') {
            abort(403);
        }

        $payroll->load('employee');
        $pdf = Pdf::loadView('pdf.payslip', [
            'p'          => $payroll,
            'schoolName' => SchoolSetting::get('school_name', 'EliteCampus'),
        ])->setPaper('a4');

        $period = ($payroll->month ?? '') . '-' . ($payroll->year ?? '');

        return $pdf->download('fiche-paie-' . str()->slug($payroll->employee?->full_name ?? 'employe') . "-{$period}.pdf");
    }
}
