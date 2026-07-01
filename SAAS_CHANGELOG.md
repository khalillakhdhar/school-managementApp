# EliteCampus — Documentation complète des changements (SaaS multi-tenant)

> Documentation exhaustive de **tout ce qui a été modifié** pour transformer EliteCampus
> (ERP scolaire mono-établissement) en **SaaS multi-écoles**, plus les correctifs associés.
> Branche : **`saas`** · Dernier commit : `95d202f` · **46/46 tests verts (MySQL)**.
> Généré le 2026-07-01.

---

## 1. Vue d'ensemble

| | |
|---|---|
| **Objectif** | Passer de 1 école à N écoles isolées (SaaS), avec un panel super-admin |
| **Stratégie** | Tenancy native **Filament v5** (`->tenant(School::class)`) · base partagée + `school_id` + Global Scope |
| **Portée de cette doc** | Phases 4→9 (activation tenancy, provisioning, super-admin, isolation) + tests MySQL + enrichissement panel + correctifs |
| **Résultat** | SaaS fonctionnel de bout en bout (hors facturation Stripe, différée) |

### Commits de la session (`91ae8bd..95d202f`)
```
95d202f feat(platform): richer super-admin panel + fix platform_admin redirect
304992a Update PasswordChangeController.php
fcd3b07 Create SAAS_SUMMARY.md
a9aab56 SaaS multi-tenant: activate tenancy on real panels, platform panel, provisioning, isolation tests
f694359 phase 1
```
**Bilan** : 36 fichiers modifiés, **+1512 / −254 lignes**.

---

## 2. Topologie des branches (2 copies préservées)

| Branche | Contenu | Commit |
|---|---|---|
| **`origin/main`** | **MVP mono-école** + config tests MySQL — SANS le SaaS | `a9e6f1c` |
| **`origin/saas`** | **SaaS complet** (cette doc) | `95d202f` |

