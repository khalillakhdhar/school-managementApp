# SaaS Multi-Établissement — État actuel & reste à faire

> Évaluation honnête : à ce jour, **EliteCampus est une application mono-établissement (single-tenant)**. Aucune brique de multi-tenancy n'existe dans le code. Ce document détaille précisément ce qui couvre quoi, et tout ce qu'il reste à construire pour transformer l'app en véritable SaaS multi-écoles.

---

## 1. Évaluation de la couverture actuelle

| Brique nécessaire à un SaaS multi-tenant | État | Détail |
|---|---|---|
| Table `schools` / notion de tenant | ❌ Absent | Aucune table ne représente "une école". `school_settings` est un **singleton** (`firstOrCreate(['id' => 1], ...)`) — une seule ligne possible, pour toute l'installation. |
| Colonne `school_id` sur les modèles métier | ❌ Absent | `Student`, `Employee`, `Payment`, `Classroom`, etc. n'ont **aucune** clé d'isolation. Vérifié par recherche exhaustive : zéro occurrence de `school_id`/`tenant_id` dans `app/` ou `database/migrations/`. |
| Scoping automatique des requêtes par tenant | ❌ Absent | Aucun *Global Scope*, aucun trait `BelongsToTenant`. Toute requête (`Student::all()`, etc.) retourne les données de **tous** les utilisateurs confondus s'il y avait plusieurs écoles. |
| Package de multi-tenancy (`stancl/tenancy`, `spatie/laravel-multitenancy`...) | ❌ Absent | `composer.json` ne contient que `filament/filament`, `laravel/framework`, `dompdf`, `filament-language-switch`. Rien lié au tenancy. |
| Identification du tenant (sous-domaine / domaine / path) | ❌ Absent | Un seul panel Filament par rôle, chemins fixes (`/admin`, `/parent`, `/staff`), aucune résolution de domaine. `routes/web.php` sert une landing page unique, pas de routing par école. |
| Authentification liée à un tenant | ❌ Absent | `User::role` est un rôle global (`admin/parent/teacher/employee`) sans notion d'école. Un même email ne peut exister que dans une seule "instance" de toute façon (DB unique, pas de scoping). |
| Provisioning automatisé d'une nouvelle école | ❌ Absent | Pas de commande/process pour créer une nouvelle école (migrations, seed, compte admin initial). Le seul "provisioning" existant est `DemoDataService` qui peuple/vide les données de démo **dans la base courante**, pas un tenant isolé. |
| Facturation / abonnement (Stripe ou autre) | ❌ Absent | Aucune table `subscriptions`/`plans`, aucun package de paiement SaaS (Stripe Cashier, Paddle...). Le `Payment`/`PayrollService` actuels gèrent les **paiements des élèves à l'école**, pas l'abonnement de l'école à la plateforme — deux notions totalement différentes, à ne pas confondre. |
| Super-admin (gestion des écoles clientes) | ❌ Absent | Le seul rôle "admin" existant est l'administrateur **d'une école**, pas un administrateur de plateforme capable de gérer plusieurs écoles. |
| Personnalisation par tenant (logo, couleurs, sous-domaine) | ⚠️ Partiel | `SchoolSetting` gère déjà logo/couleurs/coordonnées — mais pour **une seule école**. La logique est réutilisable telle quelle une fois `school_id` ajouté, mais aujourd'hui elle est mono-tenant. |
| Isolation du stockage fichiers (logos, PDF, exports) | ❌ Absent | `storage/app/public` est un répertoire plat partagé, sans sous-dossier par école. |
| Isolation des emails (expéditeur, contenu par école) | ⚠️ Partiel | Les emails sont déjà traduits par locale ([[i18n-p3-11]]), mais l'expéditeur/templates ne référencent pas dynamiquement l'identité de l'école émettrice au-delà du nom déjà en base. |
| Sécurité / isolation stricte entre tenants | ❌ Absent | Sans `school_id` + Global Scope, il n'y a **aucune barrière technique** empêchant une fuite de données entre écoles si plusieurs étaient un jour hébergées sur la même base. |
| Policies par modèle | ❌ Absent (item 13 du roadmap, séparé mais lié) | Sans Policies explicites, le futur scoping par tenant devra être ajouté en plus, pas à la place. |

