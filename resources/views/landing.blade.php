<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- ── SEO ─────────────────────────────────────────────────────────── --}}
    <title>{{ $appName }} — Logiciel de gestion scolaire tout-en-un</title>
    <meta name="description" content="{{ $appName }} est la plateforme tout-en-un pour gérer votre établissement : élèves, personnel, paie, présences, notes & bulletins, paiements, emplois du temps, et portails parents/enseignants.">
    <meta name="keywords" content="gestion scolaire, ERP école, logiciel école Tunisie, gestion élèves, bulletins, paie, présences, portail parents">
    <meta name="author" content="{{ $appName }}">
    <link rel="canonical" href="{{ url('/') }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $appName }} — Logiciel de gestion scolaire tout-en-un">
    <meta property="og:description" content="La plateforme moderne pour piloter votre établissement scolaire de A à Z.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta name="theme-color" content="#0f172a">
    <link rel="icon" href="{{ asset('favicon.svg') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
                    colors: {
                        brand: { 50:'#eff6ff',100:'#dbeafe',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a' },
                        ink: '#0f172a',
                    },
                },
            },
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body{font-family:'Inter',sans-serif}
        .grad-text{background:linear-gradient(135deg,#2563eb,#60a5fa);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
        .grad-hero{background:radial-gradient(1200px 600px at 50% -10%,rgba(37,99,235,.18),transparent),linear-gradient(180deg,#0f172a,#0b1222)}
        .card-hover{transition:transform .2s,box-shadow .2s,border-color .2s}
        .card-hover:hover{transform:translateY(-4px);box-shadow:0 16px 40px rgba(2,6,23,.10);border-color:#bfdbfe}
        details>summary{list-style:none;cursor:pointer}
        details>summary::-webkit-details-marker{display:none}
        details[open] .faq-chevron{transform:rotate(180deg)}
    </style>
</head>
<body class="bg-white text-slate-700 antialiased">

{{-- ══════════════════════════════ NAV ══════════════════════════════ --}}
<header x-data="{ open:false, scrolled:false }" @scroll.window="scrolled = window.scrollY > 20"
        class="fixed inset-x-0 top-0 z-50 transition-all"
        :class="scrolled ? 'bg-white/90 backdrop-blur border-b border-slate-200 shadow-sm' : 'bg-transparent'">
    <nav class="mx-auto max-w-7xl px-5 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <a href="#" class="flex items-center gap-2.5">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-600 text-white font-extrabold text-sm">EC</span>
                <span class="text-lg font-extrabold tracking-tight" :class="scrolled ? 'text-ink' : 'text-white'">{{ $appName }}</span>
            </a>
            <div class="hidden lg:flex items-center gap-8 text-sm font-semibold"
                 :class="scrolled ? 'text-slate-600' : 'text-slate-200'">
                <a href="#accueil" class="hover:text-brand-600">Accueil</a>
                <a href="#fonctionnalites" class="hover:text-brand-600">Fonctionnalités</a>
                <a href="#avantages" class="hover:text-brand-600">Avantages</a>
                <a href="#tarifs" class="hover:text-brand-600">Tarifs</a>
                <a href="#faq" class="hover:text-brand-600">FAQ</a>
                <a href="#contact" class="hover:text-brand-600">Contact</a>
            </div>
            <div class="hidden lg:flex items-center gap-3">
                <a href="/admin/login" class="text-sm font-semibold px-4 py-2 rounded-lg transition"
                   :class="scrolled ? 'text-slate-700 hover:bg-slate-100' : 'text-white hover:bg-white/10'">Connexion</a>
                <a href="#contact" class="text-sm font-semibold px-4 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700 shadow-sm shadow-brand-600/30">Demander une démo</a>
            </div>
            <button @click="open=!open" class="lg:hidden p-2 rounded-lg" :class="scrolled ? 'text-ink' : 'text-white'">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </nav>
    {{-- mobile menu --}}
    <div x-show="open" x-cloak @click.away="open=false" x-transition class="lg:hidden bg-white border-b border-slate-200 shadow-lg">
        <div class="px-5 py-4 flex flex-col gap-1 text-sm font-semibold text-slate-700">
            <a href="#fonctionnalites" @click="open=false" class="py-2.5 hover:text-brand-600">Fonctionnalités</a>
            <a href="#avantages" @click="open=false" class="py-2.5 hover:text-brand-600">Avantages</a>
            <a href="#tarifs" @click="open=false" class="py-2.5 hover:text-brand-600">Tarifs</a>
            <a href="#faq" @click="open=false" class="py-2.5 hover:text-brand-600">FAQ</a>
            <a href="#contact" @click="open=false" class="py-2.5 hover:text-brand-600">Contact</a>
            <div class="flex gap-3 pt-3">
                <a href="/admin/login" class="flex-1 text-center py-2.5 rounded-lg border border-slate-200">Connexion</a>
                <a href="#contact" class="flex-1 text-center py-2.5 rounded-lg bg-brand-600 text-white">Démo</a>
            </div>
        </div>
    </div>
</header>

{{-- ══════════════════════════════ HERO ══════════════════════════════ --}}
<section id="accueil" class="grad-hero relative overflow-hidden pt-32 pb-24 lg:pt-40 lg:pb-32">
    <div class="mx-auto max-w-7xl px-5 lg:px-8 grid lg:grid-cols-2 gap-14 items-center">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/15 px-3.5 py-1.5 text-xs font-semibold text-brand-100">
                ✨ Plateforme de gestion scolaire nouvelle génération
            </span>
            <h1 class="mt-6 text-4xl lg:text-6xl font-extrabold tracking-tight text-white leading-[1.05]">
                Pilotez toute votre école <span class="grad-text">depuis un seul endroit</span>
            </h1>
            <p class="mt-6 text-lg text-slate-300 leading-relaxed max-w-xl">
                {{ $appName }} réunit élèves, personnel, finances, présences, notes et communication
                dans une plateforme unique, moderne et sécurisée. Moins de paperasse, plus de temps
                pour l'essentiel : l'éducation.
            </p>
            <div class="mt-9 flex flex-wrap gap-4">
                <a href="#contact" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-3.5 text-sm font-bold text-white hover:bg-brand-700 shadow-lg shadow-brand-600/40 transition">
                    Demander une démonstration
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M9 18l6-6-6-6"/></svg>
                </a>
                <a href="/admin/login" class="inline-flex items-center gap-2 rounded-xl bg-white/10 border border-white/20 px-6 py-3.5 text-sm font-bold text-white hover:bg-white/15 transition">
                    Se connecter
                </a>
            </div>
            <div class="mt-10 flex items-center gap-8 text-slate-400 text-sm">
                <div><span class="block text-2xl font-extrabold text-white">3</span> espaces dédiés</div>
                <div><span class="block text-2xl font-extrabold text-white">10+</span> modules intégrés</div>
                <div><span class="block text-2xl font-extrabold text-white">100%</span> responsive</div>
            </div>
        </div>
        {{-- Visual mockup --}}
        <div class="relative">
            <div class="absolute -inset-4 bg-brand-500/20 blur-3xl rounded-full"></div>
            <div class="relative rounded-2xl border border-white/10 bg-slate-900/60 backdrop-blur p-3 shadow-2xl">
                <div class="rounded-xl overflow-hidden bg-slate-50">
                    <div class="flex items-center gap-1.5 px-4 py-3 bg-white border-b border-slate-100">
                        <span class="h-3 w-3 rounded-full bg-rose-400"></span>
                        <span class="h-3 w-3 rounded-full bg-amber-400"></span>
                        <span class="h-3 w-3 rounded-full bg-emerald-400"></span>
                        <span class="ml-3 text-[11px] text-slate-400 font-medium">{{ $appName }} — Tableau de bord</span>
                    </div>
                    <div class="p-4 grid grid-cols-3 gap-3">
                        @foreach([['Élèves','1 248','#2563eb'],['Présence','96%','#10b981'],['Recettes','82k','#8b5cf6']] as $kpi)
                        <div class="rounded-lg bg-white border border-slate-100 p-3">
                            <div class="text-[10px] font-semibold text-slate-400 uppercase">{{ $kpi[0] }}</div>
                            <div class="text-xl font-extrabold text-slate-800 mt-1">{{ $kpi[1] }}</div>
                            <div class="mt-2 h-1.5 rounded-full" style="background:{{ $kpi[2] }}22"><div class="h-full w-3/4 rounded-full" style="background:{{ $kpi[2] }}"></div></div>
                        </div>
                        @endforeach
                        <div class="col-span-3 rounded-lg bg-white border border-slate-100 p-4">
                            <div class="text-xs font-bold text-slate-700 mb-3">Évolution des inscriptions</div>
                            <div class="flex items-end gap-2 h-24">
                                @foreach([40,55,48,70,65,85,78,95] as $h)
                                <div class="flex-1 rounded-t bg-brand-500/80" style="height:{{ $h }}%"></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════ PRÉSENTATION / PROBLÈME ══════════════════════ --}}
<section class="py-20 lg:py-28 bg-white">
    <div class="mx-auto max-w-7xl px-5 lg:px-8">
        <div class="max-w-3xl">
            <span class="text-sm font-bold uppercase tracking-wider text-brand-600">Pourquoi {{ $appName }}</span>
            <h2 class="mt-3 text-3xl lg:text-4xl font-extrabold text-ink tracking-tight">Fini les tableurs, les cahiers et les outils éparpillés</h2>
            <p class="mt-5 text-lg text-slate-600 leading-relaxed">
                Les établissements jonglent entre fichiers Excel, registres papier et logiciels qui ne se parlent pas.
                {{ $appName }} centralise toute la gestion administrative, pédagogique et financière au même endroit —
                accessible à l'administration, aux enseignants et aux parents.
            </p>
        </div>
        <div class="mt-14 grid md:grid-cols-3 gap-6">
            @foreach([
                ['🎯','Le problème résolu','Données dispersées, ressaisies, erreurs et perte de temps. Tout est désormais unifié et fiable.'],
                ['🚀','Vos bénéfices','Gain de temps, suivi en temps réel, communication fluide avec les familles, décisions basées sur des chiffres.'],
                ['📈','Productivité','Automatisation des paiements, des présences, des bulletins et de la paie. Vos équipes se concentrent sur l\'essentiel.'],
            ] as $b)
            <div class="rounded-2xl border border-slate-200 p-7 card-hover bg-white">
                <div class="text-3xl">{{ $b[0] }}</div>
                <h3 class="mt-4 text-lg font-extrabold text-ink">{{ $b[1] }}</h3>
                <p class="mt-2 text-slate-600 leading-relaxed">{{ $b[2] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════════════ FONCTIONNALITÉS ══════════════════════════ --}}
<section id="fonctionnalites" class="py-20 lg:py-28 bg-slate-50">
    <div class="mx-auto max-w-7xl px-5 lg:px-8">
        <div class="text-center max-w-2xl mx-auto">
            <span class="text-sm font-bold uppercase tracking-wider text-brand-600">Fonctionnalités</span>
            <h2 class="mt-3 text-3xl lg:text-4xl font-extrabold text-ink tracking-tight">Tout ce dont votre établissement a besoin</h2>
            <p class="mt-4 text-lg text-slate-600">Des modules intégrés qui couvrent l'ensemble du cycle de gestion scolaire.</p>
        </div>
        <div class="mt-14 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach([
                ['🎓','Gestion des élèves','Inscriptions, dossiers, classes, statuts et historique complet de chaque élève.'],
                ['👨‍🏫','Personnel & RH','Employés, enseignants, contrats, et fiches de paie conformes (CNSS, IRPP).'],
                ['🗓️','Emplois du temps','Construction et consultation des emplois du temps par classe et par enseignant.'],
                ['✅','Présences','Appel des élèves et pointage du personnel, avec taux et alertes automatiques.'],
                ['📝','Notes & bulletins','Saisie des notes, moyennes pondérées, rang et bulletins trimestriels imprimables.'],
                ['💳','Paiements & finances','Scolarité, services, rappels d\'impayés et rapports financiers détaillés.'],
                ['👨‍👩‍👧','Portail parents','Les familles suivent paiements, présences, notes et annonces en temps réel.'],
                ['📊','Tableau de bord & rapports','KPIs, graphiques et statistiques pour piloter l\'établissement d\'un coup d\'œil.'],
                ['📣','Communication','Annonces, incidents et notifications vers les parents et le personnel.'],
            ] as $f)
            <div class="rounded-2xl bg-white border border-slate-200 p-7 card-hover">
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-brand-50 text-2xl">{{ $f[0] }}</div>
                <h3 class="mt-5 text-lg font-extrabold text-ink">{{ $f[1] }}</h3>
                <p class="mt-2 text-slate-600 leading-relaxed text-[15px]">{{ $f[2] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════════════════ AVANTAGES ══════════════════════════════ --}}
<section id="avantages" class="py-20 lg:py-28 bg-white">
    <div class="mx-auto max-w-7xl px-5 lg:px-8 grid lg:grid-cols-2 gap-14 items-center">
        <div>
            <span class="text-sm font-bold uppercase tracking-wider text-brand-600">Avantages</span>
            <h2 class="mt-3 text-3xl lg:text-4xl font-extrabold text-ink tracking-tight">Conçu pour être simple, sûr et efficace</h2>
            <p class="mt-5 text-lg text-slate-600 leading-relaxed">Une expérience pensée pour les équipes pédagogiques et administratives, sans formation technique.</p>
            <div class="mt-8 grid sm:grid-cols-2 gap-5">
                @foreach([
                    ['Interface moderne','Une UI claire et agréable, inspirée des meilleurs logiciels SaaS.'],
                    ['Sécurité des données','Accès cloisonnés par rôle : chacun ne voit que ce qui le concerne.'],
                    ['Automatisation','Paies, présences, rappels et bulletins générés automatiquement.'],
                    ['Multi-utilisateurs','Admin, enseignants et parents, chacun avec son espace dédié.'],
                    ['Accessibilité','Disponible partout, sur ordinateur, tablette et mobile.'],
                    ['100% Responsive','Un affichage impeccable sur tous les écrans.'],
                ] as $a)
                <div class="flex gap-3">
                    <span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-600">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M2 7l3.5 3.5L11 3"/></svg>
                    </span>
                    <div>
                        <h4 class="font-bold text-ink text-[15px]">{{ $a[0] }}</h4>
                        <p class="text-sm text-slate-500 mt-0.5">{{ $a[1] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 p-10 text-white shadow-2xl shadow-brand-600/30">
            <div class="text-5xl font-extrabold">+70%</div>
            <p class="mt-2 text-brand-100">de temps administratif économisé grâce à l'automatisation.</p>
            <div class="mt-8 space-y-5">
                @foreach([['Moins d\'erreurs de saisie','92%'],['Paiements suivis en temps réel','100%'],['Satisfaction des familles','4.8/5']] as $stat)
                <div>
                    <div class="flex justify-between text-sm font-semibold"><span>{{ $stat[0] }}</span><span>{{ $stat[1] }}</span></div>
                    <div class="mt-1.5 h-2 rounded-full bg-white/20"><div class="h-full rounded-full bg-white w-5/6"></div></div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════ CAPTURES ══════════════════════════════ --}}
<section class="py-20 lg:py-28 bg-slate-900">
    <div class="mx-auto max-w-7xl px-5 lg:px-8">
        <div class="text-center max-w-2xl mx-auto">
            <span class="text-sm font-bold uppercase tracking-wider text-brand-400">Aperçu</span>
            <h2 class="mt-3 text-3xl lg:text-4xl font-extrabold text-white tracking-tight">Découvrez la plateforme en images</h2>
            <p class="mt-4 text-lg text-slate-400">Un aperçu des espaces Administration, Enseignant et Parent.</p>
        </div>
        <div class="mt-14 grid md:grid-cols-3 gap-6">

            {{-- ── Mockup 1 : Tableau de bord Admin ── --}}
            <figure class="rounded-2xl overflow-hidden border border-white/10 bg-white shadow-2xl">
                <div class="flex items-center gap-1.5 px-3 py-2.5 bg-slate-100 border-b border-slate-200">
                    <span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span><span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                    <span class="ml-2 text-[10px] text-slate-400">/admin · Tableau de bord</span>
                </div>
                <div class="flex h-56">
                    <div class="w-1/3 bg-slate-900 p-2.5 space-y-1.5">
                        <div class="h-2 w-3/4 rounded bg-brand-500/80"></div>
                        @foreach([1,0,0,0,0] as $a)<div class="h-1.5 w-{{ $a ? 'full' : '5/6' }} rounded {{ $a ? 'bg-brand-600' : 'bg-white/10' }}"></div>@endforeach
                    </div>
                    <div class="flex-1 p-3 bg-slate-50">
                        <div class="grid grid-cols-3 gap-1.5 mb-2">
                            @foreach([['#2563eb','1 248'],['#10b981','96%'],['#8b5cf6','82k']] as $c)
                            <div class="rounded-md bg-white border border-slate-100 p-1.5">
                                <div class="h-1 w-2/3 rounded" style="background:{{ $c[0] }}55"></div>
                                <div class="text-[10px] font-extrabold text-slate-700 mt-1">{{ $c[1] }}</div>
                            </div>
                            @endforeach
                        </div>
                        <div class="rounded-md bg-white border border-slate-100 p-2 flex items-end gap-1 h-28">
                            @foreach([40,60,48,75,65,88,72,95] as $h)<div class="flex-1 rounded-t bg-brand-500/80" style="height:{{ $h }}%"></div>@endforeach
                        </div>
                    </div>
                </div>
                <figcaption class="px-4 py-3 bg-slate-800 text-sm font-semibold text-white">Espace Administration</figcaption>
            </figure>

            {{-- ── Mockup 2 : Faire l'appel (Enseignant) ── --}}
            <figure class="rounded-2xl overflow-hidden border border-white/10 bg-white shadow-2xl">
                <div class="flex items-center gap-1.5 px-3 py-2.5 bg-slate-100 border-b border-slate-200">
                    <span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span><span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                    <span class="ml-2 text-[10px] text-slate-400">/staff · Faire l'appel</span>
                </div>
                <div class="h-56 p-3 bg-slate-50 space-y-2">
                    <div class="grid grid-cols-4 gap-1.5">
                        @foreach([['#10b981','24'],['#ef4444','2'],['#f59e0b','1'],['#6366f1','0']] as $c)
                        <div class="rounded-md p-1.5 text-center" style="background:{{ $c[0] }}18"><div class="text-[12px] font-extrabold" style="color:{{ $c[0] }}">{{ $c[1] }}</div></div>
                        @endforeach
                    </div>
                    @foreach(['Mohamed B.','Lina T.','Youssef G.','Maryam J.'] as $i => $name)
                    <div class="flex items-center gap-2 rounded-md bg-white border border-slate-100 px-2 py-1.5">
                        <div class="h-5 w-5 rounded-full bg-brand-50 text-brand-600 grid place-items-center text-[8px] font-bold">{{ mb_substr($name,0,1) }}</div>
                        <div class="flex-1 text-[10px] font-semibold text-slate-700">{{ $name }}</div>
                        @foreach(['#10b981','#f59e0b','#ef4444'] as $j => $col)
                        <span class="h-4 w-4 rounded text-[7px] grid place-items-center font-bold" style="{{ ($i % 3 === $j) ? 'background:'.$col.';color:#fff' : 'background:#eef2f7;color:#94a3b8' }}">{{ ['P','R','A'][$j] }}</span>
                        @endforeach
                    </div>
                    @endforeach
                </div>
                <figcaption class="px-4 py-3 bg-slate-800 text-sm font-semibold text-white">Espace Enseignant</figcaption>
            </figure>

            {{-- ── Mockup 3 : Portail Parent ── --}}
            <figure class="rounded-2xl overflow-hidden border border-white/10 bg-white shadow-2xl">
                <div class="flex items-center gap-1.5 px-3 py-2.5 bg-slate-100 border-b border-slate-200">
                    <span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span><span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                    <span class="ml-2 text-[10px] text-slate-400">/parent · Tableau de bord</span>
                </div>
                <div class="h-56 p-3 bg-slate-50 space-y-2">
                    <div class="rounded-lg p-2.5 text-white" style="background:linear-gradient(135deg,#2563eb,#1d4ed8)">
                        <div class="text-[9px] opacity-80">Solde dû</div><div class="text-sm font-extrabold">220,000 TND</div>
                    </div>
                    <div class="grid grid-cols-2 gap-1.5">
                        @foreach([['Présence','92%','#10b981'],['Incidents','0','#10b981']] as $c)
                        <div class="rounded-md bg-white border border-slate-100 p-2"><div class="text-[8px] text-slate-400 font-semibold uppercase">{{ $c[0] }}</div><div class="text-xs font-extrabold" style="color:{{ $c[2] }}">{{ $c[1] }}</div></div>
                        @endforeach
                    </div>
                    <div class="rounded-md bg-white border border-slate-100 p-2">
                        <div class="text-[9px] font-bold text-slate-600 mb-1">Mes enfants</div>
                        <div class="flex items-center gap-2">
                            <div class="h-6 w-6 rounded-lg bg-brand-50 text-brand-600 grid place-items-center text-[9px] font-bold">M</div>
                            <div class="flex-1"><div class="text-[10px] font-semibold text-slate-700">Mohamed L.</div><div class="text-[8px] text-slate-400">Classe 1A</div></div>
                            <div class="text-[11px] font-extrabold text-emerald-600">92%</div>
                        </div>
                    </div>
                </div>
                <figcaption class="px-4 py-3 bg-slate-800 text-sm font-semibold text-white">Portail Parents</figcaption>
            </figure>

        </div>
        <p class="mt-8 text-center text-sm text-slate-500">Maquettes illustratives de l'interface réelle.</p>
    </div>
</section>

{{-- ══════════════════════════════ TÉMOIGNAGES ══════════════════════════════ --}}
<section class="py-20 lg:py-28 bg-white">
    <div class="mx-auto max-w-7xl px-5 lg:px-8">
        <div class="text-center max-w-2xl mx-auto">
            <span class="text-sm font-bold uppercase tracking-wider text-brand-600">Témoignages</span>
            <h2 class="mt-3 text-3xl lg:text-4xl font-extrabold text-ink tracking-tight">Ils nous font confiance</h2>
        </div>
        <div class="mt-14 grid md:grid-cols-3 gap-6">
            @foreach([
                ['« Nous avons divisé par trois le temps passé sur l\'administratif. Les parents adorent le portail. »','Mme Sonia B.','Directrice, École El Amana'],
                ['« La paie et les présences du personnel sont enfin automatisées. Un vrai soulagement. »','M. Karim J.','Responsable RH'],
                ['« Saisir les notes et générer les bulletins ne prend plus que quelques minutes. »','Mme Olfa M.','Enseignante'],
            ] as $t)
            <figure class="rounded-2xl border border-slate-200 p-7 bg-slate-50 card-hover">
                <div class="text-amber-400 text-lg">★★★★★</div>
                <blockquote class="mt-4 text-slate-700 leading-relaxed">{{ $t[0] }}</blockquote>
                <figcaption class="mt-6 flex items-center gap-3">
                    <span class="grid h-11 w-11 place-items-center rounded-full bg-brand-600 text-white font-bold">{{ mb_substr($t[1],2,1) }}</span>
                    <div>
                        <div class="font-bold text-ink text-sm">{{ $t[1] }}</div>
                        <div class="text-xs text-slate-500">{{ $t[2] }}</div>
                    </div>
                </figcaption>
            </figure>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════════════════ TARIFICATION ══════════════════════════════ --}}
<section id="tarifs" class="py-20 lg:py-28 bg-slate-50">
    <div class="mx-auto max-w-7xl px-5 lg:px-8">
        <div class="text-center max-w-2xl mx-auto">
            <span class="text-sm font-bold uppercase tracking-wider text-brand-600">Tarifs</span>
            <h2 class="mt-3 text-3xl lg:text-4xl font-extrabold text-ink tracking-tight">Une offre adaptée à chaque établissement</h2>
            <p class="mt-4 text-lg text-slate-600">Tarifs indicatifs. Contactez-nous pour un devis personnalisé.</p>
        </div>
        <div class="mt-14 grid lg:grid-cols-3 gap-6 items-start">
            @foreach([
                ['Starter','49','/ mois','Pour les petites écoles qui démarrent.',['Jusqu\'à 150 élèves','Gestion élèves & classes','Présences & emplois du temps','1 administrateur','Support par email'], false],
                ['Business','99','/ mois','Le plus populaire — toutes les fonctions clés.',['Jusqu\'à 600 élèves','Tout Starter inclus','Paiements & finances','Notes & bulletins','Portails parents & enseignants','Support prioritaire'], true],
                ['Enterprise','Sur devis','','Pour les groupes scolaires et grands établissements.',['Élèves illimités','Multi-établissements','Personnalisation avancée','Formation sur site','Accompagnement dédié'], false],
            ] as $plan)
            <div class="relative rounded-2xl border bg-white p-8 {{ $plan[5] ? 'border-brand-600 shadow-2xl shadow-brand-600/20 lg:-translate-y-3' : 'border-slate-200' }}">
                @if($plan[5])<span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-brand-600 px-4 py-1 text-xs font-bold text-white">Le plus choisi</span>@endif
                <h3 class="text-lg font-extrabold text-ink">{{ $plan[0] }}</h3>
                <p class="mt-1 text-sm text-slate-500 h-10">{{ $plan[3] }}</p>
                <div class="mt-5 flex items-end gap-1">
                    <span class="text-4xl font-extrabold text-ink">{{ $plan[1] }}</span>
                    @if($plan[2])<span class="text-slate-500 font-semibold mb-1">{{ $plan[2] }}</span>@else <span class="text-slate-500 mb-1"> TND</span>@endif
                </div>
                <a href="#contact" class="mt-6 block text-center rounded-xl px-5 py-3 text-sm font-bold transition {{ $plan[5] ? 'bg-brand-600 text-white hover:bg-brand-700' : 'bg-slate-100 text-ink hover:bg-slate-200' }}">Choisir cette offre</a>
                <ul class="mt-7 space-y-3">
                    @foreach($plan[4] as $feat)
                    <li class="flex gap-2.5 text-sm text-slate-600">
                        <svg width="18" height="18" class="shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 9l3.5 3.5L14 5"/></svg>
                        {{ $feat }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════════════════ FAQ ══════════════════════════════ --}}