> ⚠️ Pour lancer/utiliser le SaaS, être sur la branche **`saas`**. Sur `main`, `/platform` = 404 (normal, c'est le MVP).

---

## 3. Accès

### Super-admin (plateforme)
| | |
|---|---|
| URL | `http://127.0.0.1:8000/platform/login` |
| Email | `superadmin@elitecampus.tn` |
| Mot de passe | `Platform#2026` *(temporaire — changement forcé, puis atterrissage sur `/platform`)* |

### Admin d'une école
| | |
|---|---|
| URL | `http://127.0.0.1:8000/admin/login` |
| Email | `admin@example.com` · Mot de passe | `test1234` |

Après login, redirection vers `/admin/{slug-école}/…`. École existante : **École Privée El Amana** (tenant #1, slug `ecole-principale`).

---

## 4. Changements détaillés par domaine

### 4.1 Activation de la tenancy sur les 3 panels (Phase 4)

**Fichiers** : `app/Providers/Filament/{AdminPanelProvider,StaffPanelProvider,ParentPanelProvider}.php`

- Ajout de `->tenant(School::class, slugAttribute: 'slug', ownershipRelationship: 'school')`.
  → Les URLs deviennent `/admin/{slug}/…`, `/staff/{slug}/…`, `/parent/{slug}/…`.
- **Branding par école** (closures `Filament::getTenant()`) : nom + logo + couleur d'accent
  (render hook CSS `--tenant-accent` sur `/admin`).
- Le Global Scope `BelongsToSchool` (déjà en place) devient réellement actif : chaque liste,
  agrégation et création est filtrée/estampillée par l'école courante.

### 4.2 Modèle `User` — contrat multi-tenant

**Fichier** : `app/Models/User.php`

- `implements HasTenants` (+ `getTenants()`, `canAccessTenant()`, relation `schools()` via pivot `school_user`).
- Nouveau rôle **`platform_admin`** + helper `isPlatformAdmin()`.
- `canAccessPanel()` étendu :
  ```php
  'platform' => $this->role === 'platform_admin',
  'admin'    => $this->role === 'admin',
  'parent'   => in_array($this->role, ['parent', 'admin'], true),
  'staff'    => in_array($this->role, ['teacher', 'employee', 'admin'], true),
  ```
  (le cas `spike` a été retiré).

### 4.3 Panel super-admin `/platform` (Phase 7)

**Nouveaux fichiers** :
- `app/Providers/Filament/PlatformPanelProvider.php` — panel `id('platform')`, thème **violet**, **sans** `->tenant()` (au-dessus des écoles). Enregistré dans `bootstrap/providers.php`.
- `app/Filament/Platform/Resources/SchoolResource.php` (+ pages List/Create/Edit) — CRUD des écoles :
  statut, plan, compteurs élèves/utilisateurs, **activer/suspendre**, **impersonation** (« Se connecter »).
- `app/Filament/Platform/Resources/PlatformUserResource.php` (+ pages List/Edit) — **annuaire cross-tenant**
  de tous les comptes : rôle, école(s), **réinitialiser le mot de passe**, éditer, supprimer. Slug `platform/users`.
- `app/Filament/Platform/Widgets/PlatformStatsWidget.php` — **5 KPIs** : écoles (+ répartition statuts),
  élèves plateforme, utilisateurs, **encaissements TND**, **essais à échéance sous 7 j**.
- `app/Filament/Platform/Widgets/LatestSchoolsWidget.php` — table des dernières écoles sur le dashboard.

### 4.4 Impersonation (« Se connecter en tant que »)

- Action dans `SchoolResource` : connecte le super-admin en tant qu'admin de l'école ciblée
  (`session('impersonator_id')` + `auth()->login()` + redirect `/admin/{slug}`).
- **Bannière de retour** (violette, sticky) injectée globalement via render hook `panels::body.start`
  dans `app/Providers/AppServiceProvider.php`.
- Route de sortie : `GET /impersonate/leave` (`routes/web.php`) → restaure le super-admin → `/platform`.

### 4.5 Provisioning (Phase 6)

**Nouveaux fichiers** :
- `app/Console/Commands/CreateSchool.php` — `php artisan school:create "Nom" --admin-email=… --admin-name=…`
  Crée l'école (essai 30 j), l'admin (`must_change_password`), le rattachement `school_user`, le `SchoolSetting`,
  et un seed **scopé** (6 niveaux + jours fériés tunisiens via `HolidayService`, dans `Tenancy::runFor`).
- `app/Console/Commands/CreatePlatformAdmin.php` — `php artisan platform:create-admin --email=… --name=…`
  Crée un super-admin (rôle `platform_admin`, hors tenant).

**Modifié** : `app/Console/Commands/TenancyBackfill.php` — le backfill rattache désormais **tous** les
utilisateurs (sauf `platform_admin`) au tenant #1 (avant : admins seulement), pour que staff/parent
puissent accéder à leurs panels.

### 4.6 `DemoDataService` rendu multi-tenant (correctif critique)

**Fichier** : `app/Services/DemoDataService.php`

- **Bug corrigé** : `wipe()` faisait un `TRUNCATE` **global** → depuis un panel scopé, cela aurait
  effacé les données de **toutes** les écoles. Nouveau `wipeTenant($schoolId)` : suppression **scopée**
  à l'école courante (pivots nettoyés via les ids scopés, ordre FK respecté). Le `TRUNCATE` global n'est
  gardé que pour le mode mono-école legacy (aucun tenant actif).
- Les insertions en masse `Grade::insert()` / `StudentAttendance::insert()` (qui contournent l'estampillage)
  reçoivent désormais explicitement `school_id`.
- `attachMembersToTenant()` : les comptes démo créés (enseignants/parents) sont rattachés à l'école courante.

### 4.7 Paramètres par école (Phase 8.2)

**Fichier** : `app/Models/SchoolSetting.php`

- `getInstance()` résout la ligne de **l'école courante** (`Tenancy::id()`), 1 config par école.
  Repli sur le singleton `id=1` hors tenant (landing publique, CLI générique).
- Ajout de `school_id` dans `$fillable` + relation `school()`.

### 4.8 Isolation du schéma (migrations)

**Nouveaux fichiers** :
- `database/migrations/2026_07_01_000001_make_tenant_uniques_composite.php`
  Les contraintes uniques globales **`levels.code`**, **`subjects.code`**, **`blog_posts.slug`**,
  **`holidays.date`** deviennent **composées `(school_id, …)`** → deux écoles peuvent réutiliser les mêmes
  codes/dates sans collision.
- `database/migrations/2026_07_01_000002_add_platform_admin_role.php`
  La colonne `role` passe d'**ENUM** à **VARCHAR(30)** (cross-driver) pour accepter `platform_admin`.

### 4.9 Correctif redirection super-admin

**Fichier** : `app/Http/Controllers/PasswordChangeController.php`

- Le rôle `platform_admin` n'était pas géré → après le changement de mot de passe forcé, le super-admin
  était renvoyé ailleurs (vers `/admin`). Ajout du cas `platform_admin` :
  `show()` → panel `platform` (URL de logout) · `homeFor()` → `'/platform'`.

### 4.10 Suppression du panel « spike » (Phase 0 jetable)

**Fichiers supprimés** :
- `app/Providers/Filament/SpikePanelProvider.php`
- `app/Filament/Spike/Resources/SpikeStudentResource.php` (+ page)
- `tests/Feature/SpikeTenancyTest.php`
- entrée retirée de `bootstrap/providers.php` et du cas `spike` de `canAccessPanel()`.

---

## 5. Tests

### 5.1 Bascule sur MySQL
**Fichier** : `phpunit.xml` — `DB_CONNECTION` sqlite → **mysql**, base dédiée **`school_management_test`**
(jamais la base dev `school_management`). Détecte les problèmes spécifiques MySQL que sqlite masque.

### 5.2 Suites de tests
| Fichier | Objet | Cas |
|---|---|---|
| `tests/Feature/TenantIsolationTest.php` *(nouveau)* | Isolation inter-tenant (bloquant avant prod) | 9 |
| `tests/Feature/SchoolProvisioningTest.php` *(nouveau)* | `school:create` + `platform:create-admin` | 3 |
| `tests/Feature/PlatformPanelTest.php` *(nouveau)* | Accès `/platform` + redirection `platform_admin` | 4 |
| `tests/Feature/ErpCoreTest.php` *(modifié)* | URLs de panel adaptées au tenant (`/admin/{slug}/…`) | — |

**Total : 46/46 verts sur MySQL** (main : 19/19).

---

## 6. Référence des commandes

```bash
# Provisionner une école (tenant) complète
php artisan school:create "École Test" --admin-email=admin@ecole-test.tn --admin-name="Directeur"

# Créer un super-admin plateforme
php artisan platform:create-admin --email=ops@elitecampus.tn --name="Ops"

# Rattacher les données existantes au tenant #1 (idempotent)
php artisan tenancy:backfill

# Appliquer les migrations SaaS
php artisan migrate

# Tests (MySQL, base school_management_test)
php artisan test
```
*(`php` = `c:/Users/khali/.config/herd/bin/php84.bat` ; Herd doit tourner pour MySQL.)*

---

## 7. Ce qui reste (différé, non bloquant)

- **`school_id` NOT NULL** — volontairement différé (l'isolation est garantie par le scope ; le flip
  casserait les tests core qui créent des modèles sans tenant).
- **Isolation des fichiers** (Phase 8.1) — préfixer logos / justificatifs / PDF par `school_id`
  (`DocumentPdfController` lit encore `SchoolSetting` hors tenant).
- **Facturation Stripe** (Phase 10) — nécessite des clés ; onboarding manuel en attendant.

---

## 8. Note environnement (OneDrive)

Le projet est sous OneDrive « Files On-Demand » : git peut se retrouver rebasculé sur `main` et des
fichiers non matérialisés entre deux commandes. **Recommandé** : clic droit sur le dossier projet →
**« Toujours conserver sur cet appareil »**, ou suspendre la synchro pendant le développement.

---

## 9. Inventaire complet des fichiers (36)

**Nouveaux (17)**
```
app/Console/Commands/CreateSchool.php
app/Console/Commands/CreatePlatformAdmin.php
app/Providers/Filament/PlatformPanelProvider.php
app/Filament/Platform/Resources/SchoolResource.php
app/Filament/Platform/Resources/SchoolResource/Pages/{ListSchools,CreateSchool,EditSchool}.php
app/Filament/Platform/Resources/PlatformUserResource.php
app/Filament/Platform/Resources/PlatformUserResource/Pages/{ListPlatformUsers,EditPlatformUser}.php
app/Filament/Platform/Widgets/{PlatformStatsWidget,LatestSchoolsWidget}.php
database/migrations/2026_07_01_000001_make_tenant_uniques_composite.php
database/migrations/2026_07_01_000002_add_platform_admin_role.php
tests/Feature/{TenantIsolationTest,SchoolProvisioningTest,PlatformPanelTest}.php
SAAS_SUMMARY.md · SAAS_CHANGELOG.md (ce fichier)
```

**Modifiés (14)**
```
app/Models/{User,SchoolSetting}.php
app/Providers/Filament/{AdminPanelProvider,StaffPanelProvider,ParentPanelProvider}.php
app/Providers/AppServiceProvider.php
app/Http/Controllers/PasswordChangeController.php
app/Console/Commands/TenancyBackfill.php
app/Services/DemoDataService.php
bootstrap/providers.php · routes/web.php · phpunit.xml
tests/Feature/ErpCoreTest.php · SAAS_HANDOFF.md
```

**Supprimés (5)**
```
app/Providers/Filament/SpikePanelProvider.php
app/Filament/Spike/Resources/SpikeStudentResource.php (+ page ListSpikeStudents)
tests/Feature/SpikeTenancyTest.php
```

---

*Documents liés : `SAAS_SUMMARY.md` (synthèse + accès) · `SAAS_HANDOFF.md` & `SAAS_IMPLEMENTATION_PLAN.md` (contexte Phases 0-3).*
