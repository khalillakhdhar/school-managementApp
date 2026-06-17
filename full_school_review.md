# Code Review Laravel / Filament - School Management

Date: 17 juin 2026
Contexte: MVP ERP scolaire Laravel + Filament, destiné à être partagé avec des testeurs physiques.

## Score Global

Score actuel: 6.5 / 10

Le projet est un MVP avancé et déjà exploitable en démonstration contrôlée. La structure générale est bonne, les modules métier sont nombreux, les panels Filament sont séparés par rôle, et l'application démarre correctement.

En revanche, il reste des corrections importantes avant de donner un accès réel à des testeurs externes, même en phase MVP. Les risques principaux ne sont pas liés à Laravel ou Filament eux-mêmes, mais à la gestion des permissions, aux secrets exposés, aux mots de passe temporaires, à l'encodage des textes français, et au manque de garde-fous métier.

## Résumé Exécutif

Le projet peut être partagé avec des testeurs physiques si l'accès est limité, les données sont fictives, et quelques corrections prioritaires sont appliquées avant ouverture.

À ne pas faire en l'état:

- Ne pas utiliser de vraies données élèves, parents, salaires ou paiements.
- Ne pas exposer l'application publiquement sans protection réseau ou authentification forte.
- Ne pas conserver `.env.production` avec de vrais mots de passe dans le dépôt.
- Ne pas donner des comptes admin complets à tous les testeurs.

À faire avant test réel:

- Créer des comptes test dédiés par rôle: admin demo, parent demo, enseignant demo.
- Corriger les problèmes d'encodage visibles dans l'interface.
- Retirer ou neutraliser les secrets présents dans `.env.production`.
- Empêcher l'affichage de mots de passe temporaires dans les notifications.
- Ajouter au minimum des restrictions d'accès côté API et panels.

## Points Forts

### Architecture Laravel correcte

Le projet suit une structure Laravel lisible:

- `app/Models` pour les entités métier.
- `app/Filament/Resources` pour l'administration.
- `app/Filament/Pages`, `Parent/Pages`, `Staff/Pages` pour les écrans dédiés.
- `app/Services` pour certains traitements métier comme la paie et les paiements.
- `database/migrations` assez complètes.

La séparation entre modèles, ressources Filament, services et controllers est correcte pour un MVP.

### Panels Filament séparés

Le projet possède trois espaces distincts:

- `/admin` pour l'administration.
- `/parent` pour le portail parent.
- `/staff` pour l'espace personnel / enseignant.

La méthode `canAccessPanel()` dans `App\Models\User` est un bon point de départ. Elle évite déjà qu'un parent accède directement au panel admin.

### Couverture fonctionnelle large

Le MVP couvre beaucoup de besoins:

- Gestion des élèves.
- Parents et rattachements aux enfants.
- Classes, niveaux, matières.
- Emploi du temps.
- Présences élèves et employés.
- Incidents.
- Paiements.
- Dépenses.
- Paie.
- Portail parent.
- Portail staff.
- Bulletins / notes.
- Emails de bienvenue et rappels.

C'est ambitieux pour un MVP, mais la base métier est cohérente.

### Application bootable

Les vérifications effectuées montrent que:

- `php artisan about` fonctionne.
- `php artisan route:list` fonctionne.
- `composer validate --strict` passe.
- `php artisan test` passe.

Attention: les tests existants sont seulement les exemples Laravel par défaut. Ils prouvent que le squelette démarre, pas que le métier est fiable.

## Problèmes Critiques À Corriger Avant Testeurs

### 1. Secrets présents dans `.env.production`

Gravité: Critique

Le fichier `.env.production` contient des secrets en clair:

- `DB_PASSWORD`
- `DB_ROOT_PASSWORD`
- informations de connexion MySQL

Même pour un MVP, ce fichier ne doit pas être partagé tel quel.

Correction recommandée:

- Supprimer `.env.production` du dépôt si des secrets réels y sont présents.
- Créer `.env.production.example` sans valeurs sensibles.
- Ajouter `.env.production` dans `.gitignore`.
- Changer les mots de passe déjà exposés.

Priorité: P0

### 2. Mots de passe temporaires affichés dans l'interface

Gravité: Critique

