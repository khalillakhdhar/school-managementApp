# Policies Laravel — État actuel & plan d'implémentation

> ✅ **Implémenté le 2026-06-27.** Les 12 Policies manquantes décrites ci-dessous ont été créées et enregistrées. Les 18 modèles métier ont désormais une Policy explicite (`Gate::getPolicyFor()` vérifié pour chacun). 6 tests PHPUnit ajoutés (`tests/Feature/PolicyTest.php`), suite complète 19/19 verte. Ce document reste la référence de design ; la section 5 ci-dessous décrit ce qui a été exécuté.
>
> Ce document corrige `PERMISSIONS_AUDIT.md` qui indiquait à tort *"No model policies exist"*. Des Policies existent déjà pour 6 modèles. Ce document cartographie l'état réel et planifie les 12 Policies manquantes.

---

## 1. État actuel réel

### Architecture en place

```
app/Policies/
├── Concerns/
│   └── ChecksSchoolAccess.php   ← trait partagé (helpers auth)
├── StudentPolicy.php            ✅ existant
├── PaymentPolicy.php            ✅ existant
├── PayrollPolicy.php            ✅ existant
├── GradePolicy.php              ✅ existant
├── IncidentPolicy.php           ✅ existant
└── EmployeePolicy.php           ✅ existant
```

**Trait `ChecksSchoolAccess`** — 3 helpers réutilisables dans toutes les Policies :
- `isAdmin(User)` — vérifie `role === 'admin'`
- `parentOwnsStudent(User, Student)` — vérifie la relation parent↔enfant via `school_parents.user_id`
- `teacherHandlesStudent(User, Student)` — vérifie que l'employé intervient dans la classe de l'élève via `timetable_entries`
- `teacherHandlesClassSubject(User, classroomId, subjectId?)` — vérifie classe + optionnellement matière

**Enregistrement** dans `AppServiceProvider::boot()` :
```php
Gate::policy(Student::class,  StudentPolicy::class);
Gate::policy(Payment::class,  PaymentPolicy::class);
Gate::policy(Payroll::class,  PayrollPolicy::class);
Gate::policy(Grade::class,    GradePolicy::class);
Gate::policy(Incident::class, IncidentPolicy::class);
Gate::policy(Employee::class, EmployeePolicy::class);
Gate::before(fn ($user) => $user?->role === 'admin' ? true : null);
```

### Où les Policies s'appliquent actuellement

| Couche | Enforcement | Détail |
|---|---|---|
| **API REST** (`/api/...`) | ✅ Actif | `Gate::authorize()` dans `StudentController`, `PaymentController`, `PayrollController` |
| **Filament admin** (`/admin`) | ❌ Désactivé | `Resource::skipAuthorization()` dans `AppServiceProvider` ligne 31 |
| **Portails parent/staff** | ✅ Partiel | `abort(403)` codés en dur dans les pages (ex: `ParentDashboard`, `GradeEntry`, `StudentAttendanceEntry`) |

> **En résumé** : les Policies PHP existent et sont correctes, mais l'UI Filament les contourne intentionnellement (ERP mono-admin de confiance). L'isolation parent↔enfants et prof↔classes est garantie par des contrôles `abort(403)` directs dans les pages portail, pas par les Policies.

---

## 2. Couverture complète — 18 modèles

