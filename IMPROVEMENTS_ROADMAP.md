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

### P0 — Robustesse & confiance (rapide, fort impact)
1. **Tests automatisés (Pest).** Couverture actuelle quasi nulle (`ExampleTest`, `MvpAccessTest`).
   → Tests d'accès par rôle (admin/staff/parent), isolation des données, calcul de bulletin, paie CNSS/IRPP, seed/purge démo.
2. **Emails en file d'attente (Queue).** Les mails (bienvenue, rappels) partent en **synchrone** → ralentit la requête.
   → `ShouldQueue` sur les Mailables + `QUEUE_CONNECTION=database` + worker. Évite de bloquer l'UI.
3. **Statut paiement « verified ».** Ajouter à l'enum + workflow : *pending → paid (saisi) → verified (validé comptable)*. (seul vrai point du review)

### P1 — Rigueur financière & perf
4. **`PaymentService` / `PayrollService` centralisés** avec arrondi unique au millime (BCMath optionnel) — une seule source de vérité pour les calculs.
5. **Journal d'audit financier (Audit Log).** Tracer création/modif des paiements, paies, notes (`spatie/laravel-activitylog`). Anti-fraude + traçabilité.
6. **Agrégations SQL pures dans les widgets restants** (`distribution par niveau`, trends) — remplacer `->get()->groupBy()` par `groupBy` SQL.

### P2 — Fonctionnel & produit
7. **Export PDF natif** des bulletins et fiches de paie (`barryvdh/laravel-dompdf`) au lieu de `window.print()`.
8. **Notifications in-app** (cloche Filament) reliées aux events : nouvel impayé, incident, fiche de paie prête.
9. **Jours fériés tunisiens** (table + calcul hégirien, sans API payante) → exclure des présences et emplois du temps.
10. **Captures d'écran réelles** sur la landing (remplacer les placeholders).

### P3 — Évolutions long terme
11. **Internationalisation AR complète (RTL)** — l'arabe est dans le sélecteur mais les pages portail sont en FR inline.
12. **Mode multi-établissement (SaaS / tenancy)** si ouverture à plusieurs écoles.
13. **Policies explicites par modèle** (en préparation d'un futur multi-rôles plus fin que mono-admin).

---

## 📌 Ordre recommandé
**P0 (1 → 3)** d'abord : ils sécurisent la prod (tests, perf emails, workflow paiement).
Puis **P1** (rigueur compta + audit), puis **P2** (PDF, notifs, jours fériés), enfin **P3** (i18n/SaaS) selon le besoin business.

> Note : ce sont des **améliorations de durcissement**, pas des correctifs de bugs bloquants. L'ERP est fonctionnel et les « urgences P0 » du review original n'existent pas (déjà corrigées).