**Conclusion** : la base fonctionnelle (gestion scolaire : élèves, paie, paiements, présences, i18n, RTL, PDF, notifications) est mature et solide pour **une école**. Mais **0 % de la mécanique multi-tenant n'est en place** — il s'agit d'un chantier d'architecture à part entière, pas d'un ajustement.

---

## 2. Ce qu'il reste à faire pour finir le SaaS

### Étape 0 — Décision d'architecture (préalable obligatoire)
Avant d'écrire la moindre ligne de code, trancher :
- **Stratégie d'isolation des données** :
  - *Base partagée, schéma partagé* (`school_id` sur chaque table + Global Scope) — le plus simple à opérer, suffisant jusqu'à plusieurs centaines d'écoles, recommandé pour démarrer.
  - *Base partagée, schéma par tenant* — isolation plus forte, complexité de migration plus élevée.
  - *Base par tenant* (`stancl/tenancy` mode multi-DB) — isolation maximale, mais opérationnellement plus lourd (migrations à rejouer sur N bases, sauvegardes par tenant, etc.).
  - → Recommandation pour ce projet (taille, stack Filament) : **base partagée + `school_id` + Global Scope**, avec possibilité de migrer vers DB-par-tenant plus tard si un client l'exige (conformité, gros volume).
- **Identification du tenant** : sous-domaine (`ecole-x.elitecampus.tn`) vs domaine personnalisé vs sélection manuelle au login. Le sous-domaine est le standard SaaS scolaire (lisible, gratuit en SSL wildcard).
- **Modèle de facturation** : par élève, par forfait fixe, par fonctionnalité — conditionne la structure des tables `plans`/`subscriptions`.