| Modèle | Policy | Enforced | Qui peut accéder (hors admin) |
|---|---|---|---|
| `Student` | ✅ `StudentPolicy` | API | parent (ses enfants), teacher (ses classes) |
| `Payment` | ✅ `PaymentPolicy` | API | parent (paiements de ses enfants) |
| `Payroll` | ✅ `PayrollPolicy` | API | employee (sa propre fiche) |
| `Grade` | ✅ `GradePolicy` | API | teacher (ses matières), parent (notes de ses enfants) |
| `Incident` | ✅ `IncidentPolicy` | API | parent (incidents de ses enfants) |
| `Employee` | ✅ `EmployeePolicy` | API | employee (son propre profil) |
| `Classroom` | ❌ manquant | — | teacher (ses classes) |
| `Level` | ❌ manquant | — | admin only |
| `Subject` | ❌ manquant | — | teacher (ses matières) |
| `Attendance` (employé) | ❌ manquant | — | employee (sa propre présence) |
| `StudentAttendance` | ❌ manquant | — | teacher (ses classes), parent (ses enfants) |
| `BlogPost` | ❌ manquant | — | tous (portail public) |
| `Holiday` | ❌ manquant | — | tous en lecture, admin en écriture |
| `Service` | ❌ manquant | — | admin only |
| `Expense` | ❌ manquant | — | admin only |
| `ExpenseCategory` | ❌ manquant | — | admin only |
| `AuditLog` | ❌ manquant | — | admin only (lecture seule, jamais d'écriture) |
| `TimetableEntry` | ❌ manquant | — | teacher (ses créneaux) |

---

## 3. Les 12 Policies à créer

Toutes suivent le même patron que les 6 existantes. Le `Gate::before()` admin bypass s'applique globalement — aucune Policy n'a besoin de vérifier `isAdmin()` explicitement (sauf via la méthode `before()` de chaque Policy pour la clarté).

### 3.1 Policies "admin only" (4 simples)

Ces 4 modèles ne sont accessibles que par l'admin — les non-admins doivent toujours retourner `false`.

```php
// Level, Service, Expense, ExpenseCategory — même structure :
class LevelPolicy {
    use ChecksSchoolAccess;
    public function before(User $user): ?bool  { return $this->isAdmin($user) ? true : null; }
    public function viewAny(User $user): bool  { return false; }
    public function view(User $user, $model): bool   { return false; }
    public function create(User $user): bool   { return false; }
    public function update(User $user, $model): bool { return false; }
    public function delete(User $user, $model): bool { return false; }
}
// Idem pour ServicePolicy, ExpensePolicy, ExpenseCategoryPolicy
```

### 3.2 `AuditLogPolicy` — lecture seule, admin uniquement

```php
class AuditLogPolicy {
    use ChecksSchoolAccess;
    public function before(User $user): ?bool  { return $this->isAdmin($user) ? true : null; }
    public function viewAny(User $user): bool  { return false; }
    public function view(User $user, $model): bool   { return false; }
    public function create(User $user): bool   { return false; } // jamais
    public function update(User $user, $model): bool { return false; } // jamais
    public function delete(User $user, $model): bool { return false; } // jamais
}
```

### 3.3 `BlogPostPolicy` + `HolidayPolicy` — lecture publique, écriture admin

```php
class BlogPostPolicy {
    use ChecksSchoolAccess;
    public function before(User $user): ?bool  { return $this->isAdmin($user) ? true : null; }
    // Tous les rôles peuvent voir (portail parent/staff lit les annonces)
    public function viewAny(User $user): bool  { return true; }
    public function view(User $user, $model): bool   { return true; }
    // Écriture : admin only (géré par before())
    public function create(User $user): bool   { return false; }
    public function update(User $user, $model): bool { return false; }
    public function delete(User $user, $model): bool { return false; }
}
// HolidayPolicy : même logique (les portails peuvent lire les jours fériés)
```

### 3.4 `AttendancePolicy` (présence employé)

```php
class AttendancePolicy {
    use ChecksSchoolAccess;
    public function before(User $user): ?bool { return $this->isAdmin($user) ? true : null; }
    // Un employé peut voir et créer/modifier SA PROPRE présence
    public function viewAny(User $user): bool { return in_array($user->role, ['teacher', 'employee']); }
    public function view(User $user, Attendance $att): bool {
        return $user->employee?->id === $att->employee_id;
    }
    public function create(User $user): bool  { return in_array($user->role, ['teacher', 'employee']); }
    public function update(User $user, Attendance $att): bool {
        return $user->employee?->id === $att->employee_id;
    }
    public function delete(User $user, Attendance $att): bool { return false; }
}
```

### 3.5 `StudentAttendancePolicy`

```php
class StudentAttendancePolicy {
    use ChecksSchoolAccess;
    public function before(User $user): ?bool { return $this->isAdmin($user) ? true : null; }
    // Enseignant : voit et crée les appels de SES classes
    // Parent : voit les présences de SES enfants
    public function viewAny(User $user): bool {
        return in_array($user->role, ['teacher', 'employee', 'parent']);
    }
    public function view(User $user, StudentAttendance $att): bool {
        if ($user->role === 'parent') {
            return $att->student ? $this->parentOwnsStudent($user, $att->student) : false;
        }
        return $this->teacherHandlesClassSubject($user, $att->classroom_id);
    }
    public function create(User $user): bool {
        return in_array($user->role, ['teacher', 'employee']);
    }
    public function update(User $user, StudentAttendance $att): bool {
        return $this->teacherHandlesClassSubject($user, $att->classroom_id);
    }
    public function delete(User $user, StudentAttendance $att): bool { return false; }
}
```

### 3.6 `ClassroomPolicy`

```php
class ClassroomPolicy {
    use ChecksSchoolAccess;
    public function before(User $user): ?bool { return $this->isAdmin($user) ? true : null; }
    // Enseignant : voit ses propres classes (via timetable_entries)
    public function viewAny(User $user): bool {
        return in_array($user->role, ['teacher', 'employee']);
    }
    public function view(User $user, Classroom $classroom): bool {
        return $this->teacherHandlesClassSubject($user, $classroom->id);
    }
    public function create(User $user): bool   { return false; }
    public function update(User $user, Classroom $classroom): bool { return false; }
    public function delete(User $user, Classroom $classroom): bool { return false; }
}
```

### 3.7 `SubjectPolicy`

```php
class SubjectPolicy {
    use ChecksSchoolAccess;
    public function before(User $user): ?bool { return $this->isAdmin($user) ? true : null; }
    // Enseignant : voit les matières qu'il enseigne
    public function viewAny(User $user): bool {
        return in_array($user->role, ['teacher', 'employee']);
    }
    public function view(User $user, Subject $subject): bool {
        $employee = $user->employee;
        return $employee
            ? TimetableEntry::where('employee_id', $employee->id)
                ->where('subject_id', $subject->id)->exists()
            : false;
    }
    public function create(User $user): bool   { return false; }
    public function update(User $user, $model): bool { return false; }
    public function delete(User $user, $model): bool { return false; }
}
```

### 3.8 `TimetableEntryPolicy`

```php
class TimetableEntryPolicy {
    use ChecksSchoolAccess;
    public function before(User $user): ?bool { return $this->isAdmin($user) ? true : null; }
    public function viewAny(User $user): bool {
        return in_array($user->role, ['teacher', 'employee']);
    }
    public function view(User $user, TimetableEntry $entry): bool {
        return $this->teacherHandlesClassSubject($user, $entry->classroom_id, $entry->subject_id);
    }
    public function create(User $user): bool   { return false; }
    public function update(User $user, $model): bool { return false; }
    public function delete(User $user, $model): bool { return false; }
}
```

---

## 4. Décision clé : `skipAuthorization()` dans Filament

### Option A — Garder `skipAuthorization()` (recommandé, statu quo)
- Panel `/admin` reste mono-rôle avec un seul admin de confiance
- Les Policies s'appliquent uniquement à la couche API (déjà le cas)
- Zéro risque de régression dans l'UI Filament
- **À choisir si** : l'app restera avec un seul rôle admin

### Option B — Retirer `skipAuthorization()` + activer les Policies dans Filament
- Chaque Resource Filament respecte automatiquement les Policies (`viewAny` → accès à la liste, `view` → voir un enregistrement, etc.)
- Risque : si une Policy manque ou retourne `false`, l'accès est bloqué dans l'UI → régression
- **Préalable absolu** : toutes les 18 Policies doivent exister et être testées AVANT de retirer `skipAuthorization()`
- **À choisir uniquement si** : un 2ᵉ rôle admin (comptable, direction) est prévu, ou avec l'item 12 (SaaS multi-rôles)

**Recommandation actuelle : Option A.** Ne retirer `skipAuthorization()` qu'en même temps que l'item 12 (SaaS).

---

## 5. Ordre d'implémentation (quand on codera)

```
1. artisan make:policy LevelPolicy            → admin only
   artisan make:policy ServicePolicy          → admin only
   artisan make:policy ExpensePolicy          → admin only
   artisan make:policy ExpenseCategoryPolicy  → admin only
   artisan make:policy AuditLogPolicy         → admin only, read-only
   artisan make:policy BlogPostPolicy         → viewAny/view = true, écriture admin
   artisan make:policy HolidayPolicy          → viewAny/view = true, écriture admin
   artisan make:policy AttendancePolicy       → employee (soi)
   artisan make:policy StudentAttendancePolicy → teacher (ses classes) + parent (ses enfants)
   artisan make:policy ClassroomPolicy        → teacher (ses classes)
   artisan make:policy SubjectPolicy          → teacher (ses matières)
   artisan make:policy TimetableEntryPolicy   → teacher (ses créneaux)

2. Ajouter 12 Gate::policy() dans AppServiceProvider::boot()

3. Tests PHPUnit pour chaque Policy :
   - Gate::allows('viewAny', Model::class) par rôle
   - Gate::denies('create', Model::class) pour les non-admins
   - Gate::allows('view', $model) pour parent/teacher sur ses données

4. NE PAS retirer Resource::skipAuthorization() à cette étape

5. Mettre à jour PERMISSIONS_AUDIT.md avec l'état corrigé
```

---

## 6. Correction à apporter à `PERMISSIONS_AUDIT.md`

La phrase actuelle est inexacte :
> *"No model policies exist in `app/Policies/`"*

À remplacer par :
> *"6 Policies explicites existent (`Student`, `Payment`, `Payroll`, `Grade`, `Incident`, `Employee`) dans `app/Policies/`, toutes héritant du trait `ChecksSchoolAccess`. Elles sont enforced sur la couche API REST (`Gate::authorize()`). L'UI Filament admin bypasse ces Policies via `Resource::skipAuthorization()` (choix délibéré, ERP mono-admin). 12 Policies restent à créer pour les modèles restants (voir `POLICIES_ROADMAP.md`)."*

---

*Document généré le 2026-06-27. Correspond à l'item 13 de `IMPROVEMENTS_ROADMAP.md` (Policies explicites par modèle). À lire en complément de `PERMISSIONS_AUDIT.md` et `SAAS_ROADMAP.md`.*
