# Plan d'implémentation détaillé — Multi-tenant (SaaS)

> Plan technique pas-à-pas pour transformer EliteCampus (mono-école) en SaaS multi-établissement.
> Complète `SAAS_ROADMAP.md` (vue d'ensemble) avec le code concret, l'ordre d'exécution et la vérification.
> **Stack** : Laravel 13 + Filament v5 + PHP 8.3 + MySQL. **21 modèles**, 42 migrations, 3 panels Filament.

---

## Décision d'architecture retenue

| Choix | Décision | Justification |
|---|---|---|
| **Isolation données** | Base partagée + colonne `school_id` + Global Scope | Le plus simple à opérer, suffisant jusqu'à plusieurs centaines d'écoles. Migration vers DB-par-tenant possible plus tard. |
| **Mécanique tenancy** | **Filament v5 natif** (`->tenant(School::class)`) | Filament v5 gère nativement le multi-tenant : scoping des Resources, routing, switch de tenant. On ne réinvente pas. |
| **Identification tenant** | Path-based (`/admin/{school}/...`) au départ, sous-domaine en option 2 | Le path-based est natif Filament, zéro config DNS. Sous-domaine = amélioration cosmétique ultérieure. |
| **Facturation** | Laravel Cashier (Stripe) — phase finale | Standard Laravel, différable en phase pilote. |

> ⚠️ **Filament v5 tenancy ≠ packages tiers** (`stancl/tenancy`). On utilise la tenancy applicative de Filament : une colonne `school_id`, un modèle `School` comme tenant, et `->tenant()` sur les panels. C'est documenté ici : https://filamentphp.com/docs/5.x/users/tenancy

---

## Vue d'ensemble des phases

```
Phase 0  Décisions + spike de validation              (½ j)   ← valider l'approche Filament tenancy
Phase 1  Modèle School + table schools                (1 j)
Phase 2  school_id sur les 20 modèles + migration     (2-3 j) ← le plus gros, le plus risqué
Phase 3  Trait BelongsToSchool + Global Scope         (1 j)
Phase 4  Activation tenancy Filament (3 panels)       (2 j)
Phase 5  Migration de l'école existante en tenant #1  (1 j)
Phase 6  Provisioning (commande school:create)        (1 j)
Phase 7  Panel super-admin /platform                  (2 j)
Phase 8  Isolation fichiers + SchoolSetting par tenant(1 j)
Phase 9  Tests d'isolation inter-tenant (CRITIQUE)    (1-2 j) ← bloquant avant prod
Phase 10 Facturation Stripe (optionnel/différable)    (3-4 j)
─────────────────────────────────────────────────────────────
Total cœur (Phases 0-9) : ~13-15 jours.  Facturation : +4 j.
```

---

## Phase 0 — Spike de validation (½ jour)

**But** : confirmer que Filament v5 tenancy fonctionne avec les 3 panels existants AVANT de toucher aux 20 modèles.

1. Créer une branche `feat/multitenancy`.
2. Créer un modèle `School` jetable + 1 modèle pilote scopé (`Student`).
3. Activer `->tenant(School::class)` sur le seul `AdminPanelProvider`.
4. Vérifier que `/admin/{school}/students` se charge et que le `TenantScope` filtre.
5. **Go/No-Go** : si OK, dérouler le plan. Sinon, basculer sur un Global Scope maison (Phase 3 bis).

> Livrable : note de spike (3 lignes) confirmant l'approche.

---

## Phase 1 — Modèle `School` (tenant)

### 1.1 Migration `schools`
`database/migrations/xxxx_create_schools_table.php`
```php
Schema::create('schools', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();          // pour le routing /admin/{slug}
    $table->string('subdomain')->nullable()->unique(); // phase 2 routing
    $table->string('status')->default('active'); // active | suspended | trial
    $table->string('plan')->default('trial');    // trial | standard | premium
    $table->date('trial_ends_at')->nullable();
    $table->timestamps();
    $table->softDeletes();                       // suspension/suppression propre
});
```

### 1.2 Modèle `app/Models/School.php`
```php
class School extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'slug', 'subdomain', 'status', 'plan', 'trial_ends_at'];
    protected $casts = ['trial_ends_at' => 'date'];

    // Filament tenancy : membres de l'école
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'school_user');
    }

    public function settings(): HasOne
    {
        return $this->hasOne(SchoolSetting::class);
    }
}
```

### 1.3 Table pivot `school_user` (un user peut gérer plusieurs écoles)
```php
Schema::create('school_user', function (Blueprint $table) {
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->primary(['school_id', 'user_id']);
});
```
> Sur `User` : `public function schools(): BelongsToMany { return $this->belongsToMany(School::class, 'school_user'); }`
> Implémenter le contrat Filament `HasTenants` sur `User` (`getTenants()`, `canAccessTenant()`).

---

## Phase 2 — `school_id` sur les 20 modèles (le cœur, le plus risqué)

### 2.1 Modèles à scoper
Les 20 modèles métier reçoivent `school_id` (FK non-nullable après migration des données) :

`Student`, `SchoolParent`, `Employee`, `Classroom`, `Level`, `Subject`, `Payment`, `Payroll`, `Expense`, `ExpenseCategory`, `Incident`, `Attendance`, `StudentAttendance`, `Holiday`, `BlogPost`, `Service`, `TimetableEntry`, `Grade`, `AuditLog`, `SchoolSetting`.

> `User` est géré par le pivot `school_user` (un user peut être multi-école). Les tables pivot (`parent_student`, `service_student`, `payment_service`, `classroom_subject`, `employee_subject`) héritent du scope via leurs deux extrémités — pas besoin de `school_id` direct sauf si requêtées seules.

### 2.2 Migration en 3 temps (zéro perte de données)
**Migration A — ajouter la colonne nullable :**
```php
// Pour CHAQUE table métier
Schema::table('students', function (Blueprint $table) {
    $table->foreignId('school_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
    $table->index('school_id');
});
```
> Astuce : écrire une **seule migration** qui boucle sur un tableau `$tables = ['students', 'employees', ...]` pour les 20 tables.

**Migration B — backfill (l'école existante = tenant #1) :**
```php
// Dans une migration de données dédiée, exécutée APRÈS la création de la School #1
DB::table('students')->update(['school_id' => 1]);
// ... idem pour les 20 tables
```

**Migration C — passer en non-nullable (après backfill validé) :**
```php
Schema::table('students', function (Blueprint $table) {
    $table->foreignId('school_id')->nullable(false)->change();
});
```
> Nécessite `doctrine/dbal` si Laravel < 11 ; en Laravel 13 le `->change()` natif suffit.

### 2.3 Points de vigilance migration
- L'`AuditLog` (trait `Auditable`) : le `school_id` doit être rempli à la création de chaque log → ajouter dans le trait.
- `SchoolSetting` : aujourd'hui singleton `firstOrCreate(['id' => 1])`. Devient `1 ligne par école` → réécrire `getInstance()` pour résoudre via le tenant courant (Phase 8).

---

## Phase 3 — Trait `BelongsToSchool` + Global Scope

### 3.1 Trait `app/Models/Concerns/BelongsToSchool.php`
```php
trait BelongsToSchool
{
    protected static function bootBelongsToSchool(): void
    {
        // Scope automatique en lecture (sur le tenant Filament courant)
        static::addGlobalScope('school', function (Builder $builder) {
            if ($schoolId = Filament::getTenant()?->id) {
                $builder->where($builder->getModel()->getTable() . '.school_id', $schoolId);
            }
        });

        // Remplissage automatique en écriture
        static::creating(function ($model) {
            if (! $model->school_id && $tenant = Filament::getTenant()) {
                $model->school_id = $tenant->id;
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
```
> Ajouter `use BelongsToSchool;` sur les 20 modèles. **Garder la compatibilité Filament** : Filament applique déjà son propre scope via `->tenant()` ; le Global Scope maison sert de défense en profondeur (CLI, jobs, contrôleurs API hors Filament).

### 3.2 Important — les requêtes hors Filament
`Filament::getTenant()` retourne `null` en CLI/queue/API. Pour ces contextes :
- Soit définir le tenant manuellement dans les jobs (`Filament::setTenant($school)`).
- Soit prévoir un helper applicatif `app()->instance('currentSchool', $school)` et lire les deux dans le scope.
- La commande `payments:send-reminders` (existe déjà) **doit boucler sur chaque école** et set le tenant à chaque itération.

---

## Phase 4 — Activation tenancy Filament (3 panels)

### 4.1 `User` implémente `HasTenants`
```php
class User extends Authenticatable implements FilamentUser, HasTenants
{
    public function getTenants(Panel $panel): Collection { return $this->schools; }
    public function canAccessTenant(Model $tenant): bool { return $this->schools->contains($tenant); }
}
```

### 4.2 Sur chaque PanelProvider (`Admin`, `Staff`, `Parent`)
```php
->tenant(School::class, slugAttribute: 'slug')
->tenantMiddleware([ApplyTenantScopes::class], isPersistent: true)
```
> Les routes deviennent `/admin/{school}/...`. Filament gère le tenant-switcher dans la sidebar automatiquement.

### 4.3 Audit des requêtes qui contournent le scope
À vérifier **un par un** (ces requêtes ne passent pas par Eloquent Global Scope) :
- `MainDashboardWidget` : `selectRaw` + `leftJoin` agrégations → ajouter `WHERE school_id = ?` manuel.
- `FinancialReport.php` : agrégations SQL.
- Les 13 `*ListStatsWidget.php` : si elles utilisent `Model::count()` Eloquent → OK auto ; si `DB::table()` → à corriger.
- `DemoDataService` : doit cibler une école précise.
- Commande `payments:send-reminders`.

### 4.4 `canAccessPanel()` mis à jour
Combiner rôle **et** appartenance à l'école courante (le tenant). Filament vérifie déjà `canAccessTenant()`, mais garder le `role` check existant.

---

## Phase 5 — Migration de l'école existante (tenant #1)

1. Seeder/commande one-shot : créer `School #1` à partir des `school_settings` actuels (nom, slug `el-amana` ou équivalent).
2. Rattacher l'admin existant via `school_user`.
3. Exécuter la Migration B (backfill `school_id = 1` partout).
4. **Vérification post-migration** : script comparant `COUNT(*)` par table avant/après (doit être identique, toutes les lignes ont `school_id = 1`).
5. Reconstruire `SchoolSetting` en ligne rattachée à `school_id = 1`.

> Livrable : rapport de migration (comptes par table) prouvant zéro perte.

---

## Phase 6 — Provisioning d'une nouvelle école

### Commande `app/Console/Commands/CreateSchool.php`
```php
php artisan school:create "Nom École" --admin-email=... --admin-name=...
```
Étapes exécutées :
1. Créer la ligne `schools` (slug auto, plan `trial`, `trial_ends_at = now()+30j`).
2. Créer le compte admin + rattacher via `school_user`.
3. Créer `SchoolSetting` par défaut.
4. Seed minimal **scopé sur le nouveau tenant** : niveaux scolaires + jours fériés tunisiens (`HolidayService` existe déjà — l'appeler avec le tenant set).
5. Marquer `must_change_password = true` sur l'admin.

> Réutiliser la logique de `DemoDataService` comme base, mais en ciblant `Filament::setTenant($school)` au lieu de la base entière.

---

## Phase 7 — Panel super-admin `/platform`

### 7.1 Nouveau panel `PlatformPanelProvider`
- `->id('platform')->path('platform')` **sans** `->tenant()` (il est au-dessus des tenants).
- Réservé au rôle `platform_admin` (nouveau rôle, distinct de `admin` = admin d'une école).

### 7.2 `SchoolResource` (CRUD des écoles clientes)
- Liste : nom, plan, statut, nb élèves, nb users, date fin d'essai.
- Actions : activer / suspendre (`status`), changer de plan, « se connecter en tant que » (impersonation tenant).
- Widget d'usage : total écoles, écoles actives, MRR (si facturation).

### 7.3 `canAccessPanel()` étendu
```php
'platform' => $this->role === 'platform_admin',
```

---

## Phase 8 — Isolation fichiers & SchoolSetting

1. **Stockage** : préfixer les uploads par `school_id`.
   - Logos/favicons (`SchoolSetting`), images de couverture `BlogPost`, justificatifs `Expense`, PDF générés.
   - Pattern : `storage/app/public/schools/{school_id}/...` ou disque dédié.
   - Adapter `DocumentPdfController` (bulletins/fiches de paie) pour les chemins scopés.
2. **`SchoolSetting`** : réécrire `getInstance()` → `School::current()->settings` au lieu du singleton `id=1`. Le branding du panel (logo, couleurs) lit le tenant.
3. **Emails** : l'expéditeur et le pied de page des 4 Mailables référencent l'identité de l'école émettrice (déjà partiellement via le nom en base).

---

## Phase 9 — Tests d'isolation inter-tenant (CRITIQUE, bloquant)

> Sans ces tests, **ne pas mettre en prod multi-tenant**. C'est la garantie qu'une école ne lit/écrit jamais les données d'une autre.

### Fichier `tests/Feature/TenantIsolationTest.php`
Cas à couvrir :
1. Un user de l'école A authentifié sur le tenant A ne voit **aucune** ligne de l'école B (par modèle : Student, Payment, Grade, etc.).
2. Une tentative d'accès direct `/admin/{schoolB}/students/{id_de_A}` → 403/404.
3. Création : un enregistrement créé sous le tenant A reçoit bien `school_id = A`.
4. Les agrégations widgets (dashboard) ne comptent que le tenant courant.
5. La commande `payments:send-reminders` envoie à chaque école **ses** impayés uniquement.
6. Un `platform_admin` voit toutes les écoles ; un `admin` n'en voit qu'une.

> Étendre la suite actuelle (19 tests). Objectif : suite verte avec ces ~10 tests d'isolation en plus.

---

## Phase 10 — Facturation Stripe (différable)

1. `composer require laravel/cashier`, trait `Billable` sur `School`.
2. Tables `plans` (limites : nb élèves max, fonctionnalités) + abonnements Cashier (`subscriptions`).
3. Middleware quota : bloquer/avertir si l'école dépasse `students_max` de son plan.
4. Page de facturation dans le panel admin de chaque école (historique, changement de plan).
5. Webhooks Stripe : suspension auto si paiement échoue (`status = suspended`).

> Différable : en phase pilote, onboarder les écoles manuellement en `plan = standard` gratuit.

---

## Ordre d'exécution & jalons Go/No-Go

1. **Phase 0** → Go/No-Go sur l'approche Filament tenancy. **Bloquant.**
2. **Phases 1-3** → fondation données + scope. À tester en isolation avant d'activer les panels.
3. **Phase 4-5** → activation + migration de l'école réelle. Valide le système avec un cas de prod.
4. **Phase 9** → **bouclée avant** d'accepter une 2ᵉ école. Test d'isolation = condition de mise en prod.
5. **Phases 6-7** → nécessaires dès le 2ᵉ client (provisioning + super-admin).
6. **Phase 8** → en parallèle de 6-7, faible risque.
7. **Phase 10** → après les premiers clients pilotes.

---

## Risques & mitigations

| Risque | Impact | Mitigation |
|---|---|---|
| Requêtes SQL brutes contournant le Global Scope (fuite inter-tenant) | **Critique** | Audit exhaustif Phase 4.3 + tests Phase 9 |
| Migration `school_id` sur prod existante | Élevé | Migration en 3 temps (nullable → backfill → not-null) + rapport de comptes |
| `Filament::getTenant()` null en CLI/queue | Moyen | Set explicite du tenant dans jobs/commandes + helper applicatif |
| Policies existantes (18) à re-vérifier avec le scope | Moyen | Les Policies vérifient « qui », le scope vérifie « quelle école » — 2 couches, voir `POLICIES_ROADMAP.md`. Retirer `skipAuthorization()` reste optionnel. |
| Collision de noms de fichiers entre écoles | Moyen | Préfixe `school_id` sur tous les chemins de stockage (Phase 8) |
| i18n / RTL | Aucun | Indépendant du tenant, déjà terminé (voir `IMPROVEMENTS_ROADMAP.md` item 11) |

---

## Ce qui est déjà réutilisable

- **Logique métier** (paie, paiements, présences, bulletins) : raisonne déjà « dans une école », il suffit de garantir le scope.
- **`HolidayService`** : réutilisable par toute école tunisienne, à appeler avec le tenant set.
- **`DemoDataService`** : base pour la commande `school:create` (Phase 6).
- **Les 18 Policies** (`POLICIES_ROADMAP.md`) : couche « autorisation » complémentaire au scope « tenant ».
- **i18n FR/EN/AR + RTL** : zéro travail supplémentaire, profite à toutes les écoles.

---

## Vérification end-to-end (à exécuter à la fin)

```bash
# 1. Migrations propres sur base fraîche
php artisan migrate:fresh

# 2. Provisionner 2 écoles
php artisan school:create "École A" --admin-email=a@test.tn --admin-name="Admin A"
php artisan school:create "École B" --admin-email=b@test.tn --admin-name="Admin B"

# 3. Tests d'isolation (doivent tous passer)
php artisan test --filter=TenantIsolation

# 4. Suite complète (régression : les 19 tests actuels + isolation)
php artisan test

# 5. Manuel : se connecter sur /admin/ecole-a, créer un élève,
#    vérifier qu'il n'apparaît PAS sur /admin/ecole-b
```

---

*Document généré le 2026-06-27. Plan d'implémentation détaillé de l'item 12 (`IMPROVEMENTS_ROADMAP.md`). À lire avec `SAAS_ROADMAP.md` (vue d'ensemble), `POLICIES_ROADMAP.md` (couche autorisation) et `PERMISSIONS_AUDIT.md`. Estimation : ~13-15 j pour le cœur (Phases 0-9), +4 j facturation.*