Dans `ParentResource` et `EmployeeResource`, certains mots de passe temporaires sont affichés dans des notifications Filament quand l'envoi email échoue, ou après reset.

Pour un MVP local, c'est pratique. Pour des testeurs physiques, c'est risqué:

- Le mot de passe peut rester visible à l'écran.
- Un autre utilisateur peut le lire.
- L'admin peut copier/coller le mauvais mot de passe.
- Cela habitue à une mauvaise pratique.

Correction recommandée:

- Ne jamais afficher le mot de passe en clair dans une notification persistante.
- Envoyer un lien de réinitialisation ou un mot de passe temporaire uniquement par canal contrôlé.
- Pour le MVP, afficher un message du type: "Compte créé. Transmettez les identifiants via le canal prévu."
- En mode demo local uniquement, autoriser l'affichage si `APP_ENV=local`.

Priorité: P0

### 3. API trop permissive

Gravité: Critique

Les routes API sont protégées par `auth:sanctum`, mais les controllers ne font pas d'autorisation fine.

Exemples:

- `StudentController@index()` retourne les élèves actifs.
- `StudentController@show()` charge parents, services, paiements.
- `PaymentController@index()` retourne les paiements avec élèves.
- `PayrollController` expose des données de paie.

Un utilisateur authentifié pourrait accéder à trop de données si un token est créé ou mal utilisé.

Correction recommandée:

- Ajouter des policies Laravel pour `Student`, `Payment`, `Payroll`, `Incident`, `Grade`.
- Appeler `$this->authorize(...)` dans les controllers API.
- Séparer les droits API admin, parent et staff.
- Pour le MVP, désactiver temporairement les routes API non nécessaires aux testeurs.

Priorité: P0 si API exposée, P1 si API non utilisée pendant les tests.

### 4. Absence de policies métier

Gravité: Haute

Le contrôle d'accès repose surtout sur:

- `canAccessPanel()`.
- Quelques vérifications manuelles dans les pages staff.
- La séparation des panels Filament.

C'est insuffisant dès que plusieurs rôles manipulent des données sensibles.

Risques:

- Un parent pourrait voir des données d'un autre enfant si une page est mal filtrée.
- Un enseignant pourrait voir ou modifier une classe non assignée si un écran oublie le filtre.
- Un admin secondaire aurait trop de droits.

Correction recommandée:

- Créer les policies principales:
  - `StudentPolicy`
  - `PaymentPolicy`
  - `PayrollPolicy`
  - `GradePolicy`
  - `IncidentPolicy`
  - `EmployeePolicy`
- Ajouter des tests minimum sur les accès par rôle.

Priorité: P1

## Problèmes Importants À Corriger

### 5. Encodage français cassé

Gravité: Haute

Beaucoup de textes apparaissent sous forme corrompue:

- `Académique`
- `Élève`
- `Téléphone`
- `Payé`
- `Échoué`

Cela donne une impression de produit non fini. Pour des testeurs physiques, c'est probablement le problème le plus visible.

Correction recommandée:

- Reconvertir tous les fichiers PHP/Blade en UTF-8.
- Rechercher les séquences de texte corrompu dans les libellés visibles.
- Remplacer les textes corrompus.
- Centraliser les libellés dans `lang/fr`.

Priorité: P0 avant démonstration.

### 6. CSS Filament injecté dans `AdminPanelProvider`

Gravité: Moyenne à haute

Le provider admin contient un très gros bloc CSS inline via `renderHook('panels::head.end')`.

Problèmes:

- Difficile à maintenir.
- Difficile à versionner proprement.
- Fragile si Filament change ses classes CSS.
- Rend le provider trop gros.
- Mélange configuration panel et design system.

Point particulièrement risqué:

- La règle CSS masque `#livewire-error`.

Masquer les erreurs Livewire peut donner l'impression que l'application fonctionne alors qu'une action échoue en arrière-plan.

Correction recommandée:

- Déplacer le CSS vers un vrai fichier thème Filament.
- Utiliser le mécanisme officiel de thème Filament.
- Supprimer la règle qui cache `#livewire-error`.
- Corriger les erreurs Livewire réelles au lieu de les masquer.

Priorité: P1

### 7. Données de seed insuffisantes

Gravité: Haute pour testeurs

Le seeder crée seulement un `Test User`. Pour faire tester un ERP scolaire, il faut un jeu de données réaliste.

