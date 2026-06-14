@php
    $alertCount = ($overdueCount > 0 ? 1 : 0) + ($unnotifiedCount > 0 ? 1 : 0) + ($classesNoTeacher > 0 ? 1 : 0);
@endphp

{{-- icon prop renders the Filament heroicon correctly (no raw SVG in heading slot) --}}
<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-bell">
        <x-slot name="heading">
            <span>{{ __('Smart Alerts') }}</span>
            @if($alertCount > 0)
                <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;font-size:10px;font-weight:700;color:white;background:#ef4444;border-radius:50%;margin-left:6px;vertical-align:middle;">{{ $alertCount }}</span>
            @endif
        </x-slot>

        @if($overdueCount === 0 && $unnotifiedCount === 0 && $classesNoTeacher === 0)
            <div style="display:flex;align-items:center;gap:8px;color:#059669;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <span style="font-size:14px;font-weight:500;">{{ __('No alerts — everything is up to date!') }}</span>
            </div>

        @else
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">

                {{-- Overdue Payments --}}
                @if($overdueCount > 0)
                <div style="border-radius:12px;border-left:4px solid #ef4444;background:#fff1f2;padding:16px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                            </svg>
                            <span style="font-size:13px;font-weight:700;color:#b91c1c;">{{ __('Overdue Payments') }}</span>
                        </div>
                        <span style="font-size:11px;font-weight:700;color:white;background:#ef4444;padding:2px 8px;border-radius:20px;">{{ $overdueCount }}</span>
                    </div>
                    <div style="font-size:20px;font-weight:800;color:#b91c1c;letter-spacing:-0.5px;margin-bottom:12px;font-variant-numeric:tabular-nums;">
                        {{ number_format($overdueTotal, 3) }} TND
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        @foreach($overduePayments as $payment)
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:12px;">
                            <span style="color:#b91c1c;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:55%;">{{ $payment->student?->full_name ?? '—' }}</span>
                            <span style="color:#dc2626;font-weight:600;white-space:nowrap;font-variant-numeric:tabular-nums;">{{ number_format($payment->amount, 3) }}</span>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ \App\Filament\Resources\PaymentResource::getUrl('index') }}"
                       style="display:inline-flex;align-items:center;gap:4px;margin-top:12px;font-size:12px;color:#dc2626;font-weight:700;text-decoration:none;">
                        {{ __('View all') }}
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    </a>
                </div>
                @endif

                {{-- Unnotified Incidents --}}
                @if($unnotifiedCount > 0)
                <div style="border-radius:12px;border-left:4px solid #f59e0b;background:#fffbeb;padding:16px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                            <span style="font-size:13px;font-weight:700;color:#92400e;">{{ __('Incidents Not Notified') }}</span>
                        </div>
                        <span style="font-size:11px;font-weight:700;color:white;background:#f59e0b;padding:2px 8px;border-radius:20px;">{{ $unnotifiedCount }}</span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @foreach($unnotifiedIncidents as $incident)
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:12px;">
                            <span style="color:#92400e;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:60%;">{{ $incident->student?->full_name ?? '—' }}</span>
                            <span style="padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700;color:white;
                                background:{{ $incident->severity === 'high' ? '#ef4444' : ($incident->severity === 'medium' ? '#f59e0b' : '#6b7280') }};">
                                {{ $incident->severity === 'high' ? __('High') : ($incident->severity === 'medium' ? __('Medium') : __('Low')) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ \App\Filament\Resources\IncidentResource::getUrl('index') }}"
                       style="display:inline-flex;align-items:center;gap:4px;margin-top:12px;font-size:12px;color:#d97706;font-weight:700;text-decoration:none;">
                        {{ __('View all') }}
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    </a>
                </div>
                @endif

                {{-- Classes Without Teacher --}}
                @if($classesNoTeacher > 0)
                <div style="border-radius:12px;border-left:4px solid #0ea5e9;background:#f0f9ff;padding:16px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                            <span style="font-size:13px;font-weight:700;color:#0369a1;">{{ __('Classes Without Teacher') }}</span>
                        </div>
                        <span style="font-size:11px;font-weight:700;color:white;background:#0ea5e9;padding:2px 8px;border-radius:20px;">{{ $classesNoTeacher }}</span>
                    </div>
                    <div style="font-size:14px;color:#0369a1;font-weight:500;margin-bottom:12px;">
                        {{ $classesNoTeacher }} {{ $classesNoTeacher > 1 ? __('classes have no teacher assigned') : __('class has no teacher assigned') }}
                    </div>
                    <a href="{{ \App\Filament\Resources\ClassroomResource::getUrl('index') }}"
                       style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#0284c7;font-weight:700;text-decoration:none;">
                        {{ __('View classrooms') }}
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    </a>
                </div>
                @endif

            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
