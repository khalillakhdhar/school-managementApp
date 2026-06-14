# 🗺️ EliteCampus — Roadmap de finalisation de l'ERP

> Feuille de route pas-à-pas pour terminer l'ERP (accès Admin / Enseignant / Parent + modules académiques).
> Chaque tâche contient : un **prompt prêt à coller**, les **fichiers concernés**, et les **critères d'acceptation** (✅ = fait).
> Architecture actuelle : 3 panels Filament v5 — `/admin` (admin), `/staff` (teacher/employee), `/parent` (parent).
> Données de test : **Paramètres → Mode Démo** (`DemoDataService`), comptes `demo1234`.

---

## ✅ PHASE 0 — Déjà fait (référence)
- [x] Design system premium (sidebar navy, KPI cards, tables, charts dashboard)
- [x] Mode Démo (seed/purge école tunisienne « El Amana »)
- [x] 3 panels + `User::canAccessPanel()` par rôle
- [x] `employees.user_id`, `AccountService`, actions admin « Créer un compte » (Employés + Parents)
- [x] Espace enseignant de base : tableau de bord + Mon emploi du temps
- [x] Espace parent : tableau de bord (1 page)

---

## ✅ PHASE 1 — Module « Présence des élèves » (TERMINÉE)
> Débloque la valeur des portails enseignant ET parent. Aujourd'hui `attendances` ne concerne que les employés.
> **Fait :** table `student_attendances` + modèle/relations · page enseignant « Faire l'appel » · `StudentAttendanceResource` admin · KPI présence réelle (96,5%) · démo 720 présences.

### ✅ 1.1 — Schéma & modèle
**Prompt :**
```
Crée le module de présence des élèves. Migration `student_attendances`
(student_id, classroom_id, employee_id (qui a saisi), date, status enum
['present','absent','late','excused'], notes, timestamps, unique(student_id,date)).
Modèle StudentAttendance avec relations student/classroom/employee.
Ajoute la relation hasMany sur Student et Classroom. Migre.
```
**Fichiers :** `database/migrations/..._create_student_attendances_table.php`, `app/Models/StudentAttendance.php`, `app/Models/Student.php`, `app/Models/Classroom.php`
**Acceptation :** table créée, `Student::studentAttendances()` fonctionne, `php artisan migrate` OK.

### ✅ 1.2 — Saisie de présence (côté enseignant)
**Prompt :**
```
Dans le panel /staff, crée une page "Faire l'appel" (StudentAttendanceEntry) :
sélection d'une de MES classes (via timetable/classes de l'enseignant connecté) +
date, puis liste des élèves de la classe avec boutons Présent/Absent/Retard/Excusé,
et un bouton "Enregistrer l'appel" qui upsert dans student_attendances.
Design cohérent avec portal-theme. Empêche la double saisie (upsert sur student_id+date).
```
**Fichiers :** `app/Filament/Staff/Pages/StudentAttendanceEntry.php` + vue blade
**Acceptation :** un enseignant fait l'appel d'une classe, les présences sont sauvegardées et re-modifiables.

### ✅ 1.3 — Vue admin + KPI
**Prompt :**
```
Crée AdminPanel StudentAttendanceResource (lecture + filtres classe/date/statut) et
un widget KPI sur la liste Élèves : taux de présence élèves du mois. Mets à jour
StudentsListStatsWidget pour utiliser la présence ÉLÈVE réelle (pas employé).
```
**Fichiers :** `app/Filament/Resources/StudentAttendanceResource.php`, `app/Filament/Widgets/StudentsListStatsWidget.php`
**Acceptation :** la carte « Taux de présence » du module Élèves affiche la vraie présence élève.

### ✅ 1.4 — Démo
**Prompt :**
```
Étends DemoDataService::seed() pour générer la présence des élèves sur les 15 derniers
jours ouvrés (≈95% présents), saisie par le prof titulaire de chaque classe.
```
**Fichiers :** `app/Services/DemoDataService.php`
**Acceptation :** après re-seed, les présences élèves existent et les KPI sont non nuls.

---

## ✅ PHASE 2 — Portail Enseignant complet (TERMINÉE)
### ✅ 2.1 — Mes classes & mes élèves
**Prompt :**
```
Dans /staff, page "Mes classes" : cartes des classes où l'enseignant intervient
(via timetable_entries.employee_id ou classrooms.teacher_id), avec nb d'élèves,
matières enseignées, lien vers la liste des élèves de la classe (lecture seule :
nom, statut, présence du mois).
```
**Fichiers :** `app/Filament/Staff/Pages/MyClasses.php` + vue
**Acceptation :** l'enseignant voit ses classes et la liste des élèves de chacune.

### ✅ 2.2 — Mes fiches de paie
**Prompt :**
```
Dans /staff, page "Mes fiches de paie" : tableau des payrolls de l'employé connecté
(période, brut, retenues CNSS/IRPP, net, statut) en lecture seule, + total annuel.
```
**Fichiers :** `app/Filament/Staff/Pages/MyPayslips.php` + vue
**Acceptation :** l'enseignant voit l'historique de ses paies.

### ✅ 2.3 — Mon pointage
**Prompt :**
```
Dans /staff, page "Mon pointage" : présences (attendances employé) du mois courant
de l'employé connecté + bouton "Pointer mon arrivée/départ aujourd'hui".
```
**Fichiers :** `app/Filament/Staff/Pages/MyAttendance.php` + vue
**Acceptation :** l'employé voit/saisit son pointage du jour.

