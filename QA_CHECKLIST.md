# ✅ Checklist QA — EliteCampus (test bout-en-bout par rôle)

> ⚠️ **Lancer le serveur avec `php84 artisan serve`** (PHP 8.4 + `intl`).
> NE PAS utiliser `php artisan serve` → prend PHP 8.5 allégé sans `intl` → erreur sur tous les tableaux.
> **Préparation :** Paramètres → **Mode Démo** → *Activer*, puis **Ctrl+Shift+R**.
> Comptes de test : mot de passe `demo1234`.

## 🔵 ADMIN — `http://localhost:8000/admin/login`
- [ ] Tableau de bord avec **2 graphiques** visibles (évolution élèves + donut)
- [ ] Académique → Élèves : KPIs, table, **taux de présence réel**
- [ ] Académique → Présence élèves : appels des profs, filtres classe/statut
- [ ] Académique → Bulletins : classe + élève + trimestre → bulletin **imprimable**
- [ ] Finances → Paiements : montants affichés (plus d'erreur intl)
- [ ] Employés → action **« Créer un compte »** → identifiants affichés
- [ ] Paramètres → Mode Démo : Activer / Supprimer

## 🟢 ENSEIGNANT — `http://localhost:8000/staff/login` (`salimwhichi@elamana.tn`)
- [ ] Tableau de bord : KPIs + cours du jour + fiches de paie
- [ ] Mon emploi du temps : grille hebdomadaire remplie
- [ ] Faire l'appel : P/R/A/E → Enregistrer → recharger (modifiable)
- [ ] Mes classes → Voir les élèves : taux de présence par élève
- [ ] Saisie des notes : classe + matière + trimestre → /20 → Enregistrer
- [ ] Mes fiches de paie + Mon pointage (Pointer arrivée/départ)
- [ ] Accès `/admin` → **403** (cloisonnement OK)

## 🟡 PARENT — `http://localhost:8000/parent/login` (`parent1@elamana.tn`)
- [ ] Tableau de bord : KPIs + **graphique présence** + donut paiements + activité
- [ ] Paiements / Emploi du temps / Suivi / Bulletins / Annonces
- [ ] Ne voit **que ses propres enfants**

## 🔐 Sécurité (cf. SECURITY_AUDIT.md)
- [ ] 1ère connexion (compte créé par admin) → changement de mot de passe forcé
- [ ] Un prof ne peut pas pointer/noter une classe non enseignée

## ⚙️ Environnement
- [ ] Serveur : `php84 artisan serve`  ·  MySQL démarré
- [ ] Après modif code : `php84 artisan optimize:clear`
