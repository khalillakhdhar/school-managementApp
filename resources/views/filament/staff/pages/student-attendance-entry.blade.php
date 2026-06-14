<x-filament-panels::page>
@php($classes = $this->myClasses())

@if($classes->isEmpty())
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:14px;padding:24px;color:#92400e;">
        <strong>Aucune classe rattachée à votre compte.</strong>
        <div style="margin-top:6px;font-size:13px;">Vous devez être titulaire d'une classe ou avoir des séances à l'emploi du temps.</div>
    </div>
@else
<div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Controls --}}
    <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:16px 20px;box-shadow:0 1px 3px rgba(16,24,40,.05);display:flex;flex-wrap:wrap;gap:18px;align-items:flex-end;">
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:5px;">Classe</label>
            <select wire:model.live="classroomId" style="border:1px solid #dde3ea;border-radius:8px;padding:8px 12px;font-size:14px;min-width:200px;background:#fff;color:#0f172a;">
                @foreach($classes as $c)
                <option value="{{ $c['id'] }}">{{ $c['full'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:5px;">Date</label>
            <input type="date" wire:model.live="date" style="border:1px solid #dde3ea;border-radius:8px;padding:8px 12px;font-size:14px;background:#fff;color:#0f172a;">
        </div>
        <div style="flex:1;"></div>
        <div style="display:flex;gap:8px;">
            <button wire:click="markAll('present')" type="button" style="background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;border-radius:8px;padding:8px 14px;font-size:13px;font-weight:600;cursor:pointer;">Tous présents</button>
            <button wire:click="save" type="button" style="background:#2563eb;color:#fff;border:none;border-radius:8px;padding:9px 20px;font-size:13.5px;font-weight:600;cursor:pointer;box-shadow:0 1px 3px rgba(37,99,235,.3);">Enregistrer l'appel</button>
        </div>
    </div>

    {{-- Summary --}}
    @php($s = $this->summary)
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
        @foreach([['Présents',$s['present'],'#10b981','#ecfdf5'],['Absents',$s['absent'],'#ef4444','#fef2f2'],['Retards',$s['late'],'#f59e0b','#fffbeb'],['Excusés',$s['excused'],'#6366f1','#eef2ff']] as $kpi)
        <div style="background:{{ $kpi[3] }};border-radius:12px;padding:12px 16px;">
            <div style="font-size:24px;font-weight:800;color:{{ $kpi[2] }};line-height:1;">{{ $kpi[1] }}</div>
            <div style="font-size:11.5px;font-weight:600;color:#64748b;margin-top:4px;">{{ $kpi[0] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Roster --}}
    <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(16,24,40,.05);">
        @forelse($this->students as $student)
        <div style="display:flex;align-items:center;gap:14px;padding:12px 18px;border-bottom:1px solid #f1f4f8;">
            <div style="width:34px;height:34px;border-radius:50%;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;">
                {{ strtoupper(mb_substr($student->first_name,0,1).mb_substr($student->last_name,0,1)) }}
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:14px;font-weight:600;color:#0f172a;">{{ $student->full_name }}</div>
                <div style="font-size:11.5px;color:#94a3b8;">{{ $student->id_number }}</div>
            </div>
            <div style="display:flex;gap:6px;">
                @foreach([['present','P','#10b981'],['late','R','#f59e0b'],['absent','A','#ef4444'],['excused','E','#6366f1']] as $opt)
                @php($active = ($statuses[$student->id] ?? 'present') === $opt[0])
                <button type="button" wire:click="setStatus({{ $student->id }}, '{{ $opt[0] }}')" title="{{ ['present'=>'Présent','late'=>'Retard','absent'=>'Absent','excused'=>'Excusé'][$opt[0]] }}"
                    style="width:38px;height:38px;border-radius:9px;font-size:14px;font-weight:700;cursor:pointer;transition:all .1s;
                    border:1.5px solid {{ $active ? $opt[2] : '#e5e9f0' }};
                    background:{{ $active ? $opt[2] : '#fff' }};
                    color:{{ $active ? '#fff' : '#94a3b8' }};">
                    {{ $opt[1] }}
                </button>
                @endforeach
            </div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:#94a3b8;font-size:14px;">Aucun élève dans cette classe.</div>
        @endforelse
    </div>

</div>
@endif
</x-filament-panels::page>
