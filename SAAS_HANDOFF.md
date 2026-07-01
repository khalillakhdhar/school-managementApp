# SaaS Multi-Tenant — Handoff & Plan de finalisation

> **But de ce document** : état exact et autonome du chantier multi-tenant d'EliteCampus, pour **démarrer une nouvelle conversation** sans contexte préalable. Contient : l'app, l'état des branches `saas`/`main`, ce qui est **fait** (Phases 0-3 + correctif Livewire), les **décisions/pièges** à connaître, et les **étapes restantes** (Phases 4-10) pour atteindre un SaaS 100 % fonctionnel.

---

## ⚡ Mise à jour 2026-07-01 — Phases 4, 6, 7, 8.2, 9 IMPLÉMENTÉES

Le SaaS multi-tenant est désormais **fonctionnel de bout en bout** (hors facturation). **42/42 tests verts** (sqlite). Fait dans cette session :

- **Phase 4** ✅ — `->tenant(School::class, slugAttribute:'slug', ownershipRelationship:'school')` activé sur les 3 panels (`/admin`, `/staff`, `/parent`) → routes `/{panel}/{école-slug}/...`. Branding par tenant (nom + logo + couleur d'accent). **Panel spike supprimé** (provider, `app/Filament/Spike`, entrée `bootstrap/providers.php`, `SpikeTenancyTest`, case `spike` de `canAccessPanel`). Backfill étendu : **tous** les users (sauf `platform_admin`) rattachés au tenant (pour staff/parent).
- **Phase 6** ✅ — `php artisan school:create "Nom" --admin-email=… --admin-name=…` (école trial + admin `must_change_password` + `SchoolSetting` + seed scopé niveaux/jours fériés). **`DemoDataService` rendu tenant-safe** : `wipe()` ne fait plus de `TRUNCATE` global sous un tenant (→ `wipeTenant()` scopé, sinon on effaçait toutes les écoles !), `Grade::insert`/`StudentAttendance::insert` estampillent `school_id`, comptes démo rattachés au tenant.
- **Phase 7** ✅ — Panel super-admin **`/platform`** (violet, **sans** `->tenant()`), rôle `platform_admin`, `SchoolResource` (CRUD écoles, statut, plan, compteurs élèves/users, activer/suspendre, **impersonation** avec bannière de retour), `PlatformStatsWidget`. Commande `php artisan platform:create-admin`.
- **Phase 8.2** ✅ — `SchoolSetting::getInstance()` résout la ligne **du tenant courant** (fallback singleton id=1 hors tenant).
- **Contraintes uniques** ✅ — migration `make_tenant_uniques_composite` : `levels.code`, `subjects.code`, `blog_posts.slug`, `holidays.date` passent en **composite `(school_id, X)`** (sinon 2 écoles ne pouvaient pas réutiliser le même code/date).
- **Phase 9** ✅ — `tests/Feature/TenantIsolationTest.php` (9 cas) + `SchoolProvisioningTest.php` (3 cas) : isolation lecture/écriture, accès HTTP inter-tenant refusé (403/404), agrégations scopées, rappels isolés, unicité per-tenant, settings per-tenant, `platform_admin` vs `admin`.

**Reste à faire** : Phase 5 flip `NOT NULL` (**volontairement différé** — casserait les tests core qui créent des modèles sans tenant ; l'isolation est déjà garantie par le scope), Phase 8.1 (préfixe fichiers/PDF par `school_id`), Phase 10 (facturation Stripe).

**À exécuter une fois MySQL démarré** (non fait ici car MySQL était éteint) :
```bash
php artisan migrate            # applique make_tenant_uniques_composite + add_platform_admin_role
php artisan tenancy:backfill   # rattache MAINTENANT tous les users (pas que les admins) au tenant #1
php artisan platform:create-admin --email=ops@elitecampus.tn --name="Ops"   # crée le super-admin /platform
```

---

## 0. L'application en bref

- **EliteCampus** : ERP de gestion scolaire (Tunisie). Laravel 13, **Filament v5.6**, **Livewire 4**, PHP 8.3/8.4, MySQL.
- **3 panels Filament** : `/admin` (admin école), `/staff` (enseignant/employé), `/parent` (parent).
- **21 modèles** métier, ~42 migrations. i18n FR/EN/AR + RTL complète. 18 Policies (couche autorisation). Tests PHPUnit.
- Aujourd'hui **mono-établissement** ; le chantier en cours le transforme en **SaaS multi-écoles** via la **tenancy native de Filament v5** (`->tenant(School::class)`), stratégie **base partagée + `school_id` + Global Scope**.

### Environnement de dev (Windows)
- PHP : **`c:/Users/khali/.config/herd/bin/php84.bat`** (PHP 8.4 avec `intl`). Ne PAS utiliser un PHP sans `intl`.
- Serveur : `php84 artisan serve --host=127.0.0.1 --port=8000` → `http://127.0.0.1:8000`.
- MySQL doit être démarré (DB `school_management`).
- **Piège outillage** : `php84.bat -r "..."` multi-lignes échoue silencieusement → toujours écrire un fichier `.php` temporaire puis l'exécuter. `grep -P` cassé (locale) → utiliser le tool Grep ou des scripts PHP `preg_*` `/u`.

### Comptes de connexion
- **Admin** : `admin@example.com` / **`test1234`** (mot de passe fixé pendant le debug ; `must_change_password = 0`).
- Comptes démo (Mode Démo) : mot de passe `demo1234` (enseignant `salimwhichi@elamana.tn`, parent `parent1@elamana.tn`).
- Login admin : `http://127.0.0.1:8000/admin/login`.

---

## 1. État des branches Git

| Branche | Rôle | Contient le multi-tenant ? |
|---|---|---|
| **`saas`** | Développement du multi-tenant | ✅ Oui — Phases 0-3 faites |
| **`main`** | Version mono-école stable | ❌ Non (sauf le correctif Livewire, partagé) |

- **Le travail multi-tenant vit sur `saas`** (commits jusqu'à `P3` + suivants). `main` reste mono-école.
- **Correctif Livewire 403** appliqué et committé **sur les DEUX branches** (`saas`: `f98c1bb`, `main`: `b446190`) car c'est un bug pré-existant partagé (voir §4).
- Fichiers présents **uniquement sur `saas`** : `app/Models/School.php`, `app/Models/Concerns/BelongsToSchool.php`, `app/Support/Tenancy.php`, `app/Console/Commands/TenancyBackfill.php`, `app/Providers/Filament/SpikePanelProvider.php`, `app/Filament/Spike/**`, `tests/Feature/{SpikeTenancyTest,SchoolModelTest,TenantScopeRolloutTest,TenancyContextTest}.php`, `SAAS_ROADMAP.md`, `SAAS_IMPLEMENTATION_PLAN.md`, ce fichier.

> ⚠️ **Reprendre le développement multi-tenant sur `saas`** (`git checkout saas`). Ne pas coder le multi-tenant sur `main`.

---

## 2. Ce qui est FAIT (Phases 0-3) — sur `saas`

Détail complet dans `SAAS_IMPLEMENTATION_PLAN.md` (sections « Résultat Phase X »). Résumé :

### ✅ Phase 0 — Spike de validation (GO)
- Migrations `schools`, `school_user` (pivot user↔école), `students.school_id`.
- `App\Models\School` (tenant). `User implements HasTenants` (`getTenants`, `canAccessTenant`, relation `schools`).
- Trait `App\Models\Concerns\BelongsToSchool` : global scope lecture + estampillage `school_id` en création.
- **Panel jetable `/spike`** (`SpikePanelProvider` + `App\Filament\Spike\Resources\SpikeStudentResource`) qui a validé la tenancy Filament en isolation, **sans toucher aux 3 panels de prod**. → **À SUPPRIMER en Phase 4.**
- Validé : routing `spike/{tenant:slug}/...`, scope lecture/écriture, contrôle d'accès tenant. Panels prod intacts.

### ✅ Phase 1 — Modèle School complet + branding
- `School` implémente `HasName`, `HasAvatar`, `HasCurrentTenantLabel` (nom + logo dans le tenant switcher).
- Champs branding (migration append-only) : `logo_path`, `primary_color`, `email`, `phone`, `city`, `country`.
- Helpers : `isActive/isSuspended/isOnTrial/trialHasExpired`, constantes `STATUS_*`, auto-slug unique (respecte soft-delete), `logoUrl()`, `brandColor()`.
- `SchoolFactory` (états `trial()`, `suspended()`).
- Branding par tenant démontré sur le panel spike (`brandName`/`brandLogo` en closures + couleur d'accent via render hook CSS).

### ✅ Phase 2 — `school_id` sur les 20 modèles + backfill
- Migration looping `..._add_school_id_to_tenant_tables` : `school_id` **nullable**, FK `cascadeOnDelete`, index, sur les 19 tables restantes (students déjà fait).
- Trait `BelongsToSchool` ajouté à **19 modèles** (+ `school_id` en `fillable`). Les modèles `Auditable` (Payment/Payroll/Grade) combinent `use Auditable, BelongsToSchool;`.
- `SchoolSetting` : colonne + relation `school()` **sans** global scope (singleton, réécriture `getInstance()` reportée en Phase 8).
- Trait `Auditable` : chaque `AuditLog` hérite du `school_id` du modèle audité.
- **Commande idempotente `php artisan tenancy:backfill`** : crée/résout le tenant #1 depuis `school_settings`, backfill les 20 tables (NULL→#1), rattache les admins via `school_user`, rapport de vérification (0 orphelin).
- **Exécutée sur la démo** : tenant #1 = **« École Privée El Amana »** (slug `ecole-principale`), toutes les lignes rattachées.

### ✅ Phase 3 — Mécanique de scoping (CLI/queue) + audit SQL
- **`App\Support\Tenancy`** : `current()`, `id()`, `check()`, `runFor(School, Closure)` (nestable), `eachSchool(Closure)` (boucle écoles vivantes). `Filament::getTenant()` protégé try/catch (null en CLI).
- `BelongsToSchool` rebranché sur `Tenancy::id()` → le scope marche aussi en CLI/queue.
- **`payments:send-reminders` refactorée** en `Tenancy::eachSchool` : chaque école ne traite que ses impayés, digest aux admins de cette école (via pivot).
- **Audit SQL** : tous les widgets/rapports (`MainDashboardWidget`, `FinancialReport`, `ExpensesListStatsWidget`, 13 `*ListStatsWidget`) partent d'une **base Eloquent** → auto-scopés dès qu'un tenant est actif. **Aucun `DB::table()` dans l'UI**. → aucune correction requise côté widgets.

### État tests (sur `saas`) : **33/33 verts** (13 core + 6 policies + 14 tenancy).

---

## 3. Décisions & pièges CRITIQUES à connaître (pour la suite)

1. **`school_id` est NULLABLE**, volontairement. Le flip en NOT NULL (Migration C) est **reporté en Phase 5**, APRÈS que la tenancy soit active sur les vrais panels (Phase 4). Raison : tant que `/admin` n'a pas de tenant, une création n'est pas estampillée → violerait la contrainte. **Ne pas mettre NOT NULL avant la Phase 4.**
2. **Le Global Scope est no-op sans tenant.** En l'état, `/admin` (pas de tenancy) → scope inactif → l'admin voit **tout** (les 20 tables ont `school_id=1`). L'app démo reste 100 % fonctionnelle. Le scoping réel s'activera quand les panels auront `->tenant()`.
3. **`Tenancy::current()` = tenant Filament (web) OU `currentSchool` lié (CLI).** En CLI/queue/jobs, envelopper le travail dans `Tenancy::runFor()`/`eachSchool()`.
4. **Requêtes SQL brutes hors UI** (`DemoDataService`, jobs custom, futures commandes) NE passent PAS par le scope Eloquent → filtrer `school_id` à la main ou utiliser `runFor`.
5. **`User` n'a PAS `BelongsToSchool`** (relation many-to-many via `school_user`). Pour scoper les users d'une école : `$school->users()`.
6. **Mails `ShouldQueue`** (ex. `PaymentReminderMail`) : en test, `Mail::assertQueued` (pas `assertSent`).
7. **Panel `/spike` = jetable** : le supprimer en Phase 4 (fichier provider + `App\Filament\Spike\**` + entrée dans `bootstrap/providers.php` + `tests/Feature/SpikeTenancyTest.php` qui en dépend).
8. **`SchoolSetting` est un singleton** (`getInstance()` fait `firstOrCreate(['id'=>1])`) → à rendre per-tenant en Phase 8. Ne pas lui ajouter le global scope avant.

---

## 4. Correctif Livewire 403 (fait sur les 2 branches) — contexte

- **Symptôme** : un modal « 403/Forbidden » apparaît puis disparaît au clic extérieur, sur `main` et `saas`.
- **Cause** : Livewire 4 dérive son endpoint de `APP_KEY` (`/livewire-<hash>/update`) et le garde avec `RequireLivewireHeaders` (**abort 404** si header `X-Livewire` absent). Après bfcache/session expirée/snapshot périmé, une requête Livewire de fond (poll notifications) échoue → Livewire affiche la réponse d'erreur dans un **modal dismissible**. Ce n'est **pas** un vrai refus d'autorisation.
- **Fix** : render hook global (`AppServiceProvider`) + partial `resources/views/filament/livewire-error-recovery.blade.php` qui intercepte les requêtes Livewire échouées (403/404/419) et **recharge une fois** (garde anti-boucle `sessionStorage` 8s) au lieu d'afficher le modal.
- Committé : `saas` `f98c1bb`, `main` `b446190`.

---

## 5. Ce qui RESTE — Phases 4 → 10 (feuille de route)

> Réfé­rence détaillée : `SAAS_IMPLEMENTATION_PLAN.md`. Ci-dessous, le plan d'action condensé et ordonné.

### 🔜 Phase 4 — Activer la tenancy sur les VRAIS panels (cœur visible)
**Objectif** : `/admin` devient `/admin/{école}/...`, le scope s'active réellement.
1. Sur `AdminPanelProvider`, `StaffPanelProvider`, `ParentPanelProvider` : ajouter
   `->tenant(School::class, slugAttribute: 'slug', ownershipRelationship: 'school')`.
2. Porter le **branding par tenant** (déjà prototypé sur le spike) sur les 3 panels : `brandName`/`brandLogo` en closures `Filament::getTenant()`, couleur d'accent via render hook.
3. **Marquer les Resources non liées à une école** pour ne pas casser (celles dont le modèle n'a pas `school_id` de façon pertinente) : `protected static bool $isScopedToTenant = false;` — à vérifier au cas par cas (ex. `AuditLog` reste scopé ; il n'y a pas de resource pour `School` côté admin-école).
4. `canAccessPanel()` : combiner rôle **et** appartenance (`canAccessTenant` est déjà géré par Filament ; garder le check `role`).
5. **Supprimer le panel spike** : `SpikePanelProvider`, `app/Filament/Spike/**`, l'entrée dans `bootstrap/providers.php`, et `tests/Feature/SpikeTenancyTest.php` (ou le réécrire sans le panel spike).
6. Gérer les redirections « pas de tenant » : un admin sans école, la résolution du tenant par défaut (Filament `HasDefaultTenant` possible sur `User`).
7. **Tester** : login admin → redirigé vers `/admin/ecole-principale/...`, les listes ne montrent que les données du tenant, création estampillée.

**Points de vigilance Phase 4** :
- Les render hooks existants (`sidebar-school-info`, `notification-bell`) font des requêtes Eloquent → deviennent auto-scopées (OK) mais vérifier qu'ils ne plantent pas hors tenant.
- La landing publique `/` et les PDF (`DocumentPdfController`) sont hors panel → pas de tenant Filament ; adapter si besoin (souvent OK car accès par record).

### 🔜 Phase 5 — Migration de l'école existante + NOT NULL
1. Re-jouer `php artisan tenancy:backfill` (idempotent) pour rattraper d'éventuelles lignes créées `NULL` depuis la Phase 2.
2. **Migration C** : passer `school_id` en `NOT NULL` sur les 20 tables (une fois la Phase 4 en place et le backfill validé).
3. Vérifier 0 orphelin avant/après (le rapport de `tenancy:backfill` le fait).

### 🔜 Phase 6 — Provisioning d'une nouvelle école
1. Commande `php artisan school:create "Nom" --admin-email=... --admin-name=...` : crée `schools` (slug auto, plan `trial`), l'admin + rattachement `school_user`, `SchoolSetting` par défaut, seed minimal **scopé** (niveaux + jours fériés via `HolidayService`, en `Tenancy::runFor`), `must_change_password=true`.
2. **Rendre `DemoDataService` tenant-aware** (il crée encore des données sans tenant) : cibler une école via `Tenancy::runFor`.
3. Suspension/suppression propre d'une école (soft-delete cascade).

### 🔜 Phase 7 — Panel super-admin `/platform`
1. Nouveau `PlatformPanelProvider` (`->id('platform')->path('platform')`, **sans** `->tenant()`).
2. Rôle `platform_admin` (distinct de `admin` = admin d'une école) ; `canAccessPanel('platform')`.
3. `SchoolResource` (CRUD écoles clientes) : statut, plan, nb élèves/users, activer/suspendre, « se connecter en tant que » (impersonation tenant).

### 🔜 Phase 8 — Isolation fichiers + SchoolSetting per-tenant
1. Préfixer le stockage par `school_id` (logos, `BlogPost` covers, `Expense` justificatifs, PDF) → `storage/app/public/schools/{id}/...`.
2. Réécrire `SchoolSetting::getInstance()` pour résoudre via le tenant courant (1 ligne par école) + lui donner le global scope.
3. Emails : expéditeur/pied de page selon l'école émettrice.

### 🔜 Phase 9 — Tests d'isolation inter-tenant (BLOQUANT avant prod)
1. `tests/Feature/TenantIsolationTest.php` : un user de l'école A ne lit/écrit JAMAIS les données de B (par modèle) ; accès direct à un id d'une autre école → 403/404 ; agrégations widgets scopées ; `payments:send-reminders` isolé ; `platform_admin` voit tout, `admin` une seule école.
2. **Condition de mise en prod multi-tenant.**

### 🔜 Phase 10 — Facturation (différable)
1. Laravel Cashier (Stripe), trait `Billable` sur `School`.
2. Tables `plans`/`subscriptions`, quotas (nb élèves max), page de facturation par école, webhooks (suspension si paiement échoue).

---

## 6. Ordre & jalons

```
Phase 4  (tenancy sur vrais panels)  ← PROCHAINE ÉTAPE, la plus visible
Phase 5  (migration école + NOT NULL)
Phase 6  (provisioning school:create)      \
Phase 7  (super-admin /platform)            } nécessaires dès le 2e client
Phase 8  (fichiers + SchoolSetting)        /
Phase 9  (tests isolation)  ← BLOQUANT avant d'accepter une 2e école payante
Phase 10 (facturation)      ← différable (pilote gratuit possible)
```

---

## 7. Démarrage rapide d'une nouvelle session

```bash
# 1. Se placer sur la branche multi-tenant
git checkout saas

# 2. Démarrer MySQL, puis le serveur (PHP 8.4)
c:/Users/khali/.config/herd/bin/php84.bat artisan serve --host=127.0.0.1 --port=8000

# 3. Vérifier l'état
c:/Users/khali/.config/herd/bin/php84.bat artisan test          # doit être vert
c:/Users/khali/.config/herd/bin/php84.bat artisan migrate       # à jour
c:/Users/khali/.config/herd/bin/php84.bat artisan tenancy:backfill   # idempotent, doit dire 0 orphelin

# 4. Login admin : http://127.0.0.1:8000/admin/login  (admin@example.com / test1234)
```

**Première tâche de la nouvelle conversation : Phase 4** (activer `->tenant()` sur les 3 panels + supprimer le panel spike). Lire d'abord `SAAS_IMPLEMENTATION_PLAN.md` §Phase 4 et §3 (pièges) de ce document.

---

*Généré le 2026-07-01. Documents liés : `SAAS_IMPLEMENTATION_PLAN.md` (plan détaillé + résultats Phases 0-3), `SAAS_ROADMAP.md` (vue d'ensemble/évaluation), `POLICIES_ROADMAP.md` (autorisation), `IMPROVEMENTS_ROADMAP.md` (items 1-13).*