Correction recommandée:

Créer un seeder demo avec:

- 1 admin demo.
- 2 enseignants.
- 3 parents.
- 5 à 10 élèves.
- 2 niveaux.
- 3 classes.
- quelques matières.
- emplois du temps.
- paiements payés, en attente et en retard.
- présences.
- incidents.
- notes.

Priorité: P0 pour tests physiques.

### 8. Calcul de solde et retard paiement trop simplifié

Gravité: Haute métier

Le calcul de retard dans `PaymentService` repose sur le dernier paiement payé. Ce n'est pas le bon modèle pour gérer des frais scolaires.

Un retard doit dépendre:

- des échéances impayées,
- des dates d'échéance,
- du montant dû,
- des services/frais réellement facturés,
- d'éventuels paiements partiels.

Correction recommandée:

- Introduire une notion de facture, échéance ou créance.
- Calculer le solde depuis les paiements pending/unpaid.
- Ne pas déduire uniquement depuis les services associés.
- Gérer les paiements partiels.

Priorité: P1

### 9. `PaymentService::recordPayment()` attache les services sans montant pivot

Gravité: Moyenne

La table pivot `payment_service` contient un champ `amount`, mais `recordPayment()` fait seulement:

```php
$payment->services()->attach($serviceIds);
```

Si la colonne `amount` est obligatoire en base, cela peut casser. Si elle a une valeur par défaut absente, les données seront incomplètes.

Correction recommandée:

- Attacher les services avec le montant correspondant.
- Ou rendre `amount` nullable si ce détail n'est pas encore utilisé.
- Ajouter un test sur l'enregistrement d'un paiement avec services.

Priorité: P1

### 10. Stockage public non lié

Gravité: Moyenne

`php artisan about` indique:

- `public/storage .. NOT LINKED`

Si le projet utilise photos, logos, reçus ou fiches de paie, les fichiers uploadés ne seront pas servis correctement.

Correction recommandée:

```bash
php artisan storage:link
```

Priorité: P1 avant testeurs si uploads utilisés.

## Problèmes Moyens / Dette Technique

### 11. Trop de logique dans les Resources Filament

Plusieurs Resources contiennent:

- logique de création de comptes,
- génération de mots de passe,
- envoi d'emails,
- calculs,
- actions métier.

Pour un MVP, c'est acceptable. Mais si le produit continue, il faudra déplacer progressivement cette logique vers des services.

Correction recommandée:

- Utiliser `AccountService` partout pour parents et employés.
- Créer `PaymentWorkflowService`.
- Créer `GradeService` si la logique des notes évolue.

Priorité: P2

### 12. Validation métier incomplète

Exemples:

- Les montants peuvent parfois être `min:0`, ce qui autorise `0`.
- Certaines dates ne valident pas l'ordre logique.
- Les statuts sont parfois hardcodés dans plusieurs fichiers.
- Les rôles `teacher` et `employee` ne sont pas parfaitement alignés avec l'enum initial de migration.

Correction recommandée:

- Ajouter des Form Requests côté API.
- Ajouter des Enums PHP pour statuts et rôles.
- Standardiser les validations Filament et API.

Priorité: P2

### 13. Tests quasi absents

Gravité: Moyenne pour MVP, haute avant production

Les tests actuels sont les tests exemples Laravel.

Tests minimum recommandés avant testeurs:

- Admin peut accéder à `/admin`.
- Parent ne peut pas accéder à `/admin`.
- Staff ne peut pas accéder à `/admin`.
- Parent voit seulement ses enfants.
- Enseignant peut saisir des notes uniquement pour ses classes.
- Paiement peut être créé.
- Reset password force bien le changement au login.

Priorité: P1 pour accès testeurs, P0 avant production.

### 14. Documentation projet trop faible

Le `README.md` est quasiment vide.

Correction recommandée:

Ajouter:

- prérequis,
- installation,
- commandes utiles,
- comptes demo,
- lancement local,
- variables `.env`,
- procédure de reset base demo,
- limites connues du MVP.

Priorité: P1 pour testeurs et équipe.

## Revue Filament Spécifique

### Bonnes pratiques présentes

