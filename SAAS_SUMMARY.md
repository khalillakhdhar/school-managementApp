# EliteCampus — SaaS Multi-Tenant : Résumé & Accès

> Synthèse de la transformation d'EliteCampus en **SaaS multi-écoles** (branche `saas`).
> Généré le 2026-07-01.

---

## 🔑 Accès Super-Admin (plateforme)

Le compte **super-admin** gère toutes les écoles clientes depuis le panel `/platform` (au-dessus des tenants).

| | |
|---|---|
| **URL** | `http://127.0.0.1:8000/platform/login` |
| **Email** | `superadmin@elitecampus.tn` |
| **Mot de passe** | `Platform#2026` |

> ⚠️ Mot de passe **temporaire** : changement forcé à la 1ère connexion (`must_change_password`).
> Pour créer d'autres super-admins : `php artisan platform:create-admin --email=… --name="…"`.

### Ce que le super-admin peut faire
- Voir **toutes les écoles** (statut, plan, nb élèves/utilisateurs).
- **Créer / modifier / suspendre / réactiver** une école.
- **« Se connecter »** (impersonation) dans le panel admin d'une école, avec une bannière violette pour revenir à la plateforme.
- Widget d'ensemble : total écoles, élèves et utilisateurs de toute la plateforme.

---

## 🏫 Accès Admin d'une école (rappel)

| | |
|---|---|
| **URL** | `http://127.0.0.1:8000/admin/login` |
| **Email** | `admin@example.com` |
| **Mot de passe** | `test1234` |

Après login, l'admin est redirigé vers `/admin/{slug-école}/…` (ex. `/admin/ecole-principale/…`).
L'école existante = **« École Privée El Amana »** (tenant #1).

---

## ✅ Ce qui a été fait

Architecture : **tenancy native Filament v5** (`->tenant(School::class)`), base partagée + `school_id` + Global Scope. Résolveur central `App\Support\Tenancy` (web **et** CLI/queue).

### Phase 4 — Tenancy activée sur les 3 panels
- `/admin`, `/staff`, `/parent` deviennent `/{panel}/{slug-école}/…`.
- Branding **par école** (nom, logo, couleur d'accent).
- Panel de test « spike » supprimé.
- Backfill étendu : **tous** les utilisateurs rattachés à leur école.

### Phase 6 — Provisioning d'écoles
- Commande `php artisan school:create "Nom" --admin-email=… --admin-name=…`
  (école en essai 30 j + admin + `SchoolSetting` + niveaux + jours fériés, le tout scopé).
- `DemoDataService` rendu **tenant-safe** : correction d'un bug critique où la purge de démo (`TRUNCATE`) aurait effacé **toutes** les écoles ; désormais scopée à l'école courante.

### Phase 7 — Panel super-admin `/platform`
- Nouveau rôle `platform_admin`, panel violet **sans** tenant.
- `SchoolResource` (CRUD écoles + suspendre/réactiver + impersonation).
- Commande `php artisan platform:create-admin`.

### Phase 8.2 — Paramètres par école
- `SchoolSetting` résout la ligne du **tenant courant** (1 config par école).

### Isolation du schéma
- Contraintes uniques `levels.code`, `subjects.code`, `blog_posts.slug`, `holidays.date`
  → composées `(school_id, …)` : 2 écoles peuvent réutiliser les mêmes codes/dates.

### Phase 9 — Tests d'isolation inter-tenant (bloquant avant prod)
- `TenantIsolationTest` (9 cas) + `SchoolProvisioningTest` (3 cas) : lecture/écriture isolées,
  accès HTTP inter-école refusé, agrégations scopées, rappels isolés, unicité et paramètres
  par école, `platform_admin` vs `admin`.

### Tests sur MySQL
- Suite PHPUnit bascule de sqlite → **MySQL** (base dédiée `school_management_test`).
- **`main` : 19/19 verts · `saas` : 42/42 verts.**

---

## 🚀 Démarrer & utiliser

```bash
# 1. Herd doit tourner (MySQL sur 127.0.0.1:3306)
#    (lancer "C:\Program Files\Herd\Herd.exe" si besoin)

# 2. Serveur
php84 artisan serve --host=127.0.0.1 --port=8000

# 3. Créer une 2ᵉ école de test
php artisan school:create "École Test" --admin-email=admin@ecole-test.tn --admin-name="Directeur"
#   → affiche le mot de passe temporaire de l'admin

# 4. Vérifier l'isolation : se connecter en super-admin (/platform),
#    ouvrir chaque école, confirmer qu'un élève de l'une n'apparaît pas dans l'autre.
```

*(`php84` = `c:/Users/khali/.config/herd/bin/php84.bat`)*

---

## 🔧 Ce qui reste (différé, non bloquant)

- **`school_id` NOT NULL** — volontairement différé (l'isolation est déjà garantie par le scope ; le flip casserait les tests core sans bénéfice fonctionnel).
- **Isolation des fichiers** (Phase 8.1) — préfixer logos / justificatifs / PDF par `school_id`.
- **Facturation Stripe** (Phase 10) — nécessite des clés ; onboarding manuel en attendant.

---

## ⚠️ Note environnement (OneDrive)

Le projet est sous OneDrive « Files On-Demand » : git peut se retrouver rebasculé sur `main`
avec des fichiers non matérialisés entre deux commandes. Recommandé : clic droit sur le dossier
projet → **« Toujours conserver sur cet appareil »**, ou suspendre la synchro pendant le dev.

---

*Détails complets : `SAAS_HANDOFF.md` · `SAAS_IMPLEMENTATION_PLAN.md`.*