<section id="faq" class="py-20 lg:py-28 bg-white">
    <div class="mx-auto max-w-3xl px-5 lg:px-8">
        <div class="text-center">
            <span class="text-sm font-bold uppercase tracking-wider text-brand-600">FAQ</span>
            <h2 class="mt-3 text-3xl lg:text-4xl font-extrabold text-ink tracking-tight">Questions fréquentes</h2>
        </div>
        <div class="mt-12 space-y-3">
            @foreach([
                ['Faut-il installer un logiciel ?','Non. '.$appName.' est une application web : un simple navigateur suffit, sur ordinateur, tablette ou mobile.'],
                ['Mes données sont-elles sécurisées ?','Oui. Chaque utilisateur accède uniquement aux données qui le concernent grâce à un cloisonnement strict par rôle (admin, enseignant, parent).'],
                ['Peut-on importer nos données existantes ?','Oui, nous accompagnons la reprise de vos listes d\'élèves, classes et personnel lors de la mise en route.'],
                ['Les parents ont-ils un accès ?','Oui, un portail dédié leur permet de suivre paiements, présences, notes et annonces en temps réel.'],
                ['La paie tunisienne est-elle gérée ?','Oui : calcul CNSS, IRPP, FOPROLOS et génération des fiches de paie conformes.'],
                ['Proposez-vous une démonstration ?','Bien sûr. Cliquez sur « Demander une démonstration » et notre équipe vous recontacte rapidement.'],
            ] as $q)
            <details class="group rounded-xl border border-slate-200 bg-white px-5 open:bg-slate-50 open:border-brand-200">
                <summary class="flex items-center justify-between py-4 font-bold text-ink">
                    {{ $q[0] }}
                    <svg class="faq-chevron transition-transform text-slate-400" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 9l4 4 4-4"/></svg>
                </summary>
                <p class="pb-5 -mt-1 text-slate-600 leading-relaxed">{{ $q[1] }}</p>
            </details>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════════════════ CTA / CONTACT ══════════════════════════════ --}}
