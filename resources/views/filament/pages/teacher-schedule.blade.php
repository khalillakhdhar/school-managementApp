<x-filament-panels::page>
@assets
<style>
/* ── Teacher Schedule — shared layout ────────────────────────────────── */
.ts-wrap{padding:0 0 40px}

/* ── LIST VIEW ───────────────────────────────────────────────────────── */
.ts-list-intro{color:#64748b;font-size:14px;margin:0 0 20px;line-height:1.5}
html.dark .ts-list-intro{color:#94a3b8}

.ts-cards-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px}
@media(max-width:640px){.ts-cards-grid{grid-template-columns:1fr}}

.ts-card{background:#ffffff;border:1.5px solid #e2e8f0;border-radius:14px;padding:20px;
  cursor:pointer;transition:transform .15s,box-shadow .15s,border-color .15s;position:relative;overflow:hidden}
.ts-card:hover{transform:translateY(-3px);box-shadow:0 10px 28px rgba(0,0,0,.1);border-color:#7c3aed}
html.dark .ts-card{background:#1e293b;border-color:#334155}
html.dark .ts-card:hover{border-color:#a78bfa;box-shadow:0 10px 28px rgba(0,0,0,.3)}

.ts-card-accent{position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#7c3aed,#a78bfa)}
.ts-card-accent.empty{background:linear-gradient(90deg,#94a3b8,#cbd5e1)}

.ts-avatar{width:44px;height:44px;background:linear-gradient(135deg,#7c3aed,#a78bfa);border-radius:12px;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
  font-size:15px;font-weight:800;color:#ffffff;letter-spacing:.5px}
.ts-card-head{display:flex;align-items:flex-start;gap:12px;margin-bottom:12px}
.ts-card-name{font-size:16px;font-weight:800;color:#0f172a;line-height:1.2}
html.dark .ts-card-name{color:#f1f5f9}
.ts-card-position{font-size:12px;color:#64748b;margin-top:2px}
html.dark .ts-card-position{color:#94a3b8}

.ts-card-tags{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;min-height:22px}
.ts-tag{background:#f5f3ff;color:#7c3aed;font-size:11px;font-weight:600;padding:3px 9px;border-radius:6px}
html.dark .ts-tag{background:#2e1065;color:#c4b5fd}
.ts-tag-class{background:#eff6ff;color:#1d4ed8}
html.dark .ts-tag-class{background:#1e3a5f;color:#93c5fd}

.ts-card-stats{display:flex;gap:12px;margin-bottom:16px}
.ts-stat-pill{background:#f8fafc;border:1px solid #e2e8f0;border-radius:7px;padding:6px 10px;text-align:center;flex:1}
html.dark .ts-stat-pill{background:#0f172a;border-color:#334155}
.ts-stat-pill-val{font-size:15px;font-weight:800;color:#0f172a;line-height:1}
html.dark .ts-stat-pill-val{color:#f1f5f9}
.ts-stat-pill-lbl{font-size:9.5px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-top:2px}

.ts-card-btn{width:100%;background:#7c3aed;color:#ffffff;border:none;border-radius:9px;
  padding:9px 14px;font-size:13px;font-weight:600;cursor:pointer;transition:background .15s;
  display:flex;align-items:center;justify-content:center;gap:6px}
.ts-card-btn:hover{background:#6d28d9}
.ts-card-btn svg{width:14px;height:14px}
.ts-card-btn.inactive{background:#e2e8f0;color:#94a3b8;cursor:default}
html.dark .ts-card-btn.inactive{background:#334155;color:#475569}

/* ── DETAIL VIEW ─────────────────────────────────────────────────────── */
.ts-detail-bar{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;
  gap:12px;margin-bottom:20px}
.ts-back-btn{display:inline-flex;align-items:center;gap:8px;background:#ffffff;border:1.5px solid #e2e8f0;
  border-radius:9px;padding:8px 16px;font-size:13px;font-weight:600;color:#475569;cursor:pointer;
  transition:border-color .15s,color .15s}
.ts-back-btn:hover{border-color:#7c3aed;color:#7c3aed}
html.dark .ts-back-btn{background:#1e293b;border-color:#334155;color:#94a3b8}
html.dark .ts-back-btn:hover{border-color:#a78bfa;color:#c4b5fd}
.ts-back-btn svg{width:15px;height:15px}

.ts-kpi-row{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px}
@media(max-width:700px){.ts-kpi-row{grid-template-columns:repeat(2,1fr)}}
.ts-kpi{background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px;
  border-top:3px solid transparent}
html.dark .ts-kpi{background:#1e293b;border-color:#334155}
.ts-kpi-lbl{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin-bottom:4px}
html.dark .ts-kpi-lbl{color:#94a3b8}
.ts-kpi-val{font-size:24px;font-weight:800;color:#0f172a;line-height:1}
html.dark .ts-kpi-val{color:#f1f5f9}

/* ── TIMETABLE GRID TABLE ────────────────────────────────────────────── */
.ts-table-outer{background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;
  overflow-x:auto}
html.dark .ts-table-outer{background:#1e293b;border-color:#334155}

.ts-table{width:100%;border-collapse:collapse;min-width:500px}

.ts-th-day{padding:12px 16px;background:#f8fafc;font-size:11px;font-weight:700;text-transform:uppercase;
  letter-spacing:.7px;color:#64748b;border-bottom:1.5px solid #e2e8f0;white-space:nowrap;
  min-width:90px;position:sticky;left:0;z-index:2}
html.dark .ts-th-day{background:#0f172a;color:#94a3b8;border-color:#334155}

.ts-th-slot{padding:10px 14px;background:#f8fafc;font-size:11px;font-weight:700;color:#374151;
  border-bottom:1.5px solid #e2e8f0;text-align:center;min-width:150px;border-left:1px solid #f1f5f9}
html.dark .ts-th-slot{background:#0f172a;color:#94a3b8;border-color:#334155;border-left-color:#1e293b}
.ts-slot-time{font-size:13px;font-weight:800;color:#0f172a;display:block;line-height:1.3}
html.dark .ts-slot-time{color:#f1f5f9}
.ts-slot-end{font-size:10px;color:#94a3b8;font-weight:500}

.ts-td-day{padding:10px 16px;background:#f8fafc;font-size:12px;font-weight:700;color:#374151;
  border-bottom:1px solid #f1f5f9;white-space:nowrap;position:sticky;left:0;z-index:1}
html.dark .ts-td-day{background:#0f172a;color:#94a3b8;border-color:#1e293b}

.ts-td-cell{padding:6px 8px;vertical-align:top;border-bottom:1px solid #f1f5f9;
  border-left:1px solid #f1f5f9;min-width:150px}
html.dark .ts-td-cell{border-color:#1e293b}
.ts-tr:last-child .ts-td-day,.ts-tr:last-child .ts-td-cell{border-bottom:none}

.ts-session{background:#ffffff;border:1px solid #e2e8f0;border-radius:9px;padding:10px 12px;
  border-left:3px solid #7c3aed;transition:box-shadow .12s}
.ts-session:hover{box-shadow:0 3px 12px rgba(0,0,0,.09)}
html.dark .ts-session{background:#1e293b;border-color:#334155}
.ts-session-subj{font-size:12.5px;font-weight:700;color:#0f172a;margin-bottom:3px;line-height:1.3}
html.dark .ts-session-subj{color:#f1f5f9}
.ts-session-class{font-size:11px;color:#64748b;display:flex;align-items:center;gap:4px;margin-bottom:2px}
html.dark .ts-session-class{color:#94a3b8}
.ts-session-class svg{width:11px;height:11px;flex-shrink:0}
.ts-session-room{display:inline-block;background:#f5f3ff;color:#7c3aed;font-size:10px;font-weight:600;
  padding:2px 7px;border-radius:4px;margin-top:4px}
html.dark .ts-session-room{background:#2e1065;color:#c4b5fd}

.ts-cell-free{text-align:center;color:#cbd5e1;font-size:16px;padding:8px 0;line-height:1}

/* ── EMPTY STATE ─────────────────────────────────────────────────────── */
.ts-empty{text-align:center;padding:56px 24px;color:#64748b}
.ts-empty svg{width:52px;height:52px;color:#cbd5e1;margin:0 auto 16px;display:block}
.ts-empty-title{font-size:15px;font-weight:700;color:#374151;margin-bottom:8px}
html.dark .ts-empty-title{color:#cbd5e1}
</style>
@endassets

<div class="ts-wrap">

@if(!$this->employeeId)
{{-- ══════════════════════════════════════════════════════════════════════
     STATE 1 — Teacher selection list
     ══════════════════════════════════════════════════════════════════════ --}}
<p class="ts-list-intro">
    Sélectionnez un enseignant pour consulter son planning hebdomadaire, ses heures de cours et les classes dont il a la charge.
</p>

<div class="ts-cards-grid">
    @forelse($this->teachersList as $t)
    @php $initials = collect(explode(' ', $t['name']))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->implode(''); @endphp
    <div class="ts-card">
        <div class="ts-card-accent {{ $t['hasData'] ? 'has' : 'empty' }}"></div>

        <div class="ts-card-head">
            <div class="ts-avatar">{{ strtoupper($initials) }}</div>
            <div>
                <div class="ts-card-name">{{ $t['name'] }}</div>
                @if($t['position'])
                <div class="ts-card-position">{{ $t['position'] }}</div>
                @endif
            </div>
        </div>

        @if($t['subjects'] || $t['classes'])
        <div class="ts-card-tags">
            @if($t['subjects'])
            <span class="ts-tag">{{ $t['subjects'] }}</span>
            @endif
            @if($t['classes'])
            <span class="ts-tag ts-tag-class">{{ $t['classes'] }}</span>
            @endif
        </div>
        @else
        <div class="ts-card-tags"></div>
        @endif

        <div class="ts-card-stats">
            <div class="ts-stat-pill">
                <div class="ts-stat-pill-val">{{ $t['sessions'] }}</div>
                <div class="ts-stat-pill-lbl">Séances</div>
            </div>
            <div class="ts-stat-pill">
                <div class="ts-stat-pill-val">{{ $t['hours'] }}h</div>
                <div class="ts-stat-pill-lbl">/ sem.</div>
            </div>
        </div>

        <button class="ts-card-btn {{ !$t['hasData'] ? 'inactive' : '' }}"
                @if($t['hasData']) wire:click="selectTeacher({{ $t['id'] }})" @endif>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
            </svg>
            @if($t['hasData']) Voir le planning @else Aucune séance planifiée @endif
        </button>
    </div>
    @empty
    <div style="grid-column:1/-1" class="ts-empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
        </svg>
        <div class="ts-empty-title">Aucun enseignant actif</div>
        <p>Ajoutez des enseignants dans le module RH et activez l'option "Enseignant".</p>
    </div>
    @endforelse
</div>

@else
{{-- ══════════════════════════════════════════════════════════════════════
     STATE 2 — Teacher schedule detail
     ══════════════════════════════════════════════════════════════════════ --}}

<div class="ts-detail-bar">
    <button class="ts-back-btn" wire:click="backToList()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        Retour aux enseignants
    </button>
</div>

{{-- KPI cards --}}
<div class="ts-kpi-row">
    @foreach($this->detailStats as $stat)
    <div class="ts-kpi" style="border-top-color:{{ $stat['color'] }}">
        <div class="ts-kpi-lbl">{{ $stat['label'] }}</div>
        <div class="ts-kpi-val" style="color:{{ $stat['color'] }}">{{ $stat['value'] }}</div>
    </div>
    @endforeach
</div>

{{-- Timetable grid --}}
@php $data = $this->timetableData; @endphp

@if($data['empty'])
<div class="ts-empty">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
    </svg>
    <div class="ts-empty-title">Aucune séance planifiée pour cet enseignant</div>
    <p>Assignez des séances à cet enseignant dans le module Emplois du temps.</p>
</div>
@else
<div class="ts-table-outer">
    <table class="ts-table">
        <thead>
            <tr>
                <th class="ts-th-day">Jour</th>
                @foreach($data['slots'] as $slot)
                <th class="ts-th-slot">
                    <span class="ts-slot-time">{{ $slot['start'] }}</span>
                    <span class="ts-slot-end">→ {{ $slot['end'] }}</span>
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data['grid'] as $row)
            <tr class="ts-tr">
                <td class="ts-td-day">{{ $row['day'] }}</td>
                @foreach($row['cells'] as $cell)
                <td class="ts-td-cell">
                    @if($cell)
                    <div class="ts-session" style="border-left-color:{{ $cell->subject?->color ?? '#7c3aed' }}">
                        <div class="ts-session-subj">{{ $cell->subject?->name ?? '—' }}</div>
                        <div class="ts-session-class">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                            </svg>
                            {{ $cell->classroom?->full_name ?? '—' }}
                        </div>
                        @if($cell->room)
                        <span class="ts-session-room">{{ $cell->room }}</span>
                        @endif
                    </div>
                    @else
                    <div class="ts-cell-free">·</div>
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endif
</div>
</x-filament-panels::page>