---

## 🟨 PHASE 3 — Portail Parent complet
### 3.1 — Mes enfants & paiements
**Prompt :**
```
Dans /parent, page "Paiements" : pour chaque enfant du parent connecté, solde dû,
historique des paiements (payés/en attente/en retard), total annuel. Design portal-theme.
```
**Fichiers :** `app/Filament/Parent/Pages/ParentPayments.php` + vue
**Acceptation :** le parent voit les soldes et paiements de ses enfants.

### 3.2 — Emploi du temps de l'enfant
**Prompt :**
```
Dans /parent, page "Emploi du temps" : sélection d'un enfant, affiche la grille
hebdomadaire de sa classe (réutilise la logique grille de MySchedule).
```
**Fichiers :** `app/Filament/Parent/Pages/ChildTimetable.php` + vue
**Acceptation :** le parent voit l'emploi du temps de la classe de son enfant.

### 3.3 — Présences & incidents
**Prompt :**
```
Dans /parent, page "Suivi" : présence du mois (student_attendances) de chaque enfant
+ liste des incidents le concernant. Indicateurs visuels (taux présence, alertes).
```
**Fichiers :** `app/Filament/Parent/Pages/ChildMonitoring.php` + vue
**Acceptation :** le parent voit présences + incidents de ses enfants.

### 3.4 — Annonces
**Prompt :**
```
Dans /parent, page "Annonces" : liste lecture seule des BlogPost publiés (titre,
date, contenu), cards propres.
```
**Fichiers :** `app/Filament/Parent/Pages/ParentAnnouncements.php` + vue
**Acceptation :** le parent lit les annonces publiées.

---

## 🟧 PHASE 4 — Notes & Bulletins (module académique)
### 4.1 — Schéma notes
**Prompt :**
```
Crée le module de notes : migrations `grades` (student_id, subject_id, classroom_id,
employee_id, term enum['T1','T2','T3'], score decimal(5,2), max_score, coefficient,
date, comment) + modèle Grade et relations. Migre.
```
**Fichiers :** migration + `app/Models/Grade.php` + relations
**Acceptation :** table créée, relations OK.

### 4.2 — Saisie des notes (enseignant)
**Prompt :**
```
Dans /staff, page "Saisie des notes" : choix classe + matière + trimestre, puis grille
des élèves pour saisir une note /20. Sauvegarde en masse dans grades.
```
**Fichiers :** `app/Filament/Staff/Pages/GradeEntry.php` + vue
**Acceptation :** un enseignant saisit les notes d'une classe pour une matière.

### 4.3 — Bulletin
**Prompt :**
```
Génère le bulletin trimestriel d'un élève : moyenne par matière (pondérée coefficient),
moyenne générale, rang dans la classe. Vue imprimable côté admin ET côté parent (lecture).
```
**Fichiers :** `app/Services/ReportCardService.php`, pages admin + parent
**Acceptation :** bulletin calculé correctement, imprimable, visible par le parent.

---

## 🟪 PHASE 5 — Sécurité & transverse
### 5.1 — Changement de mot de passe à la 1ère connexion
**Prompt :**
```
Implémente la redirection forcée vers une page "Changer mon mot de passe" quand
User.must_change_password = true, sur les panels /staff et /parent (middleware ou
hook Filament). Après changement, must_change_password=false.
```
**Acceptation :** un compte fraîchement créé est obligé de changer son mot de passe.

### 5.2 — Emails (bienvenue / rappels)
**Prompt :**
```
Configure l'envoi d'emails (actuellement MAIL_MAILER=log). Documente la config SMTP
dans .env.example. Vérifie ParentWelcomeMail + crée StaffWelcomeMail. Les rappels de
paiement (reminders) doivent partir par email.
```
**Acceptation :** emails de bienvenue testables (au moins en log), template propre.

### 5.3 — Revue de sécurité
**Prompt :**
```
Lance /security-review sur la branche. Vérifie : isolation des données par rôle
(un parent ne voit QUE ses enfants, un prof QUE ses classes), pas de fuite via
les pages portail, validation des saisies.
```
**Acceptation :** aucune fuite de données inter-rôles.

---

## 🟫 PHASE 6 — Finitions & QA
- [ ] **6.1** Étendre le Mode Démo aux notes + présences élèves + comptes staff non-enseignants
- [ ] **6.2** Cohérence design : auditer chaque page des 3 panels (cards, badges, vides)
- [ ] **6.3** Traductions FR complètes (libellés, enums affichés)
- [ ] **6.4** Tests manuels de bout en bout par rôle (checklist)
- [ ] **6.5** `/code-review` final + nettoyage des fichiers d'audit temporaires

---

## 📌 Ordre recommandé (chemin critique)
1. **Phase 1** (présence élèves) — socle qui donne de la valeur aux 2 portails
2. **Phase 2** (enseignant) — exploite la présence + paie
3. **Phase 3** (parent) — exploite présence + paiements + emploi du temps
4. **Phase 5.1** (1ère connexion) — sécurité minimale avant usage réel
5. **Phase 4** (notes/bulletins) — gros module, mais autonome
6. **Phase 5.2/5.3 + Phase 6** — emails, sécurité, finitions

> 💡 Conseil : après CHAQUE phase, activer le Mode Démo et tester avec les comptes
> `demo1234` (enseignant `salimwhichi@elamana.tn`, parent `parent1@elamana.tn`).
