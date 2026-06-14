<x-filament-panels::page>

    {{-- Chart.js — @assets ensures it loads before @script runs --}}
    @assets
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endassets

    @php
        $revenue   = $this->getRevenue();
        $expenses  = $this->getExpensesTotal();
        $net       = $this->getNetProfit();
        $pending   = $this->getPendingTotal();
        $rate      = $this->getCollectionRate();
        $overdue   = $this->getTotalOverdue();
        $aging     = $this->getOverdueByAging();
        $students  = $this->getStudentsWithBalance();
        $byMethod  = $this->getRevenueByPaymentMethod();
        $expByCat  = $this->getExpensesByCategory();
        $chartData = $this->getChartData();

        $catColors = ['#4f46e5','#ef4444','#f59e0b','#10b981','#06b6d4','#8b5cf6','#ec4899','#14b8a6','#f97316','#84cc16'];
        $catBgColors = ['bg-indigo-500','bg-red-500','bg-amber-500','bg-emerald-500','bg-cyan-500','bg-violet-500','bg-pink-500','bg-teal-500','bg-orange-500','bg-lime-500'];
    @endphp

    <div class="space-y-6">

        {{-- ══ PERIOD SELECTOR ══════════════════════════════════════════════ --}}
        <div class="rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-700 p-5 shadow-xl">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-white font-bold text-xl tracking-tight">{{ __('Financial Report') }}</h2>
                    <p class="text-indigo-200 text-sm mt-0.5">
                        {{ \Carbon\Carbon::parse($from)->locale('fr')->isoFormat('D MMM YYYY') }}
                        &nbsp;→&nbsp;
                        {{ \Carbon\Carbon::parse($until)->locale('fr')->isoFormat('D MMM YYYY') }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @foreach([
                        'month'   => __('This Month'),
                        'quarter' => __('This Quarter'),
                        'year'    => __('This Year'),
                    ] as $key => $label)
                        <button wire:click="setPeriod('{{ $key }}')"
                            class="px-4 py-2 rounded-lg text-sm font-semibold transition-all
                                {{ $period === $key
                                    ? 'bg-white text-indigo-700 shadow-md'
                                    : 'bg-white/15 text-white hover:bg-white/25 border border-white/20' }}">
                            {{ $label }}
                        </button>
                    @endforeach

                    <div class="flex items-center gap-1.5 bg-white/10 border border-white/20 rounded-lg px-3 py-1.5">
                        <input type="date" wire:model.live="from"
                            class="bg-transparent text-white text-sm border-none outline-none [color-scheme:dark] w-28">
                        <span class="text-indigo-300 text-xs font-bold">→</span>
                        <input type="date" wire:model.live="until"
                            class="bg-transparent text-white text-sm border-none outline-none [color-scheme:dark] w-28">
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ 6 KPI CARDS ═════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">

            {{-- Recettes --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 border-t-4 border-emerald-500 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-4 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/30">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">+</span>
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Revenue') }}</div>
                    <div class="text-lg font-bold text-gray-900 dark:text-white tabular-nums leading-tight mt-0.5">{{ number_format($revenue, 3) }}</div>
                    <div class="text-xs text-gray-400">TND</div>
                </div>
            </div>

            {{-- Dépenses --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 border-t-4 border-red-500 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-4 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="p-2 rounded-lg bg-red-50 dark:bg-red-900/30">
                        <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400">−</span>
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Expenses') }}</div>
                    <div class="text-lg font-bold text-gray-900 dark:text-white tabular-nums leading-tight mt-0.5">{{ number_format($expenses, 3) }}</div>
                    <div class="text-xs text-gray-400">TND</div>
                </div>
            </div>

            {{-- Bénéfice net --}}
            @php $netColor = $net >= 0 ? 'indigo' : 'rose'; @endphp
            <div class="rounded-xl bg-white dark:bg-gray-900 border-t-4 {{ $net >= 0 ? 'border-indigo-500' : 'border-rose-500' }} shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-4 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="p-2 rounded-lg {{ $net >= 0 ? 'bg-indigo-50 dark:bg-indigo-900/30' : 'bg-rose-50 dark:bg-rose-900/30' }}">
                        <svg class="w-5 h-5 {{ $net >= 0 ? 'text-indigo-600' : 'text-rose-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Net Profit') }}</div>
                    <div class="text-lg font-bold {{ $net >= 0 ? 'text-indigo-600' : 'text-rose-600' }} tabular-nums leading-tight mt-0.5">
                        {{ $net >= 0 ? '+' : '' }}{{ number_format($net, 3) }}
                    </div>
                    <div class="text-xs text-gray-400">TND</div>
                </div>
            </div>

            {{-- En attente --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 border-t-4 border-amber-500 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-4 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-900/30">
                        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Pending') }}</div>
                    <div class="text-lg font-bold text-amber-600 tabular-nums leading-tight mt-0.5">{{ number_format($pending, 3) }}</div>
                    <div class="text-xs text-gray-400">TND</div>
                </div>
            </div>

            {{-- Taux de recouvrement --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 border-t-4 border-violet-500 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-4 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="p-2 rounded-lg bg-violet-50 dark:bg-violet-900/30">
                        <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $rate >= 80 ? 'bg-emerald-100 text-emerald-700' : ($rate >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                        {{ $rate }}%
                    </span>
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Collection Rate') }}</div>
                    <div class="mt-1.5 w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all duration-700 {{ $rate >= 80 ? 'bg-emerald-500' : ($rate >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                             style="width: {{ $rate }}%"></div>
                    </div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('of total due collected') }}</div>
                </div>
            </div>

            {{-- Impayés en retard --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 border-t-4 border-rose-500 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-4 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="p-2 rounded-lg bg-rose-50 dark:bg-rose-900/30">
                        <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    @if($overdue > 0)
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400 animate-pulse">!</span>
                    @endif
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Overdue') }}</div>
                    <div class="text-lg font-bold text-rose-600 tabular-nums leading-tight mt-0.5">{{ number_format($overdue, 3) }}</div>
                    <div class="text-xs text-gray-400">TND</div>
                </div>
            </div>
        </div>

        {{-- ══ CHARTS ROW ═══════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Revenue vs Expenses Bar Chart --}}
            <div class="lg:col-span-2 rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('Revenue vs Expenses') }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ __('Last 6 months') }}</p>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>{{ __('Revenue') }}</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span>{{ __('Expenses') }}</span>
                    </div>
                </div>
                <div class="relative" style="height:220px">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            {{-- Expenses Doughnut --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
                <div class="mb-5">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('Expenses by Category') }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ __('Current period') }}</p>
                </div>
                @if(empty($expByCat))
                    <div class="flex flex-col items-center justify-center h-44 text-gray-400 gap-2">
                        <svg class="w-10 h-10 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="text-sm">{{ __('No expenses in this period') }}</span>
                    </div>
                @else
                    <div class="relative" style="height:180px">
                        <canvas id="expensesDoughnut"></canvas>
                    </div>
                @endif
            </div>
        </div>

        {{-- ══ PAYMENT METHODS + CATEGORY BREAKDOWN ════════════════════════ --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- Revenue by payment method --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">{{ __('Revenue by Payment Method') }}</h3>
                @if(empty($byMethod))
                    <p class="text-sm text-gray-400">{{ __('No revenue in this period') }}</p>
                @else
                    @php
                        $methodMeta = [
                            'cash'          => ['icon' => '💵', 'label' => __('Cash'),          'color' => 'bg-emerald-500'],
                            'bank_transfer' => ['icon' => '🏦', 'label' => __('Bank Transfer'), 'color' => 'bg-indigo-500'],
                            'cheque'        => ['icon' => '📋', 'label' => __('Cheque'),        'color' => 'bg-amber-500'],
                            'app'           => ['icon' => '📱', 'label' => __('App'),           'color' => 'bg-violet-500'],
                        ];
                    @endphp
                    <div class="space-y-4">
                        @foreach($byMethod as $method => $amount)
                        @php $pct = $revenue > 0 ? round($amount / $revenue * 100) : 0; @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-9 text-xl text-center flex-shrink-0">{{ $methodMeta[$method]['icon'] ?? '💰' }}</div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between text-xs mb-1.5">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $methodMeta[$method]['label'] ?? $method }}</span>
                                    <span class="text-gray-500 tabular-nums">{{ number_format($amount, 3) }} TND &nbsp;<span class="text-gray-400">({{ $pct }}%)</span></span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                                    <div class="{{ $methodMeta[$method]['color'] ?? 'bg-gray-400' }} h-2 rounded-full transition-all duration-700" style="width:{{ $pct }}%"></div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Expenses breakdown list --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">{{ __('Expenses by Category') }}</h3>
                @if(empty($expByCat))
                    <p class="text-sm text-gray-400">{{ __('No expenses in this period') }}</p>
                @else
                    <div class="space-y-3">
                        @foreach(array_slice($expByCat, 0, 8, true) as $category => $amount)
                        @php
                            $ci  = $loop->index;
                            $pct = $expenses > 0 ? round($amount / $expenses * 100) : 0;
                        @endphp
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0 {{ $catBgColors[$ci % count($catBgColors)] }}"></span>
                                    <span class="text-gray-700 dark:text-gray-300 truncate">{{ $category }}</span>
                                </div>
                                <span class="ml-2 font-semibold text-gray-900 dark:text-white tabular-nums flex-shrink-0">
                                    {{ number_format($amount, 3) }} <span class="text-gray-400 font-normal">({{ $pct }}%)</span>
                                </span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5">
                                <div class="{{ $catBgColors[$ci % count($catBgColors)] }} h-1.5 rounded-full transition-all duration-700" style="width:{{ $pct }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ══ AGING ANALYSIS ═══════════════════════════════════════════════ --}}
        @if(array_sum($aging) > 0)
        <div class="rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
            <div class="flex items-center gap-2 mb-5">
                <svg class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('Overdue Analysis') }}</h3>
                <span class="text-xs text-rose-500 bg-rose-50 dark:bg-rose-900/20 px-2 py-0.5 rounded-full font-medium">
                    {{ __('Total') }}: {{ number_format(array_sum($aging), 3) }} TND
                </span>
            </div>
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach([
                    ['label'=>__('1–30 days'),  'key'=>'1_30',  'border'=>'border-amber-400', 'bg'=>'bg-amber-50 dark:bg-amber-900/15',   'text'=>'text-amber-700 dark:text-amber-400',  'badge'=>'bg-amber-500'],
                    ['label'=>__('31–60 days'), 'key'=>'31_60', 'border'=>'border-orange-400','bg'=>'bg-orange-50 dark:bg-orange-900/15',  'text'=>'text-orange-700 dark:text-orange-400','badge'=>'bg-orange-500'],
                    ['label'=>__('61–90 days'), 'key'=>'61_90', 'border'=>'border-red-400',   'bg'=>'bg-red-50 dark:bg-red-900/15',         'text'=>'text-red-700 dark:text-red-400',     'badge'=>'bg-red-500'],
                    ['label'=>__('90+ days'),   'key'=>'90p',   'border'=>'border-rose-600',  'bg'=>'bg-rose-50 dark:bg-rose-900/15',        'text'=>'text-rose-700 dark:text-rose-400',   'badge'=>'bg-rose-600'],
                ] as $bucket)
                <div class="rounded-xl border {{ $bucket['border'] }} {{ $bucket['bg'] }} p-4">
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">{{ $bucket['label'] }}</div>
                    <div class="text-xl font-bold {{ $bucket['text'] }} tabular-nums">
                        {{ number_format($aging[$bucket['key']], 3) }}
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5">TND</div>
                    @if($aging[$bucket['key']] > 0)
                    <div class="mt-2 inline-flex items-center justify-center w-5 h-5 rounded-full {{ $bucket['badge'] }} text-white text-[10px] font-bold">!</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ══ STUDENTS WITH BALANCE ════════════════════════════════════════ --}}
        @if(!empty($students))
        <div class="rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('Students with Outstanding Balance') }}</h3>
                <span class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full">
                    {{ count($students) }} {{ __('students') }}
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider pb-3 pr-4">#</th>
                            <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider pb-3 pr-4">{{ __('Student') }}</th>
                            <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider pb-3 pr-4">{{ __('Balance') }}</th>
                            <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider pb-3">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                        @foreach($students as $idx => $student)
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition-colors">
                            <td class="py-2.5 pr-4 text-gray-400 text-xs">{{ $idx + 1 }}</td>
                            <td class="py-2.5 pr-4 font-medium text-gray-900 dark:text-white">{{ $student['name'] }}</td>
                            <td class="py-2.5 pr-4 text-right font-bold tabular-nums {{ $student['is_overdue'] ? 'text-rose-600' : 'text-amber-600' }}">
                                {{ number_format($student['balance'], 3) }} TND
                            </td>
                            <td class="py-2.5 text-right">
                                @if($student['is_overdue'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400">
                                        {{ __('Overdue') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                        {{ __('Pending') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

    {{-- ══ CHART.JS INITIALIZATION ══════════════════════════════════════════ --}}
    @script
    <script>
        // Revenue vs Expenses (last 6 months)
        Chart.getChart('revenueChart')?.destroy();
        const rvEl = document.getElementById('revenueChart');
        if (rvEl) {
            new Chart(rvEl, {
                type: 'bar',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [
                        {
                            label: '{{ __("Revenue") }}',
                            data: @json($chartData['revenue']),
                            backgroundColor: 'rgba(16,185,129,0.75)',
                            borderColor: 'rgb(16,185,129)',
                            borderWidth: 2,
                            borderRadius: 6,
                            borderSkipped: false,
                        },
                        {
                            label: '{{ __("Expenses") }}',
                            data: @json($chartData['expenses']),
                            backgroundColor: 'rgba(239,68,68,0.75)',
                            borderColor: 'rgb(239,68,68)',
                            borderWidth: 2,
                            borderRadius: 6,
                            borderSkipped: false,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(17,24,39,0.92)',
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 11 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toFixed(3)} TND`
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 }, color: '#9ca3af' }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(156,163,175,0.1)' },
                            ticks: {
                                font: { size: 11 }, color: '#9ca3af',
                                callback: val => val >= 1000 ? (val/1000).toFixed(1)+'k' : val.toFixed(0)
                            }
                        }
                    }
                }
            });
        }

        // Expenses by Category — Doughnut
        Chart.getChart('expensesDoughnut')?.destroy();
        const donutEl = document.getElementById('expensesDoughnut');
        if (donutEl) {
            const expData = @json($expByCat);
            if (Object.keys(expData).length > 0) {
                new Chart(donutEl, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(expData),
                        datasets: [{
                            data: Object.values(expData),
                            backgroundColor: ['#4f46e5','#ef4444','#f59e0b','#10b981','#06b6d4','#8b5cf6','#ec4899','#14b8a6','#f97316','#84cc16'],
                            borderWidth: 2,
                            borderColor: document.documentElement.classList.contains('dark') ? '#111827' : '#ffffff',
                            hoverOffset: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, boxWidth: 8, font: { size: 10 }, padding: 6, color: '#6b7280' }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(17,24,39,0.92)',
                                callbacks: {
                                    label: ctx => ` ${ctx.label}: ${ctx.parsed.toFixed(3)} TND`
                                }
                            }
                        }
                    }
                });
            }
        }
    </script>
    @endscript

</x-filament-panels::page>
