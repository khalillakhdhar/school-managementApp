# PERMISSIONS AUDIT — EliteCampus ERP
**Date:** 2026-06-14  
**Framework:** Laravel 13 + Filament v5 + PHP 8.4  
**Authorization Model:** Single-role Admin — all authenticated users have full access

---

## Root Cause of 403 Errors

### Problem 1: `Gate::before` insufficient for Filament v5

In Filament v5, resource authorization does NOT go directly through `Gate::before` in all code paths. The internal `get_authorization_response()` helper (`vendor/filament/filament/src/helpers.php`) bypasses the Gate when no policy exists by calling `Gate::callBeforeCallbacks()` via the `invade()` package — but this only works correctly when the Gate's before-callbacks return a `Response` object, not a raw `true` boolean.

### Problem 2: Wildcard CSS selector causing uppercase nav items

The CSS rule `.fi-sidebar [class*="fi-sidebar-group"] span[class*="label"]` with `text-transform: uppercase` unintentionally matched `fi-sidebar-item-label` (nav item labels), making ALL navigation text uppercase.

### Problem 3: Wrong active-state CSS selector

Previous CSS targeted `.fi-sidebar [aria-current="page"]` and `[class*="fi-sidebar-item-active"]`. In Filament v5, the active class is `fi-active` on the `<li class="fi-sidebar-item fi-active">` element, not `aria-current="page"`.

---

## Fix Applied

### Primary Fix: `Resource::skipAuthorization()`

**File:** `app/Providers/AppServiceProvider.php`

```php
use Filament\Resources\Resource;

public function boot(): void
{
    // Filament v5 API — bypasses get_authorization_response() entirely
    Resource::skipAuthorization();

    // Secondary gate bypass
    Gate::before(fn ($user) => $user ? true : null);
}
```

**How it works:**  
In `HasAuthorization::getAuthorizationResponse()`:
```php
if (static::shouldSkipAuthorization()) {
    return Response::allow(); // immediate allow, no Gate check
}
```
This short-circuits ALL resource authorization checks at the Filament level.

---

## Resource Authorization Audit

| Resource | Model | Route Pattern | Previous Issue | Fix Applied |
|---|---|---|---|---|
| AttendanceResource | Attendance | `resources/attendances.*` | Gate check failed | `Resource::skipAuthorization()` |
| BlogPostResource | BlogPost | `resources/blog-posts.*` | Gate check failed | `Resource::skipAuthorization()` |
| ClassroomResource | Classroom | `resources/classrooms.*` | Gate check failed | `Resource::skipAuthorization()` |
| EmployeeResource | Employee | `resources/employees.*` | Gate check failed | `Resource::skipAuthorization()` |
| ExpenseCategoryResource | ExpenseCategory | `resources/expense-categories.*` | Gate check failed | `Resource::skipAuthorization()` |
| ExpenseResource | Expense | `resources/expenses.*` | Gate check failed | `Resource::skipAuthorization()` |
| IncidentResource | Incident | `resources/incidents.*` | Gate check failed | `Resource::skipAuthorization()` |
| LevelResource | Level | `resources/levels.*` | Gate check failed | `Resource::skipAuthorization()` |
| ParentResource | SchoolParent | `resources/parents.*` | Gate check failed | `Resource::skipAuthorization()` |
| PaymentResource | Payment | `resources/payments.*` | Gate check failed | `Resource::skipAuthorization()` |
| PayrollResource | Payroll | `resources/payrolls.*` | Gate check failed | `Resource::skipAuthorization()` |
| ServiceResource | Service | `resources/services.*` | Gate check failed | `Resource::skipAuthorization()` |
| StudentResource | Student | `resources/students.*` | Gate check failed | `Resource::skipAuthorization()` |
| SubjectResource | Subject | `resources/subjects.*` | Gate check failed | `Resource::skipAuthorization()` |
| TimetableEntryResource | TimetableEntry | `resources/timetable-entries.*` | Gate check failed | `Resource::skipAuthorization()` |

---

## Custom Page Authorization Audit

| Page | canAccess() | Status |
|---|---|---|
| ClassTimetable | Inherited from `CanAuthorizeAccess` trait → `return true` | ✅ No issue |
| FinancialReport | Inherited from `CanAuthorizeAccess` trait → `return true` | ✅ No issue |
| SchoolSettings | Inherited from `CanAuthorizeAccess` trait → `return true` | ✅ No issue |
| TeacherSchedule | Inherited from `CanAuthorizeAccess` trait → `return true` | ✅ No issue |
| Dashboard | `Filament\Pages\Dashboard` → always accessible | ✅ No issue |

**Note:** In Filament v5, `Page::canAccess()` defaults to `return true` via the `CanAuthorizeAccess` trait. Custom pages are not affected by `Resource::skipAuthorization()`.

---

## Widget Authorization Audit

| Widget | canView() | Status |
|---|---|---|
| MainDashboardWidget | Not declared → always visible | ✅ |
| DashboardHeaderWidget | Not declared → always visible | ✅ |
| StudentsListStatsWidget | `routeIs('*.students.index')` | ✅ Route-gated, not auth-gated |
| EmployeesListStatsWidget | `routeIs('*.employees.index')` | ✅ Route-gated |
| ClassroomsListStatsWidget | `routeIs('*.classrooms.index')` | ✅ Route-gated |
| PaymentsListStatsWidget | `routeIs('*.payments.index')` | ✅ Route-gated |
| ExpensesListStatsWidget | `routeIs('*.expenses.index')` | ✅ Route-gated |
| IncidentsListStatsWidget | `routeIs('*.incidents.index')` | ✅ Route-gated |
| SmartAlertsWidget | Not declared → always visible | ✅ |
| SchoolStructureWidget | `return true` | ✅ |
| FinancialOverviewWidget | `return false` — intentionally hidden | ✅ Disabled |
| StudentsOverviewWidget | `return false` — intentionally hidden | ✅ Disabled |

---

## Parent Portal (Separate Panel)

`app/Filament/Parent/Pages/ParentDashboard.php` contains:
```php
abort(403, 'Aucun profil parent associé à ce compte.');
```
This is **intentional** — users without a parent profile cannot access the parent portal. This is correct behavior and should NOT be removed.

---

## Security Notes

- `Resource::skipAuthorization()` sets `static::$shouldSkipAuthorization = true` on the base `Filament\Resources\Resource` class. Since PHP static properties are shared across all subclasses that don't redeclare the property, this effectively bypasses authorization for ALL 15 resources in a single call.
- No model policies exist in `app/Policies/` — the application relies entirely on Filament's panel authentication (session-based) as the authorization boundary.
- The `Authenticate` middleware in `->authMiddleware([Authenticate::class])` ensures all panel routes require a valid session.
- This architecture is appropriate for a single-administrator school ERP where all users are trusted staff.
