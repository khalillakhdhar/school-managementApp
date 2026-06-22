# 🔧 Roadmap d'amélioration — EliteCampus

> Établi le 2026-06-16 après **vérification du `full_school_review.md` contre le code réel**.
> ⚠️ La plupart des points « critiques » du review sont **déjà résolus ou inexacts** (voir tableau de vérité ci-dessous).
> Ce roadmap ne garde que les améliorations **réellement utiles** + des manques non détectés par le review.

---

## ✅ Tableau de vérité du `full_school_review.md`

| Affirmation du review | Verdict | Preuve dans le code |
|---|---|---|
| N+1 dans `EmployeeResource` (heures impayées) | ❌ **FAUX** | `getEloquentQuery()` utilise déjà `withSum('payrolls as unpaid_hours_sum'/'unpaid_amount_sum')` ; les colonnes lisent l'attribut pré-chargé, **0 requête par ligne**. |
| Redondance `withCount` + `counts()` dans `ExpenseCategoryResource` | ❌ **FAUX** | `getEloquentQuery()->withCount('expenses')` + colonne `expenses_count` (affichage). Pattern correct, pas de doublon. |
| Filtrage des dettes sur `payment_date` | ❌ **FAUX** | Tous les calculs de retard utilisent `due_date` (`MainDashboardWidget`, `FinancialReport` aging, `PaymentsListStatsWidget`). |
| Dashboard vide / `AccountWidget` | ❌ **FAUX / obsolète** | Dashboard riche : `MainDashboardWidget` (KPIs + 2 charts Chart.js), `DashboardHeaderWidget`, `SmartAlertsWidget`… `AccountWidget` n'est pas chargé. |
| UX trop d'espace blanc / « ressemble à un blog » | ❌ **OBSOLÈTE** | Refonte design haute densité déjà faite (sidebar navy, cards, tables denses, thème partagé). |
| `float` pour CNSS/IRPP → erreurs de centimes | ⚠️ **PARTIEL/mineur** | Colonnes en `decimal(10,3)`, calc PHP en float **arrondi au millime**. OK pour une école ; rigueur améliorable via service centralisé. |
| Montants à migrer en `Decimal(12,3)` | ⚠️ **MINEUR** | Actuel `decimal(10,3)` = max 9 999 999,999. Suffisant pour une école ; `12,3` utile seulement pour très gros volumes. |
| Pas de statut paiement « Verified » | ✅ **VRAI** | enum = `paid/pending/failed/cancelled`. Pas de séparation saisie/validation. |
| Policies / IDOR sur bulk actions | ⚠️ **PARTIEL** | Mono-admin via `skipAuthorization` + `canAccessPanel` ; portails isolés & écritures validées côté serveur. Faible risque en mono-école. |
| Widgets chargeant des modèles en mémoire | ⚠️ **PARTIEL** | Quelques agrégats chargent des collections (`distribution`, trends). Améliorable en SQL pur. |

**Conclusion :** le review est **peu précis (≈ 60% inexact ou obsolète)** — il semble basé sur une version antérieure/générique. Le score 52/100 (et surtout UX 30, Finance 38) ne reflète pas l'état actuel. Les seuls points valides sont **mineurs ou prospectifs**.

---

## 🎯 Améliorations réellement utiles (priorisées)

