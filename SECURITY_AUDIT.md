# 🔒 Audit de sécurité — Isolation des données par rôle

**Date :** 2026-06-15
**Portée :** Phase 5.3 — vérifier qu'aucun rôle ne peut accéder/écrire les données d'un autre.

---

## 1. Accès aux panels (authentification)

`User::canAccessPanel()` (implémente `FilamentUser`) verrouille chaque panel par rôle :

| Panel | Rôles autorisés | Vérifié |
|---|---|---|
| `/admin` | admin | ✅ enseignant → 403 |
| `/staff` | teacher, employee (+ admin) | ✅ |
| `/parent` | parent (+ admin) | ✅ |

---

## 2. Isolation en LECTURE

### Portail Parent — ne voit QUE ses enfants
Toutes les pages résolvent les enfants via `auth()->user()->parent->students()` :
| Page | Scoping | Test |
|---|---|---|
| Dashboard | `$parent->students()` | ✅ 1 enfant (pas 48) |
| Paiements | `$parent->students()` | ✅ |
| Emploi du temps | `array_key_exists($studentId, childrenOptions())` | ✅ ID étranger 99999 → bloqué |
| Suivi | `$parent->students()` | ✅ |
| Bulletins | `array_key_exists($studentId, children)` | ✅ ID étranger → `report=null` |

### Portail Enseignant — ne voit QUE ses classes
Toutes les pages résolvent l'employé via `auth()->user()->employee` et filtrent par
`employee_id` / classes de l'emploi du temps. ✅

---

## 3. Isolation en ÉCRITURE — 2 failles trouvées et corrigées

> ⚠️ Les pages Livewire « Faire l'appel » et « Saisie des notes » recevaient
> `classroomId` / `subjectId` depuis le client. Sans contrôle serveur, un enseignant
> aurait pu forger une requête pour écrire des présences/notes dans une classe ou une
> matière qu'il **n'enseigne pas**.

### Faille A — `StudentAttendanceEntry::save()` (présences)
**Correctif :**
- Refuse si `classroomId` ∉ `myClasses()` du prof.
- N'écrit que pour les élèves **réellement inscrits** dans cette classe (les IDs injectés côté client sont ignorés).
- Valide le statut (`present|absent|late|excused`).

**Test :** classe étrangère (99999) → **0 ligne écrite** ✅ ; saisie légitime → 6 lignes ✅

### Faille B — `GradeEntry::save()` (notes)
**Correctif :**
- Refuse si `classroomId` ∉ `myClasses()` **ou** `subjectId` ∉ `subjectsForClass()`.
- Valide le trimestre (`T1|T2|T3`).
- N'écrit que pour les élèves inscrits dans la classe.

**Test :** matière non enseignée → **0 ligne écrite** ✅

---

## 4. Autres contrôles vérifiés
- **1ère connexion :** `ForcePasswordChange` (3 panels) force le changement avant tout accès. ✅
- **Changement de mot de passe :** vérifie le mot de passe actuel + `Password::min(8)`. ✅
- **Cloisonnement des resources :** chaque panel ne découvre que son propre dossier de pages (aucune resource admin exposée aux portails). ✅
- **Paiements / notes admin :** `Resource::skipAuthorization()` (ERP mono-admin) — acceptable, l'accès admin est déjà verrouillé par `canAccessPanel`.

---

## Conclusion
**Aucune fuite de données inter-rôles** après correction des 2 failles d'écriture.
Lecture isolée (parent ↔ enfants, prof ↔ classes), écriture validée côté serveur,
accès panels verrouillés par rôle.