### Étape 1 — Fondations de données
1. Créer la table `schools` (nom, sous-domaine, statut actif/suspendu, plan, date de fin d'essai...).
2. Ajouter `school_id` (FK, `nullable: false`, `index`) sur **tous** les modèles métier : `Student`, `SchoolParent`, `Employee`, `Classroom`, `Level`, `Subject`, `Payment`, `Payroll`, `Expense`, `ExpenseCategory`, `Incident`, `Attendance`, `StudentAttendance`, `Holiday`, `BlogPost`, `Service`, `TimetableEntry`, `AuditLog`, `Notification`. Migration de données : l'école existante devient le tenant #1, toutes les lignes actuelles reçoivent `school_id = 1`.
3. Ajouter `school_id` sur `users` (un utilisateur appartient à une école — ou prévoir une table pivot `school_user` si un utilisateur doit pouvoir gérer plusieurs écoles, ex. un cabinet de gestion scolaire).
4. Convertir `SchoolSetting` (actuellement singleton `id=1`) en table normale `school_id`-scopée, une ligne par école.

### Étape 2 — Mécanique de scoping
5. Créer un trait `BelongsToSchool` + un `Global Scope` appliqué automatiquement à tous les modèles ci-dessus, basé sur le tenant courant résolu en requête (middleware).
6. Middleware de résolution du tenant (sous-domaine → `School` courante, injectée dans un singleton `app()->instance('currentSchool', ...)` ou équivalent).
7. Adapter les 3 `PanelProvider` (`Admin`, `Parent`, `Staff`) pour qu'ils résolvent le bon tenant selon le domaine d'entrée, et bloquer l'accès si l'utilisateur n'appartient pas à l'école visée.
8. Revoir `canAccessPanel()` dans `User.php` pour intégrer la vérification d'appartenance à l'école courante, pas seulement le rôle.
9. Auditer **tous** les `Filament\Widgets\*` et `*ListStatsWidget.php` (cf. travail i18n précédent — ce sont les mêmes fichiers) : leurs requêtes (`Student::count()`, etc.) doivent automatiquement bénéficier du Global Scope sans modification de code si l'étape 5 est bien faite — mais à vérifier un par un, en particulier les agrégations SQL brutes (`MainDashboardWidget::selectRaw`) qui pourraient contourner le scope.

### Étape 3 — Provisioning
10. Commande artisan `school:create` (ou formulaire super-admin) : crée la ligne `schools`, le compte admin initial, les `school_settings` par défaut, déclenche le seed minimal (niveaux scolaires, jours fériés tunisiens via `HolidayService` déjà existant).
11. Processus de suppression/suspension propre d'une école (soft-delete en cascade, ou anonymisation pour conformité RGPD si export hors Tunisie).

### Étape 4 — Panel Super-Admin (nouveau, distinct des panels existants)
12. Nouveau panel Filament `/platform` (ou similaire), réservé à l'équipe EliteCampus : liste des écoles clientes, statut d'abonnement, bascule actif/suspendu, vue d'usage (nb élèves, nb utilisateurs) par école.
13. Rôle `platform_admin` distinct de `admin` (qui reste "admin d'une école").

### Étape 5 — Facturation
14. Choisir et intégrer un fournisseur de paiement récurrent (Stripe via Laravel Cashier est le plus simple en Laravel 13).
15. Tables `plans` (limites : nb élèves max, fonctionnalités incluses) et `subscriptions` liées à `schools`.
16. Logique de limitation/avertissement quand une école dépasse son quota (ex. nb élèves), et écran de mise à niveau.
17. Page de facturation/historique des paiements pour l'admin de chaque école.

### Étape 6 — Personnalisation & isolation fichiers
18. Adapter le stockage (`storage/app/public/...`) pour préfixer par `school_id` (logos, exports PDF, pièces jointes) — sinon collision de noms de fichiers entre écoles.
19. Sous-domaine + thème (couleur, logo) déjà partiellement couvert par `SchoolSetting`, à re-brancher sur le tenant résolu plutôt que sur le singleton.

### Étape 7 — Sécurité & qualité
20. Policies explicites par modèle (item 13 du roadmap principal) **en plus** du scoping tenant — deux couches différentes : Policy = "cet utilisateur a-t-il le droit ?", Global Scope = "dans quelle école ?". Les deux sont nécessaires, l'un ne remplace pas l'autre.
21. Tests automatisés spécifiques multi-tenant : vérifier qu'une requête authentifiée sur l'école A ne peut jamais lire/modifier une ressource de l'école B (test de non-régression critique, à écrire avant la mise en prod multi-tenant).
22. Audit de toutes les requêtes brutes (`DB::table(...)`, `selectRaw`, jobs en queue, commandes artisan planifiées comme `payments:send-reminders`) pour confirmer qu'elles respectent bien le `school_id` — ces requêtes-là ne passent généralement pas par le Global Scope Eloquent et doivent être corrigées à la main.

### Étape 8 — Migration de l'école existante
23. Script de migration : l'unique école actuelle (données de prod réelles) devient le tenant #1 du nouveau système, sans perte de données, avec vérification post-migration (comptes des lignes par table avant/après).

---

## 3. Ce qui est déjà réutilisable tel quel

Pour limiter le travail, certaines briques actuelles sont **déjà prêtes** à devenir multi-tenant sans réécriture profonde, une fois `school_id` ajouté et le Global Scope branché :
- Toute la logique métier (paiements, paie, présences, incidents, bulletins PDF) — elle raisonne déjà "à l'intérieur d'une école", il suffit de garantir qu'elle ne voit que les bonnes lignes.
- L'i18n FR/EN/AR + RTL (terminée cette session) — indépendante du tenant, profite à toutes les écoles sans travail supplémentaire.
- `HolidayService` (jours fériés tunisiens) — réutilisable par toute école tunisienne sans changement.
- `DemoDataService` — base de départ pour écrire la commande `school:create` de l'étape 3 (la logique de génération de données cohérentes existe déjà, à adapter pour viser un tenant précis au lieu de la base entière).

---

## 4. Ordre recommandé

1. **Étape 0** (décisions) → bloque tout le reste, à valider avant de coder.
2. **Étape 1 + 2** (données + scoping) → cœur technique, le plus risqué, à faire en premier et à tester intensivement (étape 7.21) avant d'avancer.
3. **Étape 3 + 8** (provisioning + migration de l'école existante) → permet de valider le système avec un cas réel (l'école actuelle) avant d'onboarder un second client.
4. **Étape 4** (super-admin) → nécessaire dès le 2ᵉ client.
5. **Étape 5** (facturation) → peut être différée si les premières écoles sont onboardées manuellement/gratuitement en phase pilote.
6. **Étape 6** (fichiers/personnalisation) → en parallèle de l'étape 4, faible risque.
7. **Étape 7** (policies, audit sécurité) → en continu, mais doit être **bouclé avant** d'accepter une 2ᵉ école payante en production.

---

*Document généré le 2026-06-22. Items 12 (SaaS/tenancy) et 13 (Policies) du `IMPROVEMENTS_ROADMAP.md` correspondent à ce chantier, volontairement exclu des correctifs i18n précédents sur demande explicite.*
