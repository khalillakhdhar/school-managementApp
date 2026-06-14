# EliteCampus ERP — Business Dependency Matrix
**Date:** 2026-06-14

---

## DÉPENDANCES CRÉATION (ordre obligatoire)

```
SchoolSetting (autonome)
    └─ Pas de dépendance

Level (autonome)
    └─ Requis pour créer Classroom

Classroom
    ├─ Dépend de : Level
    ├─ Optionnel : Employee (teacher_id)
    └─ Requis pour : Student, TimetableEntry

Employee (autonome)
    ├─ Optionnel : Classroom (teacher_id via Classes assignées)
    └─ Requis pour : Attendance, Payroll, TimetableEntry

Subject (autonome)
    └─ Requis pour : TimetableEntry, classroom_subject, employee_subject

Student
    ├─ Dépend de : Classroom (optionnel)
    └─ Requis pour : Payment, Incident, service_student

Service (autonome)
    └─ Requis pour : service_student, payment_service

Parent (autonome)
    └─ Optionnel pour : Student (parent_student)
```

---

## FLUX DONNÉES CRITIQUES

```
Employee ──→ Attendance ──→ Payroll (mode vacataire: load_hours)
                             └─ Payroll (mode CDI: calcul direct)

Student ──→ service_student ──→ Payment
         └─ Incident ──→ IncidentNotificationMail ──→ Parent.email

Classroom ──→ classroom_subject ──→ TimetableEntry ←── Employee
                                    └─ ClassTimetable (page)
                                    └─ TeacherSchedule (page)
```

---

## ENTITÉS SANS DÉPENDANCES (peuvent être créées en premier)

| Entité | Utilisée par |
|---|---|
| Level | Classroom |
| Service | Student (souscription), Payment |
| Subject | Classroom, Employee, TimetableEntry |
| SchoolSetting | Footer, tous les rapports |
| ExpenseCategory | Expense |
| BlogPost | (aucune dépendance aval) |

---

## RISQUES DE SUPPRESSION

| Si on supprime | Impact |
|---|---|
| Level | Bloque création Classroom (FK constraint) |
| Classroom | Orpheline les Students (classroom_id NULL) |
| Employee | Orpheline TimetableEntry, Payroll, Attendance |
| Student | Supprime en cascade : Payments, Incidents |
| Service | Détache les souscriptions élèves |
| Subject | Orpheline TimetableEntry |

---

## ORDRE DE SETUP RECOMMANDÉ (nouvel établissement)

1. **SchoolSetting** — Identité de l'école
2. **Levels** — Niveaux scolaires (6e, 5e, etc.)
3. **Employees** — Personnel (enseignants en premier)
4. **Classrooms** — Classes + enseignant titulaire
5. **Students** — Élèves + classe assignée
6. **Services** — Catalogue des services
7. **service_student** — Souscriptions (via fiche élève)
8. **Subjects** — Matières
9. **TimetableEntries** — Emplois du temps
10. **Payments** — Enregistrer les paiements
