<x-filament-panels::page>
<div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Selectors --}}
    <div class="no-print" style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:16px 20px;box-shadow:0 1px 3px rgba(16,24,40,.05);display:flex;flex-wrap:wrap;gap:18px;align-items:flex-end;">
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:5px;">{{ __('Classe') }}</label>
            <select wire:model.live="classroomId" style="border:1px solid #dde3ea;border-radius:8px;padding:8px 12px;font-size:14px;min-width:180px;background:#fff;color:#0f172a;">
                @foreach($this->classrooms() as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:5px;">{{ __('Élève') }}</label>
            <select wire:model.live="studentId" style="border:1px solid #dde3ea;border-radius:8px;padding:8px 12px;font-size:14px;min-width:200px;background:#fff;color:#0f172a;">
                @forelse($this->students() as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@empty<option value="">—</option>@endforelse
            </select>
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:5px;">{{ __('Trimestre') }}</label>
            <select wire:model.live="term" style="border:1px solid #dde3ea;border-radius:8px;padding:8px 12px;font-size:14px;background:#fff;color:#0f172a;">
                <option value="T1">{{ __('1er trimestre') }}</option><option value="T2">{{ __('2e trimestre') }}</option><option value="T3">{{ __('3e trimestre') }}</option>
            </select>
        </div>
        <div style="flex:1;"></div>
        @if($report && $report['hasGrades'])
        <a href="{{ route('pdf.bulletin', ['student' => $report['student']->id, 'term' => $report['term']]) }}" target="_blank"
           style="background:#2563eb;color:#fff;border-radius:8px;padding:10px 20px;font-size:13px;font-weight:600;text-decoration:none;">⬇ {{ __('Télécharger PDF') }}</a>
        @endif
        <button onclick="window.print()" type="button" style="background:#0f172a;color:#fff;border:none;border-radius:8px;padding:10px 20px;font-size:13px;font-weight:600;cursor:pointer;">🖨️ {{ __('Imprimer') }}</button>
    </div>

    @if($report)
        @include('partials.report-card', ['report' => $report, 'schoolName' => $schoolName])
    @else
        <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:48px;text-align:center;color:#94a3b8;">{{ __('Sélectionnez un élève.') }}</div>
    @endif

</div>
</x-filament-panels::page>
