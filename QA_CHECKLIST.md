# ✅ Checklist QA — EliteCampus (test bout-en-bout par rôle)

> **Préparation :** Paramètres → **Mode Démo** → *Activer*. Puis **Ctrl+Shift+R**.
> Comptes : admin (le vôtre) · enseignant `salimwhichi@elamana.tn` · parent `parent1@elamana.tn` — mot de passe `demo1234`.

---

## 🔵 Admin — `/admin`
- [ ] Tableau de bord : KPIs + 2 graphiques (évolution élèves, répartition) s'affichent
- [ ] Élèves : KPI « Taux de présence » non nul + table remplie
- [ ] Académique → **Présence élèves** : appels visibles, filtres classe/statut OK
- [ ] Académique → **Bulletins** : choisir classe/élève/trimestre (T1 ou T2) → bulletin calculé + bouton Imprimer
- [ ] Emploi du temps : chaque classe a sa grille (32 séances)
- [ ] Finances → Paiements : KPIs (recettes, impayés, recouvrement)
- [ ] RH → Employés : action **Créer un compte** sur un employé sans accès → notification identifiants
- [ ] Paramètres → Mode Démo : voir les identifiants démo

## 🟢 Enseignant — `/staff` (`salimwhichi@elamana.tn`)
- [ ] Connexion → tableau de bord (cours du jour, fiches de paie)
- [ ] Mon emploi du temps : grille hebdomadaire
- [ ] **Faire l'appel** : choisir classe + date → P/R/A/E → Enregistrer → revenir, statuts conservés
- [ ] Mes classes → Voir les élèves : taux de présence par élève
- [ ] **Saisie des notes** : classe + matière (celles que j'enseigne) + trimestre → notes /20 → Enregistrer
- [ ] Mes fiches de paie : net annuel + historique
- [ ] Mon pointage : « Pointer l'arrivée » → heure enregistrée
- [ ] Tenter d'accéder à `/admin` → **403** (verrouillé)

## 🟠 Parent — `/parent` (`parent1@elamana.tn`)
- [ ] Connexion → tableau de bord (KPIs + 2 graphiques + enfants + activité + annonces)
- [ ] Paiements : solde + historique de **mes** enfants uniquement
- [ ] Emploi du temps : sélectionner l'enfant → grille de sa classe
- [ ] Suivi : taux de présence + incidents
- [ ] Bulletins : sélectionner enfant + trimestre → bulletin imprimable
- [ ] Annonces : articles publiés
- [ ] Ne voir QUE ses propres enfants (jamais les 48 de l'école)

## 🔒 Sécurité (cf. SECURITY_AUDIT.md)
- [ ] Enseignant ne peut pointer/noter qu'une classe/matière qu'il enseigne
- [ ] Parent ne voit que ses enfants (forcer un ID d'enfant dans l'URL → bloqué)
- [ ] 1ère connexion d'un compte créé par l'admin → redirection forcée changement de mot de passe

## 🧹 Purge
- [ ] Paramètres → Mode Démo → *Supprimer* → base vidée, compte admin conservé
