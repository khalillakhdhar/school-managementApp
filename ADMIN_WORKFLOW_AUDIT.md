# EliteCampus ERP — Business Workflow Audit
**Date:** 2026-06-14

---

## WORKFLOW 1 : INSCRIPTION D'UN ÉLÈVE

**Étapes requises :**
1. Créer un niveau (`/admin/levels`)
2. Créer une classe (`/admin/classrooms`)
3. Inscrire l'élève (`/admin/students/create`) — assigner la classe
4. Attacher les services souscrits (`Élève → Services`)
5. Enregistrer le premier paiement (`Élève → Paiements`)

**Évaluation :** ✅ Flux complet. Après audit, l'inscription d'un élève ET la gestion de ses paiements/services s'effectuent dans la même fiche.

---

## WORKFLOW 2 : GESTION FINANCIÈRE MENSUELLE

**Étapes requises :**
1. Vérifier les paiements en retard (`/admin/payments` badge rouge)
2. Relancer les familles (`SmartAlertsWidget → Paiements en retard`)
3. Enregistrer les dépenses du mois (`/admin/expenses`)
4. Consulter le rapport financier (`/admin/financial-report`)

**Évaluation :** ✅ Flux complet et automatisé. Les alertes intelligentes remontent les retards en temps réel.

---

## WORKFLOW 3 : GESTION RH MENSUELLE

**Étapes requises :**
1. Vérifier les présences du mois (`/admin/attendances`)
   — Action "Marquer tous présents" pour le pointage rapide
2. Générer les fiches de paie (`/admin/payrolls`)
   — CDI : calcul automatique CNSS + IRPP
   — Vacataires : charger les heures depuis les présences
3. Finaliser les fiches (`action: Finaliser`)
4. Payer les employés (`action: Marquer payé` ou `pay_contractor`)

**Évaluation :** ✅ Flux complet. Historique paie visible directement dans la fiche employé.

---

## WORKFLOW 4 : GESTION D'UN INCIDENT

**Étapes requises :**
1. Signaler l'incident depuis la fiche élève (`Élève → Incidents → Signaler`)
   OU depuis `/admin/incidents/create`
2. Définir gravité (faible/moyen/grave)
3. Notifier les parents (`action: Notifier les parents` — envoi email automatique)
4. L'alerte SmartAlertsWidget disparaît une fois parents notifiés

**Évaluation :** ✅ Flux complet. Notification email fonctionnelle via `IncidentNotificationMail`.

---

## WORKFLOW 5 : EMPLOI DU TEMPS

**Étapes requises :**
1. Créer les matières (`/admin/subjects`)
2. Assigner matières aux classes (`Classe → Matières`)
3. Créer les créneaux (`/admin/timetable-entries`)
   — Validation automatique des conflits (salle/enseignant)
4. Visualiser l'emploi du temps (`/admin/class-timetable`)
5. Visualiser le planning enseignant (`/admin/teacher-schedule`)

**Évaluation :** ✅ Flux complet avec validation des conflits en temps réel.

---

## WORKFLOW 6 : GESTION DES CONTRATS

**Étapes requises :**
1. Créer l'employé avec `contract_type = temporary` et `end_date`
2. L'alerte SmartAlertsWidget remonte les contrats expirant dans 30 jours
3. Action RH : renouveler (`EditEmployee`) ou désactiver (`is_active = false`)

**Évaluation :** ✅ Alerte ajoutée dans cet audit. Flux maintenant complet.

---

## POINTS DE FRICTION RÉSIDUELS

| Point de friction | Impact | Solution recommandée |
|---|---|---|
| Pas de filtre "cette semaine" dans Présences | Faible | Ajouter filtre date range |
| Bulletins de notes non disponibles | Moyen | Nouveau module (hors scope) |
| Pas d'export PDF depuis la liste élèves | Faible | Laravel Excel / DomPDF |
| Rapport financier non filtrable par mois | Moyen | Ajouter sélecteur mois/année |
