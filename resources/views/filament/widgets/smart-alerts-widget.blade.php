<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span>{{ __('Smart Alerts') }}</span>
                @php $alertCount = ($overdueCount > 0 ? 1 : 0) + ($unnotifiedCount > 0 ? 1 : 0) + ($classesNoTeacher > 0 ? 1 : 0); @endphp
                @if($alertCount > 0)
                    <span class="inline-flex items-center justify-center w-5 h-5 text-[11px] font-bold text-white bg-rose-500 rounded-full">{{ $alertCount }}</span>
                @endif
            </div>
        </x-slot>

        @if($overdueCount === 0 && $unnotifiedCount === 0 && $classesNoTeacher === 0)
            <div class="flex items-center gap-3 py-4 text-emerald-600 dark:text-emerald-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-semibold">{{ __('No alerts — everything is up to date!') }}</span>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

                {{-- Overdue Payments --}}
                @if($overdueCount > 0)
                <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 dark:bg-rose-900/15 p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-bold text-rose-700 dark:text-rose-400">{{ __('Overdue Payments') }}</span>
                        </div>
                        <span class="text-xs font-bold text-white bg-rose-500 px-2 py-0.5 rounded-full">{{ $overdueCount }}</span>
                    </div>
                    <div class="text-xl font-bold text-rose-700 dark:text-rose-300 tabular-nums mb-3">
                        {{ number_format($overdueTotal, 3) }} TND
                    </div>
                    <div class="space-y-1.5">
                        @foreach($overduePayments as $payment)
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-rose-700 dark:text-rose-400 truncate">{{ $payment->student?->full_name ?? '—' }}</span>
                            <span class="text-rose-600 font-semibold tabular-nums ml-2 flex-shrink-0">{{ number_format($payment->amount, 3) }} TND</span>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ \App\Filament\Resources\PaymentResource::getUrl('index') }}"
                       class="mt-3 inline-flex items-center gap-1 text-xs text-rose-600 dark:text-rose-400 hover:text-rose-800 font-semibold">
                        {{ __('View all') }}
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                @endif

                {{-- Unnotified Incidents --}}
                @if($unnotifiedCount > 0)
                <div class="rounded-xl border-l-4 border-amber-500 bg-amber-50 dark:bg-amber-900/15 p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span class="text-sm font-bold text-amber-700 dark:text-amber-400">{{ __('Incidents Not Notified') }}</span>
                        </div>
                        <span class="text-xs font-bold text-white bg-amber-500 px-2 py-0.5 rounded-full">{{ $unnotifiedCount }}</span>
                    </div>
                    <div class="space-y-2">
                        @foreach($unnotifiedIncidents as $incident)
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-amber-700 dark:text-amber-400 truncate">{{ $incident->student?->full_name ?? '—' }}</span>
                            <span class="ml-2 px-1.5 py-0.5 rounded text-white text-[10px] font-bold flex-shrink-0
                                {{ $incident->severity === 'high' ? 'bg-red-500' : ($incident->severity === 'medium' ? 'bg-amber-500' : 'bg-gray-400') }}">
                                {{ __($incident->severity === 'high' ? 'High' : ($incident->severity === 'medium' ? 'Medium' : 'Low')) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ \App\Filament\Resources\IncidentResource::getUrl('index') }}"
                       class="mt-3 inline-flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400 hover:text-amber-800 font-semibold">
                        {{ __('View all') }}
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                @endif

                {{-- Classes Without Teacher --}}
                @if($classesNoTeacher > 0)
                <div class="rounded-xl border-l-4 border-sky-500 bg-sky-50 dark:bg-sky-900/15 p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-sky-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="text-sm font-bold text-sky-700 dark:text-sky-400">{{ __('Classes Without Teacher') }}</span>
                        </div>
                        <span class="text-xs font-bold text-white bg-sky-500 px-2 py-0.5 rounded-full">{{ $classesNoTeacher }}</span>
                    </div>
                    <div class="text-sm text-sky-700 dark:text-sky-300 font-medium mt-1">
                        {{ $classesNoTeacher }} {{ $classesNoTeacher > 1 ? __('classes have no teacher assigned') : __('class has no teacher assigned') }}
                    </div>
                    <a href="{{ \App\Filament\Resources\ClassroomResource::getUrl('index') }}"
                       class="mt-3 inline-flex items-center gap-1 text-xs text-sky-600 dark:text-sky-400 hover:text-sky-800 font-semibold">
                        {{ __('View classrooms') }}
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                @endif

            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
