<x-filament-panels::page>
@assets
<style>
/* ── Class Timetable — shared layout ─────────────────────────────────── */
.ct-wrap{padding:0 0 40px}

/* ── LIST VIEW ───────────────────────────────────────────────────────── */
.ct-list-intro{color:#64748b;font-size:14px;margin:0 0 20px;line-height:1.5}
html.dark .ct-list-intro{color:#94a3b8}
.ct-summary-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px}
@media(max-width:700px){.ct-summary-grid{grid-template-columns:repeat(2,1fr)}}
.ct-summary-kpi{background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;
  display:flex;align-items:center;gap:12px}
html.dark .ct-summary-kpi{background:#1e293b;border-color:#334155}
.ct-summary-icon{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ct-summary-icon svg{width:18px;height:18px}
.ct-summary-val{font-size:20px;font-weight:800;color:#0f172a;line-height:1}
html.dark .ct-summary-val{color:#f1f5f9}
.ct-summary-lbl{font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px;margin-top:2px}
html.dark .ct-summary-lbl{color:#94a3b8}

.ct-cards-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:18px}
@media(max-width:640px){.ct-cards-grid{grid-template-columns:1fr}}

.ct-card{background:#ffffff;border:1.5px solid #e2e8f0;border-radius:14px;padding:20px;
  cursor:pointer;transition:transform .15s,box-shadow .15s,border-color .15s;position:relative;overflow:hidden}
.ct-card:hover{transform:translateY(-3px);box-shadow:0 10px 28px rgba(0,0,0,.1);border-color:#1d4ed8}
html.dark .ct-card{background:#1e293b;border-color:#334155}
html.dark .ct-card:hover{border-color:#3b82f6;box-shadow:0 10px 28px rgba(0,0,0,.3)}

.ct-card-accent{position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#1d4ed8,#3b82f6)}
.ct-card-accent.empty{background:linear-gradient(90deg,#94a3b8,#cbd5e1)}

.ct-card-head{display:flex;align-items:flex-start;gap:12px;margin-bottom:14px}
.ct-level-badge{min-width:40px;height:40px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;
  justify-content:center;font-size:11px;font-weight:800;color:#1d4ed8;letter-spacing:.5px;flex-shrink:0;text-align:center;padding:0 4px}
html.dark .ct-level-badge{background:#1e3a5f;color:#93c5fd}
.ct-card-name{font-size:17px;font-weight:800;color:#0f172a;line-height:1.2}
html.dark .ct-card-name{color:#f1f5f9}
.ct-card-level{font-size:12px;color:#64748b;margin-top:2px}
html.dark .ct-card-level{color:#94a3b8}

.ct-card-teacher{font-size:12.5px;color:#475569;margin-bottom:14px;display:flex;align-items:center;gap:6px}
html.dark .ct-card-teacher{color:#94a3b8}
.ct-card-teacher svg{width:13px;height:13px;flex-shrink:0;color:#1d4ed8}

.ct-card-stats{display:flex;gap:12px;margin-bottom:16px}
.ct-stat-pill{background:#f8fafc;border:1px solid #e2e8f0;border-radius:7px;padding:6px 10px;text-align:center;flex:1}
html.dark .ct-stat-pill{background:#0f172a;border-color:#334155}
.ct-stat-pill-val{font-size:15px;font-weight:800;color:#0f172a;line-height:1}
html.dark .ct-stat-pill-val{color:#f1f5f9}
.ct-stat-pill-lbl{font-size:9.5px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-top:2px}

.ct-card-status{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;
  padding:3px 9px;border-radius:6px;margin-bottom:14px}
.ct-card-status.has{background:#ecfdf5;color:#059669}
.ct-card-status.none{background:#f1f5f9;color:#94a3b8}
html.dark .ct-card-status.has{background:#064e3b;color:#6ee7b7}
html.dark .ct-card-status.none{background:#1e293b;color:#475569}

.ct-card-btn{width:100%;background:#1d4ed8;color:#ffffff;border:none;border-radius:9px;
  padding:9px 14px;font-size:13px;font-weight:600;cursor:pointer;transition:background .15s;
  display:flex;align-items:center;justify-content:center;gap:6px}
.ct-card-btn:hover{background:#1e40af}
.ct-card-btn svg{width:14px;height:14px}

/* ── DETAIL VIEW ─────────────────────────────────────────────────────── */
.ct-detail-bar{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;
  gap:12px;margin-bottom:20px}
.ct-back-btn{display:inline-flex;align-items:center;gap:8px;background:#ffffff;border:1.5px solid #e2e8f0;
  border-radius:9px;padding:8px 16px;font-size:13px;font-weight:600;color:#475569;cursor:pointer;
  transition:border-color .15s,color .15s}
.ct-back-btn:hover{border-color:#1d4ed8;color:#1d4ed8}
html.dark .ct-back-btn{background:#1e293b;border-color:#334155;color:#94a3b8}
html.dark .ct-back-btn:hover{border-color:#3b82f6;color:#93c5fd}
.ct-back-btn svg{width:15px;height:15px}

.ct-add-btn{display:inline-flex;align-items:center;gap:8px;background:#1d4ed8;border:none;
  border-radius:9px;padding:9px 18px;font-size:13px;font-weight:600;color:#ffffff;cursor:pointer;
  text-decoration:none;transition:background .15s}
.ct-add-btn:hover{background:#1e40af}
.ct-add-btn svg{width:14px;height:14px}

.ct-kpi-row{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px}
@media(max-width:700px){.ct-kpi-row{grid-template-columns:repeat(2,1fr)}}
.ct-kpi{background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px;
  border-top:3px solid transparent}
html.dark .ct-kpi{background:#1e293b;border-color:#334155}
.ct-kpi-lbl{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin-bottom:4px}
html.dark .ct-kpi-lbl{color:#94a3b8}
.ct-kpi-val{font-size:24px;font-weight:800;color:#0f172a;line-height:1}
html.dark .ct-kpi-val{color:#f1f5f9}

/* ── TIMETABLE GRID TABLE ────────────────────────────────────────────── */
.ct-table-outer{background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;
  overflow-x:auto}
html.dark .ct-table-outer{background:#1e293b;border-color:#334155}

.ct-table{width:100%;border-collapse:collapse;min-width:500px}

.ct-th-day{padding:12px 16px;background:#f8fafc;font-size:11px;font-weight:700;text-transform:uppercase;
  letter-spacing:.7px;color:#64748b;border-bottom:1.5px solid #e2e8f0;white-space:nowrap;
  min-width:90px;position:sticky;left:0;z-index:2}
html.dark .ct-th-day{background:#0f172a;color:#94a3b8;border-color:#334155}

.ct-th-slot{padding:10px 14px;background:#f8fafc;font-size:11px;font-weight:700;color:#374151;
  border-bottom:1.5px solid #e2e8f0;text-align:center;min-width:140px;border-left:1px solid #f1f5f9}
html.dark .ct-th-slot{background:#0f172a;color:#94a3b8;border-color:#334155;border-left-color:#1e293b}
.ct-slot-time{font-size:13px;font-weight:800;color:#0f172a;display:block;line-height:1.3}
html.dark .ct-slot-time{color:#f1f5f9}
.ct-slot-end{font-size:10px;color:#94a3b8;font-weight:500}

.ct-td-day{padding:10px 16px;background:#f8fafc;font-size:12px;font-weight:700;color:#374151;
  border-bottom:1px solid #f1f5f9;white-space:nowrap;position:sticky;left:0;z-index:1}
html.dark .ct-td-day{background:#0f172a;color:#94a3b8;border-color:#1e293b}

.ct-td-cell{padding:6px 8px;vertical-align:top;border-bottom:1px solid #f1f5f9;
  border-left:1px solid #f1f5f9;min-width:140px}
html.dark .ct-td-cell{border-color:#1e293b}
.ct-tr:last-child .ct-td-day,.ct-tr:last-child .ct-td-cell{border-bottom:none}

.ct-session{background:#ffffff;border:1px solid #e2e8f0;border-radius:9px;padding:10px 12px;
  border-left:3px solid #1d4ed8;transition:box-shadow .12s}
.ct-session:hover{box-shadow:0 3px 12px rgba(0,0,0,.09)}
html.dark .ct-session{background:#1e293b;border-color:#334155}
.ct-session-subj{font-size:12.5px;font-weight:700;color:#0f172a;margin-bottom:3px;line-height:1.3}
html.dark .ct-session-subj{color:#f1f5f9}
.ct-session-teacher{font-size:11px;color:#64748b;display:flex;align-items:center;gap:4px}
html.dark .ct-session-teacher{color:#94a3b8}
.ct-session-teacher svg{width:11px;height:11px;flex-shrink:0}
.ct-session-room{display:inline-block;background:#f1f5f9;color:#64748b;font-size:10px;font-weight:600;
  padding:2px 7px;border-radius:4px;margin-top:5px}
html.dark .ct-session-room{background:#334155;color:#94a3b8}

.ct-cell-free{text-align:center;color:#cbd5e1;font-size:16px;padding:8px 0;line-height:1}

/* ── EMPTY STATE ─────────────────────────────────────────────────────── */
.ct-empty{text-align:center;padding:56px 24px;color:#64748b}
.ct-empty svg{width:52px;height:52px;color:#cbd5e1;margin:0 auto 16px;display:block}
.ct-empty-title{font-size:15px;font-weight:700;color:#374151;margin-bottom:8px}
html.dark .ct-empty-title{color:#cbd5e1}
.ct-empty-link{display:inline-block;background:#1d4ed8;color:#fff;border-radius:9px;
  padding:9px 20px;font-size:13px;font-weight:600;text-decoration:none;margin-top:16px}
.ct-empty-link:hover{background:#1e40af}
</style>
@endassets

<div class="ct-wrap">

@if(!$this->classroomId)
{{-- ══════════════════════════════════════════════════════════════════════
     STATE 1 — Class selection list
     ══════════════════════════════════════════════════════════════════════ --}}
@php
$cl = $this->classroomsList;
$totalClasses   = $cl->count();
$plannedClasses = $cl->filter(fn($c) => $c['hasData'])->count();
$totalSessions  = $cl->sum('sessions');
$totalHours     = $cl->sum(fn($c) => (float) str_replace(',', '.', $c['hours']));
$totalSubjects  = $cl->sum('subjects');
@endphp
<div class="ct-summary-grid">
    <div class="ct-summary-kpi">
        <div class="ct-summary-icon" style="background:#eff6ff">
            <svg fill="none" stroke="#1d4ed8" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
        </div>
        <div><div class="ct-summary-val">{{ $totalClasses }}</div><div class="ct-summary-lbl">Classes</div></div>
    </div>
    <div class="ct-summary-kpi">
        <div class="ct-summary-icon" style="background:#ecfdf5">
            <svg fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
        </div>
        <div><div class="ct-summary-val">{{ $plannedClasses }}<span style="font-size:13px;font-weight:500;color:#94a3b8">/{{ $totalClasses }}</span></div><div class="ct-summary-lbl">Planifiées</div></div>
    </div>
    <div class="ct-summary-kpi">
        <div class="ct-summary-icon" style="background:#f0fdf4">
            <svg fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
        </div>
        <div><div class="ct-summary-val">{{ $totalSessions }}</div><div class="ct-summary-lbl">Séances / sem.</div></div>
    </div>
    <div class="ct-summary-kpi">
        <div class="ct-summary-icon" style="background:#fdf4ff">
            <svg fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
        <div><div class="ct-summary-val">{{ $totalSubjects }}</div><div class="ct-summary-lbl">Matières</div></div>
    </div>
</div>

<p class="ct-list-intro">
    {{ $plannedClasses }} classe(s) sur {{ $totalClasses }} ont un emploi du temps planifié — {{ $totalSessions }} séances pour {{ number_format($totalHours, 1) }}h par semaine au total.
</p>

<div class="ct-cards-grid">
    @forelse($this->classroomsList as $c)
    <div class="ct-card">
        <div class="ct-card-accent {{ $c['hasData'] ? 'has' : 'empty' }}"></div>

        <div class="ct-card-head">
            <div class="ct-level-badge">{{ $c['code'] }}</div>
            <div>
                <div class="ct-card-name">{{ $c['name'] }}</div>
                <div class="ct-card-level">{{ $c['level'] }}</div>
            </div>
        </div>

        <div class="ct-card-teacher">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            {{ $c['teacher'] ?? 'Aucun enseignant assigné' }}
        </div>

        <div class="ct-card-stats">
            <div class="ct-stat-pill">
                <div class="ct-stat-pill-val">{{ $c['sessions'] }}</div>
                <div class="ct-stat-pill-lbl">Séances</div>
            </div>
            <div class="ct-stat-pill">
                <div class="ct-stat-pill-val">{{ $c['hours'] }}h</div>
                <div class="ct-stat-pill-lbl">/ sem.</div>
            </div>
            <div class="ct-stat-pill">
                <div class="ct-stat-pill-val">{{ $c['subjects'] }}</div>
                <div class="ct-stat-pill-lbl">Matières</div>
            </div>
        </div>

        <div class="ct-card-status {{ $c['hasData'] ? 'has' : 'none' }}">
            @if($c['hasData'])
            ● Emploi du temps planifié
            @else
            ○ Non planifié
            @endif
        </div>

        <button class="ct-card-btn" wire:click="selectClass({{ $c['id'] }})">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Voir l'emploi du temps
        </button>
    </div>
    @empty
    <div style="grid-column:1/-1" class="ct-empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
        </svg>
        <div class="ct-empty-title">Aucune classe créée</div>
        <p>Créez d'abord des classes dans le module Académique.</p>
    </div>
    @endforelse
</div>

@else
{{-- ══════════════════════════════════════════════════════════════════════
     STATE 2 — Timetable detail for selected class
     ══════════════════════════════════════════════════════════════════════ --}}

{{-- Action bar --}}
<div class="ct-detail-bar">
    <button class="ct-back-btn" wire:click="backToList()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        Retour aux classes
    </button>

    <a class="ct-add-btn" href="{{ route('filament.admin.resources.timetable-entries.create') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 5v14M5 12h14"/>
        </svg>
        Ajouter une séance
    </a>
</div>

{{-- KPI cards --}}
<div class="ct-kpi-row">
    @foreach($this->detailStats as $stat)
    <div class="ct-kpi" style="border-top-color:{{ $stat['color'] }}">
        <div class="ct-kpi-lbl">{{ $stat['label'] }}</div>
        <div class="ct-kpi-val" style="color:{{ $stat['color'] }}">{{ $stat['value'] }}</div>
    </div>
    @endforeach
</div>

{{-- Timetable grid --}}
@php $data = $this->timetableData; @endphp

@if($data['empty'])
<div class="ct-empty">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
    </svg>
    <div class="ct-empty-title">Aucune séance planifiée pour cette classe</div>
    <p>Commencez à construire l'emploi du temps en ajoutant des séances.</p>
    <a class="ct-empty-link" href="{{ route('filament.admin.resources.timetable-entries.create') }}">
        + Ajouter la première séance
    </a>
</div>
@else
<div class="ct-table-outer">
    <table class="ct-table">
        <thead>
            <tr>
                <th class="ct-th-day">{{ __('Jour') }}</th>
                @foreach($data['slots'] as $slot)
                <th class="ct-th-slot">
                    <span class="ct-slot-time">{{ $slot['start'] }}</span>
                    <span class="ct-slot-end">→ {{ $slot['end'] }}</span>
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data['grid'] as $row)
            <tr class="ct-tr">
                <td class="ct-td-day">{{ __($row['day']) }}</td>
                @foreach($row['cells'] as $cell)
                <td class="ct-td-cell">
                    @if($cell)
                    <div class="ct-session" style="border-left-color:{{ $cell->subject?->color ?? '#1d4ed8' }}">
                        <div class="ct-session-subj">{{ $cell->subject?->name ?? '—' }}</div>
                        <div class="ct-session-teacher">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                            {{ $cell->teacher?->full_name ?? 'Non assigné' }}
                        </div>
                        @if($cell->room)
                        <span class="ct-session-room">{{ $cell->room }}</span>
                        @endif
                    </div>
                    @else
                    <div class="ct-cell-free">·</div>
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
