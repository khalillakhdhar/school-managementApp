<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Welcome --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                Bonjour, {{ $schoolParent->full_name }} 👋
            </h2>
            <p class="text-sm text-gray-500 mt-1">Voici le suivi scolaire de vos enfants.</p>
        </div>

        @foreach($this->getStudentsWithDetails() as $data)
        @php $student = $data['student']; @endphp

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            {{-- Student header --}}
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $student->full_name }}</h3>
                    <p class="text-sm text-gray-500">
                        {{ $student->class }} — {{ $student->level }}
                        @if($student->classroom)
                            &nbsp;| Classe {{ $student->classroom->name }}
                        @endif
                    </p>
                </div>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium
                    {{ $student->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $student->status === 'active' ? 'Actif' : ucfirst($student->status) }}
                </span>
            </div>

            {{-- Financial summary --}}
            <div class="grid grid-cols-3 divide-x divide-gray-200 dark:divide-gray-700">
                <div class="p-4 text-center">
                    <p class="text-xs text-gray-500 mb-1">Total dû</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($data['totalDue'], 3) }} TND</p>
                </div>
                <div class="p-4 text-center">
                    <p class="text-xs text-gray-500 mb-1">Total payé</p>
                    <p class="text-lg font-bold text-green-600">{{ number_format($data['totalPaid'], 3) }} TND</p>
                </div>
                <div class="p-4 text-center">
                    <p class="text-xs text-gray-500 mb-1">Restant</p>
                    <p class="text-lg font-bold {{ $data['outstanding'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($data['outstanding'], 3) }} TND
                    </p>
                </div>
            </div>

            {{-- Last payments --}}
            @if(count($data['payments']) > 0)
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Derniers paiements</h4>
                <div class="space-y-2">
                    @foreach($data['payments'] as $payment)
                    <div class="flex items-center justify-between text-sm py-2 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-600 dark:text-gray-400">{{ $payment->payment_date?->format('d/m/Y') }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($payment->amount, 3) }} TND</span>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                            {{ $payment->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $payment->status === 'paid' ? 'Payé' : 'En attente' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Incidents --}}
            @if(count($data['incidents']) > 0)
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-red-50 dark:bg-red-950/20">
                <h4 class="text-sm font-medium text-red-700 dark:text-red-400 mb-3">⚠️ Incidents récents</h4>
                <div class="space-y-2">
                    @foreach($data['incidents'] as $incident)
                    <div class="text-sm p-3 bg-white dark:bg-gray-800 rounded-lg border border-red-200 dark:border-red-800">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-900 dark:text-white">{{ $incident->title }}</span>
                            <span class="text-xs text-gray-500">{{ $incident->incident_date?->format('d/m/Y') }}</span>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mt-1 text-xs">{{ $incident->description }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endforeach

        @if(empty($this->getStudentsWithDetails()))
        <div class="text-center py-12 text-gray-500">
            Aucun élève associé à votre compte.
        </div>
        @endif

    </div>
</x-filament-panels::page>