- Resources nombreuses et bien séparées.
- Actions personnalisées utiles.
- Navigation par groupes.
- Panels séparés.
- Usage de widgets pour dashboards.
- Pages custom pour parent et staff.
- Certaines optimisations de requêtes dans `StudentResource` et `EmployeeResource`.

### Améliorations Filament recommandées

- Ajouter des `canViewAny`, `canCreate`, `canEdit`, `canDelete` via policies.
- Éviter les requêtes directes dans les options si la table peut grandir.
- Remplacer les gros `options(Student::orderBy(...)->get())` par `relationship()->searchable()->preload(false)` selon le volume.
- Ajouter des filtres utiles pour testeurs:
  - statut élève,
  - classe,
  - paiement en retard,
  - enseignant,
  - période.
- Standardiser les labels via traductions.
- Éviter de masquer les erreurs Livewire.
- Déplacer le thème Filament hors du provider.

## Priorités Avant Partage Avec Testeurs Physiques

### P0 - À faire avant de partager l'accès

1. Retirer les secrets de `.env.production`.
2. Corriger l'encodage visible dans l'interface.
3. Créer des comptes demo dédiés par rôle.
4. Créer un seeder de données fictives réalistes.
5. Désactiver ou restreindre l'API si elle n'est pas nécessaire aux tests.
6. Ne plus afficher les mots de passe temporaires en clair dans les notifications.
7. S'assurer que les testeurs n'utilisent aucune donnée réelle.

### P1 - À faire pendant la phase MVP test

1. Ajouter policies principales.
2. Ajouter tests Feature sur accès par rôle.
3. Corriger le calcul de solde/retard paiement.
4. Lier le storage public si uploads utilisés.
5. Déplacer le CSS Filament vers un thème propre.
6. Améliorer le README.
7. Ajouter logs propres pour erreurs email et actions sensibles.

### P2 - À faire avant version beta sérieuse

1. Refactorer la logique métier hors des Resources.
2. Introduire des Enums PHP pour statuts/rôles.
3. Ajouter des exports propres PDF/Excel si nécessaires.
4. Ajouter audit trail des actions sensibles.
5. Améliorer dashboards avec requêtes agrégées optimisées.
6. Ajouter sauvegarde/restauration base demo.

## Checklist Testeurs Physiques

Avant d'envoyer le lien aux testeurs:

- [ ] Base remplie avec données fictives.
- [ ] Aucun vrai élève dans la base.
- [ ] Aucun vrai salaire dans la base.
- [ ] Aucun secret dans le dépôt.
- [ ] Comptes test créés:
  - admin.demo@example.test
  - parent.demo@example.test
  - teacher.demo@example.test
- [ ] Chaque compte a un mot de passe connu et temporaire.
- [ ] Le changement forcé de mot de passe fonctionne.
- [ ] Parent demo ne voit que ses enfants.
- [ ] Teacher demo ne voit que ses classes.
- [ ] Admin demo ne peut pas casser la base par erreur, ou les données sont resetables.
- [ ] `APP_DEBUG=false` sur l'environnement partagé.
- [ ] Backups ou reset script disponibles.
- [ ] README de test fourni aux testeurs.

## Recommandation De Mise À Disposition MVP

Pour partager le projet avec des testeurs physiques, je recommande ce mode:

- Hébergement privé ou URL non indexée.
- Données 100% fictives.
- `APP_ENV=staging`.
- `APP_DEBUG=false`.
- Mail en mode `log` ou SMTP de test.
- API désactivée sauf besoin explicite.
- Comptes test par rôle.
- Reset base possible après chaque session.
- Formulaire ou canal de feedback pour les testeurs.

## Conclusion

Le projet est une bonne base MVP Laravel/Filament. Il est suffisamment avancé pour faire tester les parcours principaux: administration scolaire, portail parent, portail staff, paiements, présences et notes.

La priorité n'est pas de refaire l'architecture. La priorité est de rendre le MVP présentable, contrôlé et testable:

- corriger ce qui est visible,
- réduire les risques d'accès excessif,
- sécuriser les secrets,
- préparer des données demo,
- ajouter quelques tests d'accès par rôle.

Après les corrections P0, le projet peut raisonnablement passer de 6.5/10 à environ 7.5/10 pour un MVP testable. Avec les P1, il peut atteindre 8/10 en qualité MVP.
