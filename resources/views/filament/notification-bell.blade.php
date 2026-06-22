@php
    use App\Models\Incident;
    use App\Models\Payment;
    use App\Models\Payroll;
    use App\Models\Classroom;

    $overdueCount    = Payment::where('status', 'pending')->whereNotNull('due_date')->whereDate('due_date', '<', now())->count();
    $incidentCount   = Incident::where('parent_notified', false)->count();
    $noTeacherCount  = Classroom::whereNull('teacher_id')->count();
    $payrollCount    = Payroll::where('status', 'finalized')->count();

    $totalAlerts  = ($overdueCount > 0 ? 1 : 0) + ($incidentCount > 0 ? 1 : 0) + ($noTeacherCount > 0 ? 1 : 0) + ($payrollCount > 0 ? 1 : 0);
    $hasCritical  = $overdueCount > 0 || Incident::where('parent_notified', false)->where('severity', 'high')->exists();
    $dashUrl      = url('/admin');
@endphp

<a href="{{ $dashUrl }}" title="Centre de notifications{{ $totalAlerts > 0 ? ' — '.$totalAlerts.' alerte'.($totalAlerts > 1 ? 's' : '') : '' }}"
   style="position:relative;display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;border:1px solid #e2e8f0;background:white;color:#475569;text-decoration:none;margin-inline-end:4px;transition:background .12s,border-color .12s;"
   onmouseover="this.style.background='#f8fafc';this.style.borderColor='#cbd5e1';"
   onmouseout="this.style.background='white';this.style.borderColor='#e2e8f0';">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path d="M13.73 21a2 2 0 01-3.46 0"/>
    </svg>
    @if($totalAlerts > 0)
    <span style="position:absolute;top:-5px;right:-5px;min-width:17px;height:17px;background:{{ $hasCritical ? '#ef4444' : '#f59e0b' }};color:white;font-size:9px;font-weight:800;border-radius:10px;display:flex;align-items:center;justify-content:center;padding:0 3px;border:2px solid white;line-height:1;">{{ $totalAlerts }}</span>
    @endif
</a>