<section id="contact" class="py-20 lg:py-28 grad-hero">
    <div class="mx-auto max-w-4xl px-5 lg:px-8 text-center">
        <h2 class="text-3xl lg:text-5xl font-extrabold text-white tracking-tight">Prêt à moderniser votre établissement ?</h2>
        <p class="mt-5 text-lg text-slate-300 max-w-2xl mx-auto">
            Demandez une démonstration gratuite et personnalisée. Notre équipe vous montre comment
            {{ $appName }} simplifie votre quotidien.
        </p>
        <div class="mt-9 flex flex-wrap justify-center gap-4">
            <a href="mailto:contact@elitecampus.tn?subject=Demande%20de%20démonstration%20{{ $appName }}"
               class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-7 py-4 text-sm font-bold text-white hover:bg-brand-700 shadow-lg shadow-brand-600/40 transition">
                Demander une démonstration
            </a>
            <a href="mailto:contact@elitecampus.tn" class="inline-flex items-center gap-2 rounded-xl bg-white/10 border border-white/20 px-7 py-4 text-sm font-bold text-white hover:bg-white/15 transition">
                Contacter l'équipe
            </a>
            <a href="/admin/login" class="inline-flex items-center gap-2 rounded-xl bg-white px-7 py-4 text-sm font-bold text-ink hover:bg-slate-100 transition">
                Accéder à la plateforme
            </a>
        </div>
        <div class="mt-10 flex flex-wrap justify-center gap-x-8 gap-y-2 text-sm text-slate-400">
            <span>📧 contact@elitecampus.tn</span>
            <span>📞 +216 71 245 678</span>
            <span>📍 Tunis, Tunisie</span>
        </div>
    </div>
