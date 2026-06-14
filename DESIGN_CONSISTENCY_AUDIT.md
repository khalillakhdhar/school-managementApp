# EliteCampus ERP — Design Consistency Audit
**Date:** 2026-06-14

---

## 1. NAVIGATION

| Groupe | Ressources | Cohérence |
|---|---|---|
| Académique | Students, Classrooms, Subjects, Timetable, ClassTimetable, TeacherSchedule | ✅ Toutes groupées |
| RH | Employees, Attendance, Payroll | ✅ Toutes groupées |
| Finances | Payments, Expenses, FinancialReport | ✅ Toutes groupées |
| Communication | Incidents, Blog, Parents | ✅ Toutes groupées |
| Paramètres | SchoolSettings, Levels, Services, ExpenseCategories | ✅ Toutes groupées |

**Règle respectée :** Toutes les ressources ont un `getNavigationGroup()`.

---

## 2. FORMULAIRES

| Pattern | Application | Statut |
|---|---|---|
| `Section::make()->icon()->description()` | Tous les formulaires principaux | ✅ Uniforme |
| `->columns(2)` sur les sections | Toutes les sections à 2+ champs | ✅ Uniforme |
| Labels en français | Tous les champs | ✅ Uniforme |
| Placeholder descriptifs | Champs texte | ✅ Présents |
| `->required()` + validation | Champs obligatoires | ✅ Présents |

---

## 3. TABLEAUX

| Pattern | Application | Statut |
|---|---|---|
| `.weight(FontWeight::SemiBold)` sur colonne principale | Toutes les listes | ✅ Uniforme |
| `.badge()` pour statuts | Statuts, types, niveaux | ✅ Uniforme |
| `.money('TND')` pour montants | Paiements, paie, dépenses | ✅ Uniforme |
| `emptyStateHeading` + `emptyStateDescription` | Toutes les ressources | ✅ Présents |
| `.defaultSort()` cohérent | Dates desc, noms asc | ✅ Cohérent |
| Actions : Edit + Delete | Toutes les tables | ✅ Uniforme |

---

## 4. CODES COULEUR BADGE

| Signification | Couleur | Cohérence |
|---|---|---|
| Succès / Actif / Payé | `success` (vert) | ✅ Uniforme |
| Attention / En cours | `warning` (orange) | ✅ Uniforme |
| Erreur / Retard / Grave | `danger` (rouge) | ✅ Uniforme |
| Information / Référence | `primary` (bleu) | ✅ Uniforme |
| Neutre / Brouillon | `gray` | ✅ Uniforme |
| Académique | `info` (cyan) | ✅ Uniforme |

---

## 5. PAGES CUSTOM (Livewire)

| Page | Pattern | Cohérence |
|---|---|---|
| ClassTimetable | État null=liste / non-null=grille | ✅ |
| TeacherSchedule | Même pattern | ✅ |
| SchoolSettings | Form sections + save bar | ✅ |
| FinancialReport | KPI cards + graphiques | ✅ |
| ParentDashboard | Vue portail readonly | ✅ |

**Règle CSS :** Toutes utilisent inline styles (Tailwind JIT ne scanne pas les nouveaux fichiers blade à chaud).

---

## 6. INCONSISTANCES IDENTIFIÉES

| Problème | Fichier | Impact |
|---|---|---|
| Labels partiellement en anglais (`__('Employees')`) | EmployeeResource, PayrollResource | Faible — Filament utilise `__()` pour i18n |
| `->visible(fn () => true)` inutile | EmployeeResource.php ligne 194 | Cosmétique, pas fonctionnel |
| `capacity` affiché sans relation eager dans table | ClassroomResource table | Faible — déclenche N+1 sur `students_count` vs `capacity` display |

---

## 7. RECOMMANDATIONS DESIGN FUTURES

1. **Page d'accueil** : Ajouter une onboarding checklist pour les nouvelles installations (voir BUSINESS_DEPENDENCY_MATRIX.md)
2. **Empty states** : Ajouter des liens vers les prérequis (ex: "Créez d'abord un niveau" dans Classrooms)
3. **Breadcrumbs** : Filament les génère automatiquement — vérifier sur mobile
4. **Responsive** : Toutes les tables avec `->toggleable(isToggledHiddenByDefault: true)` sur les colonnes secondaires — bien utilisé
