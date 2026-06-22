<x-filament-panels::page>
@php($classes = $this->myClasses())
@php($subjects = $this->subjectsForClass())

@if($classes->isEmpty())
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:14px;padding:24px;color:#92400e;">
        <strong>{{ __('Aucune classe à votre emploi du temps.') }}</strong>
    </div>
@else
<div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Controls --}}
    <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:16px 20px;box-shadow:0 1px 3px rgba(16,24,40,.05);display:flex;flex-wrap:wrap;gap:18px;align-items:flex-end;">
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:5px;">{{ __('Classe') }}</label>
            <select wire:model.live="classroomId" style="border:1px solid #dde3ea;border-radius:8px;padding:8px 12px;font-size:14px;min-width:180px;background:#fff;color:#0f172a;">
                @foreach($classes as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:5px;">{{ __('Matière') }}</label>
            <select wire:model.live="subjectId" style="border:1px solid #dde3ea;border-radius:8px;padding:8px 12px;font-size:14px;min-width:180px;background:#fff;color:#0f172a;">
                @forelse($subjects as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@empty<option value="">— {{ __('aucune') }} —</option>@endforelse
            </select>
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:5px;">{{ __('Trimestre') }}</label>
            <select wire:model.live="term" style="border:1px solid #dde3ea;border-radius:8px;padding:8px 12px;font-size:14px;background:#fff;color:#0f172a;">
                <option value="T1">{{ __('1er trimestre') }}</option><option value="T2">{{ __('2e trimestre') }}</option><option value="T3">{{ __('3e trimestre') }}</option>
            </select>
        </div>
        <div style="flex:1;"></div>
        <button wire:click="save" type="button" style="background:#2563eb;color:#fff;border:none;border-radius:8px;padding:10px 22px;font-size:13.5px;font-weight:600;cursor:pointer;box-shadow:0 1px 3px rgba(37,99,235,.3);">{{ __('Enregistrer les notes') }}</button>
    </div>

    {{-- Roster --}}
    @if($subjects->isEmpty())
        <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:36px;text-align:center;color:#94a3b8;">{{ __("Vous n'enseignez aucune matière dans cette classe.") }}</div>
    @else
    <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(16,24,40,.05);">
        @forelse($this->students as $student)
        <div style="display:flex;align-items:center;gap:14px;padding:11px 18px;border-bottom:1px solid #f1f4f8;">
            <div style="width:32px;height:32px;border-radius:50%;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;">
                {{ strtoupper(mb_substr($student->first_name,0,1).mb_substr($student->last_name,0,1)) }}
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:14px;font-weight:600;color:#0f172a;">{{ $student->full_name }}</div>
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
                <input type="number" min="0" max="20" step="0.25" wire:model="scores.{{ $student->id }}" placeholder="—"
                    style="width:78px;border:1px solid #dde3ea;border-radius:8px;padding:8px 10px;font-size:14px;font-weight:700;text-align:center;color:#0f172a;">
                <span style="font-size:13px;color:#94a3b8;font-weight:600;">/ 20</span>
            </div>
        </div>
        @empty
        <div style="padding:36px;text-align:center;color:#94a3b8;font-size:14px;">{{ __('Aucun élève dans cette classe.') }}</div>
        @endforelse
    </div>
    @endif

</div>
@endif
</x-filament-panels::page>