### ✅ P0 — Robustesse & confiance (TERMINÉ)
1. ✅ **Tests automatisés (PHPUnit).** Suite `tests/Feature/ErpCoreTest.php` : accès par rôle (admin/teacher/parent + 403), 1ère connexion forcée, bulletin (moyenne pondérée + rang + mention), validation paiement + audit, CNSS. **11/11 verts** (sqlite mémoire, prod intacte). *(PHPUnit déjà présent — Pest inutile.)*
2. ✅ **Emails en file d'attente.** Les 4 Mailables implémentent `ShouldQueue` ; `QUEUE_CONNECTION=database` + table `jobs` déjà là. → lancer `php artisan queue:work`.
3. ✅ **Validation de paiement.** Flag `is_verified` + `verified_at`/`verified_by` (migration `2026_06_16_000001`), actions « Valider (comptable) » / « Annuler la validation », colonne + filtre. *(flag plutôt qu'enum → aucune requête de revenu impactée.)*

### ✅ P1 — Rigueur financière & perf (TERMINÉ)
4. ✅ **Services centralisés.** `PaymentService` (existant) étendu : `money()`, `markPaid()`, `verify()`, `unverify()` ; le `PaymentResource` les appelle (DRY). `PayrollService` déjà en place.
5. ✅ **Journal d'audit (maison, sans dépendance).** Table `audit_logs` + modèle + trait `Auditable` (toggle `App\Support\Audit`) sur `Payment`, `Payroll`, `Grade` ; resource admin lecture seule (`/admin/audit-logs`). Seed démo silencieux.
6. ✅ **Agrégations SQL.** `MainDashboardWidget` : répartition par niveau passée en `leftJoin + groupBy` SQL (plus de chargement de collection en mémoire).

### P2 — Fonctionnel & produit
7. ✅ **Export PDF natif** (`barryvdh/laravel-dompdf`). `DocumentPdfController` + vues `pdf.bulletin`/`pdf.payslip` (layout table dompdf-safe) ; routes `pdf.bulletin`/`pdf.payslip` sécurisées par rôle (admin/parent-own-child, admin/staff-own-payslip → 403 sinon) ; boutons « Télécharger PDF » câblés (admin Bulletins + PayrollResource, parent, staff Mes fiches). Vérifié : PDF réels + 403.
8. ✅ **Notifications in-app** (cloche Filament). `->databaseNotifications()` sur panels Admin + Staff (+ table `notifications`). Observers : `IncidentObserver` (nouvel incident → admins), `PayrollObserver` (fiche finalisée/payée → l'employé). Commande `payments:send-reminders` → digest impayés aux admins. **Envoi via `notifyNow()`** (la `DatabaseNotification` de Filament est `ShouldQueue` → `sendToDatabase` exigerait un worker ; `notifyNow` persiste immédiatement). Skippé pendant le seed (toggle `Audit`). Vérifié par 2 tests PHPUnit (13/13).
9. ✅ **Jours fériés tunisiens** (sans API). `HolidayService` : jours civils grégoriens fixes + fêtes religieuses converties du calendrier **hégirien Umm al-Qura** via `intl`. Réconcilié avec la table existante (`holidays`, enum `national/religieux/scolaire`, `unique(date)` → fusion des noms en cas de collision ex. Indépendance + Aïd). Resource admin (Paramètres → Jours fériés) + action « Synchroniser une année » ; bannière jour férié dans *Faire l'appel* ; sync intégrée au Mode Démo.
10. ✅ **Aperçus de la landing** — placeholders vides remplacés par **3 maquettes HTML/CSS réalistes** en fenêtre navigateur (Admin : sidebar + KPI + bar chart ; Enseignant : faire l'appel ; Parent : dashboard). Légende « Maquettes illustratives ». *(De vraies captures PNG pourront les remplacer plus tard — impossible à générer côté serveur.)*

### P3 — Évolutions long terme
11. ✅ **Internationalisation AR/EN complète (RTL)**. Audit exhaustif : **684 chaînes FR→EN** (`lang/en.json`) et **984 entrées AR** (`lang/ar.json`). Tout le texte codé en dur dans `app/Filament` (labels, placeholders, `Section::make()`, `getNavigationLabel/Group`, `getModelLabel`, notifications `->title()/->body()`) enveloppé dans `__()`. Les 13 vues Blade portail (staff + parent) entièrement traduites (dashboards, bulletin, emploi du temps, paie, présences). **Correctif post-retour utilisateur** : le contenu *intérieur* des pages (pas seulement la navigation) restait en français quel que soit le locale — corrigé pour les 4 widgets du dashboard admin (`dashboard-header-widget`, `main-dashboard-widget`, `smart-alerts-widget` + son générateur PHP `SmartAlertsWidget.php`, `academic-stats-widget`) et les 6 pages admin custom (`class-timetable`, `teacher-schedule` + leurs classes PHP, `financial-report` — page la plus volumineuse, 762 lignes, intégralement traduite — `demo-mode`, `report-cards`, `school-settings`). Jours de la semaine (`Lundi`→`Monday`/`الإثنين`) traduits à l'affichage sans toucher aux valeurs FR stockées en base. `now()->locale('fr')` corrigé en `now()->locale(app()->getLocale())` partout (y compris `financial-report.blade.php`). **RTL** : propriétés CSS directionnelles converties en propriétés logiques (`-inline-start/end`, `inset-inline-start`) ; `->alignRight()` Filament → `->alignEnd()`. Le switch `dir="rtl"` et le sélecteur de langue fonctionnaient déjà correctement. Scan final automatisé (regex sur tout `resources/views/filament/{pages,widgets}`) confirme l'absence de texte français résiduel non traduit. **13/13 tests verts**, vues Blade compilées sans erreur.
12. **Mode multi-établissement (SaaS / tenancy)** si ouverture à plusieurs écoles.
13. **Policies explicites par modèle** (en préparation d'un futur multi-rôles plus fin que mono-admin).

---

## 📌 Ordre recommandé
**P0 (1 → 3)** d'abord : ils sécurisent la prod (tests, perf emails, workflow paiement).
Puis **P1** (rigueur compta + audit), puis **P2** (PDF, notifs, jours fériés), enfin **P3** (i18n/SaaS) selon le besoin business.

> Note : ce sont des **améliorations de durcissement**, pas des correctifs de bugs bloquants. L'ERP est fonctionnel et les « urgences P0 » du review original n'existent pas (déjà corrigées).
