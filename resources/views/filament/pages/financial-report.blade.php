<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Period selector --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Période :</span>

                @foreach(['month' => 'Ce mois', 'quarter' => 'Ce trimestre', 'year' => 'Cette année'] as $key => $label)
                <button wire:click="setPeriod('{{ $key }}')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition
                        {{ $period === $key
                            ? 'bg-primary-600 text-white'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                    {{ $label }}
                </button>
                @endforeach

                <div class="flex items-center gap-2 ml-4">
                    <input type="date" wire:model.live="from" class="text-sm rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white px-3 py-2">
                    <span class="text-gray-400">→</span>
                    <input type="date" wire:model.live="until" class="text-sm rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white px-3 py-2">
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">
                Du {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($until)->format('d/m/Y') }}
            </p>
        </div>

        {{-- Key figures --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $revenue   = $this->getRevenue();
                $expenses  = $this->getExpensesTotal();
                $net       = $this->getNetProfit();
                $pending   = $this->getPendingTotal();
            @endphp

            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-5">
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Recettes</div>
                <div class="text-2xl font-bold text-green-600">{{ number_format($revenue, 3) }}</div>
                <div class="text-xs text-gray-400 mt-1">TND</div>
            </div>

            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-5">
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Dépenses</div>
                <div class="text-2xl font-bold text-red-600">{{ number_format($expenses, 3) }}</div>
                <div class="text-xs text-gray-400 mt-1">TND</div>
            </div>

            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-5">
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Bénéfice net</div>
                <div class="text-2xl font-bold {{ $net >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $net >= 0 ? '+' : '' }}{{ number_format($net, 3) }}
                </div>
                <div class="text-xs text-gray-400 mt-1">TND</div>
            </div>

            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-5">
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">En attente</div>
                <div class="text-2xl font-bold text-amber-600">{{ number_format($pending, 3) }}</div>
                <div class="text-xs text-gray-400 mt-1">TND non encaissé</div>
            </div>
        </div>

        {{-- Expenses by category --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Dépenses par catégorie</h3>
            @php $expensesByCat = $this->getExpensesByCategory(); @endphp
            @if(empty($expensesByCat))
                <p class="text-sm text-gray-400">Aucune dépense sur cette période.</p>
            @else
                <div class="space-y-3">
                    @foreach($expensesByCat as $category => $amount)
                    @php $pct = $expenses > 0 ? round($amount / $expenses * 100) : 0; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-700 dark:text-gray-300">{{ $category }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ number_format($amount, 3) }} TND ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-red-500 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Revenue by payment method --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Recettes par mode de paiement</h3>
            @php
                $byMethod = $this->getRevenueByPaymentMethod();
                $methodLabels = ['cash' => 'Espèces', 'bank_transfer' => 'Virement', 'cheque' => 'Chèque', 'app' => 'Application'];
            @endphp
            @if(empty($byMethod))
                <p class="text-sm text-gray-400">Aucune recette sur cette période.</p>
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach($byMethod as $method => $amount)
                    <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="text-xs text-gray-500 mb-1">{{ $methodLabels[$method] ?? $method }}</div>
                        <div class="font-bold text-green-600">{{ number_format($amount, 3) }}</div>
                        <div class="text-xs text-gray-400">TND</div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Revenue by month --}}
        @php $byMonth = $this->getRevenueByMonth(); @endphp
        @if(!empty($byMonth))
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Évolution des recettes</h3>
            @php $maxRev = max($byMonth) ?: 1; @endphp
            <div class="flex items-end gap-2 h-32">
                @foreach($byMonth as $month => $amount)
                @php $h = round($amount / $maxRev * 100); @endphp
                <div class="flex-1 flex flex-col items-center gap-1">
                    <div class="text-xs text-gray-500">{{ number_format($amount / 1000, 1) }}k</div>
                    <div class="w-full bg-primary-500 rounded-t" style="height: {{ $h }}%"></div>
                    <div class="text-xs text-gray-400">{{ substr($month, 5) }}/{{ substr($month, 0, 4) }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</x-filament-panels::page>