</section>

{{-- ══════════════════════════════ FOOTER ══════════════════════════════ --}}
<footer class="bg-ink text-slate-400">
    <div class="mx-auto max-w-7xl px-5 lg:px-8 py-14 grid md:grid-cols-2 lg:grid-cols-4 gap-10">
        <div class="lg:col-span-1">
            <div class="flex items-center gap-2.5">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-600 text-white font-extrabold text-sm">EC</span>
                <span class="text-lg font-extrabold text-white">{{ $appName }}</span>
            </div>
            <p class="mt-4 text-sm leading-relaxed">La plateforme tout-en-un de gestion scolaire : administration, pédagogie, finances et communication réunies.</p>
            <div class="mt-5 flex gap-3">
                @foreach(['Facebook','LinkedIn','Instagram'] as $soc)
                <a href="#" aria-label="{{ $soc }}" class="grid h-9 w-9 place-items-center rounded-lg bg-white/5 hover:bg-brand-600 hover:text-white transition text-slate-400">
                    <span class="text-xs font-bold">{{ mb_substr($soc,0,1) }}</span>
                </a>
                @endforeach
            </div>
        </div>
        <div>
            <h4 class="text-white font-bold text-sm mb-4">Produit</h4>
            <ul class="space-y-2.5 text-sm">
                <li><a href="#fonctionnalites" class="hover:text-white">Fonctionnalités</a></li>
                <li><a href="#avantages" class="hover:text-white">Avantages</a></li>
                <li><a href="#tarifs" class="hover:text-white">Tarifs</a></li>
                <li><a href="#faq" class="hover:text-white">FAQ</a></li>
            </ul>
        </div>
        <div>
            <h4 class="text-white font-bold text-sm mb-4">Accès</h4>
            <ul class="space-y-2.5 text-sm">
                <li><a href="/admin/login" class="hover:text-white">Espace Administration</a></li>
                <li><a href="/staff/login" class="hover:text-white">Espace Enseignant</a></li>
                <li><a href="/parent/login" class="hover:text-white">Portail Parents</a></li>
            </ul>
        </div>
        <div>
            <h4 class="text-white font-bold text-sm mb-4">Contact</h4>
            <ul class="space-y-2.5 text-sm">
                <li>contact@elitecampus.tn</li>
                <li>+216 71 245 678</li>
                <li>Tunis, Tunisie</li>
            </ul>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="mx-auto max-w-7xl px-5 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <span>© {{ date('Y') }} {{ $appName }}. Tous droits réservés.</span>
            <div class="flex gap-5">
                <a href="#" class="hover:text-white">Mentions légales</a>
                <a href="#" class="hover:text-white">Confidentialité</a>
                <a href="#" class="hover:text-white">CGU</a>
            </div>
        </div>
    </div>
</footer>

<style>[x-cloak]{display:none!important}</style>
</body>
</html>
