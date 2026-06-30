# Code Review - Branche `main`

Date: 30 juin 2026  
Projet: `school-management`  
Version analysee: `main` commit `b446190`  
Positionnement: version "one install", single-school, non-SaaS.

## Score global

Score: 7.4 / 10

La branche `main` est une bonne base MVP avancee pour une installation unique. Elle est plus solide que le premier MVP: policies nombreuses, audit log, notifications in-app, PDF, jours feries, portails parent/staff, tests fonctionnels et API desactivee par defaut.

Elle n'est pas encore une version "production propre" a cause de trois familles de dette:

- authorization Filament contournee par `Resource::skipAuthorization()`;
- design system tres inline et fragile;
- encodage mojibake encore visible dans plusieurs fichiers UI/commentaires.

## Validation technique

Commandes executees sur `main`:

```bash
php artisan test
composer validate --strict
php artisan route:list
```

Resultats:

- Tests: OK, `19` tests, `46` assertions.
- Composer: OK.
- Routes Laravel/Filament: OK, `99` routes.
- API metier: desactivee par defaut via `ENABLE_MVP_API=false`.

## Points forts

### Architecture fonctionnelle claire

Le projet est bien decoupe pour un MVP Laravel/Filament:

- 3 panels: `/admin`, `/parent`, `/staff`.
- Resources Filament completes pour l'administration.
- Pages custom pour parent et staff.
- Services metier pour comptes, paiements, paie, bulletins, jours feries.
- Observers pour incidents et paie.
- Audit log pour les operations sensibles.
- Exports PDF bulletin et fiche de paie.

### Securite MVP en progression

Points positifs:

- `canAccessPanel()` separe correctement admin, parent et staff.
- Nombreuses policies existent dans `app/Policies`.
- API metier desactivee par defaut.
- `Gate::before` donne le bypass uniquement aux admins.
- Changement de mot de passe force disponible.
- Notifications de mots de passe temporaires limitees hors environnement local.

### Couverture de tests utile

Les tests couvrent deja:

- acces panel admin/parent/staff;
- changement force de mot de passe;
- calcul bulletin;
- validation paiement + audit;
- CNSS salariale;
- notifications incidents/paie;
- policies principales;
- API desactivee par defaut.

Pour un MVP, c'est un socle sain.

## Problemes critiques / priorite P0

### 1. Les policies ne protegent pas vraiment les Resources Filament admin

Gravite: Haute

Dans `AppServiceProvider`, le projet appelle:

```php
Resource::skipAuthorization();
```

Les policies existent et sont enregistrees, mais Filament Resources ignore leur authorization interne. Dans la version actuelle, cela reste partiellement acceptable car `/admin` est reserve aux admins via le panel. Mais le commentaire est trompeur et la dette devient dangereuse si:

- on ajoute des admins limites;
- on ajoute des roles direction/comptabilite/secretaire;
- une Resource est exposee dans un autre panel;
- on veut utiliser les policies comme source de verite.

Amelioration recommandee:

- Supprimer progressivement `Resource::skipAuthorization()`.
- Activer les policies Resource par Resource.
- Commencer par les modules sensibles: `Payment`, `Payroll`, `Employee`, `AuditLog`, `Student`.
- Ajouter des tests HTTP Filament pour verifier les refus reels, pas seulement `Gate::allows()`.

Priorite: P0 avant beta publique.

### 2. Les PDF bulletins sont trop permissifs pour les enseignants

Gravite: Haute

`DocumentPdfController::bulletin()` autorise les roles `admin`, `teacher`, `employee`, mais ne limite pas les enseignants a leurs propres classes/eleves.

Risque:

- un enseignant authentifie peut telecharger le bulletin d'un eleve qui n'est pas dans ses classes s'il connait l'URL.

Amelioration recommandee:

- Utiliser `StudentPolicy::view` ou une methode dediee `downloadReportCard`.
- Pour `teacher/employee`, verifier que l'employe enseigne dans la classe de l'eleve.
- Ajouter un test: teacher A ne peut pas telecharger bulletin d'une classe B.

Priorite: P0.

### 3. Encodage mojibake encore visible

Gravite: Haute design / qualite percue

Des textes comme `Académique`, `Paramètres`, `Français`, `Fonctionnalités`, `Démo`, etc. restent visibles dans plusieurs fichiers. Cela donne une impression de produit cassé même si la logique fonctionne.

Fichiers touches:

- `app/Providers/AppServiceProvider.php`
- `app/Providers/Filament/AdminPanelProvider.php`
- `resources/views/landing.blade.php`
- plusieurs tests/commentaires

Amelioration recommandee:

- Reconvertir les fichiers en UTF-8 propre.
- Rechercher les motifs mojibake courants avant publication.
- Corriger en priorite les textes visibles dans l'UI, pas seulement les commentaires.
- Ajouter une vérification simple CI ou test automatisé sur les marqueurs mojibake.

Priorite: P0 avant presentation client/testeurs.

## Problemes importants / priorite P1

### 4. Design system Filament trop inline et fragile

Gravite: Moyenne haute

Le design admin est injecte sous forme d'un tres grand bloc `<style>` dans `AdminPanelProvider`. Le portail parent/staff utilise aussi une vue CSS inline. Cela fonctionne, mais ce n'est pas maintenable.

Problemes:

- provider trop long;
- selectors Filament internes fragiles;
- beaucoup de `!important`;
- duplication entre admin et `portal-theme`;
- difficile a tester ou versionner proprement;
- risque de casse lors d'un upgrade Filament.

Amelioration recommandee:

- Extraire le style vers `resources/css/filament/admin/theme.css`.
- Extraire un theme commun parent/staff.
- Garder les render hooks uniquement pour charger les assets.
- Reduire les overrides globaux `*{font-family...}` et les `!important`.
- Documenter les tokens: couleurs, radius, spacing, typographie.

Priorite: P1.

### 5. La landing page charge Tailwind et Alpine depuis CDN

Gravite: Moyenne

`landing.blade.php` utilise `https://cdn.tailwindcss.com` et Alpine CDN. Pour une page publique, cela marche vite en MVP, mais ce n'est pas ideal en production.

Risques:

- dependance externe au runtime;
- performance moins previsible;
- CSP plus difficile;
- build non optimise;
- incoherence avec Vite/Tailwind local.

Amelioration recommandee:

- Migrer la landing vers les assets Vite.
- Compiler Tailwind localement.
- Charger Alpine via npm si necessaire.
- Ajouter une vraie politique CSP plus tard.

Priorite: P1.

### 6. `PaymentService::getStudentBalance()` a une definition ambigue

Gravite: Moyenne metier

Aujourd'hui:

- `total_due` = somme des paiements `pending`;
- `total_paid` = somme des paiements `paid`;
- `outstanding` = `total_due`.

Ce n'est pas faux si `payments.pending` represente des creances, mais le nom `total_due` peut etre confondu avec le total des services/frais dus. Le modele financier reste hybride: `services`, `payments`, pivot `payment_service`.

Amelioration recommandee:

- Renommer les valeurs pour clarifier: `pending_amount`, `paid_amount`, `overdue_amount`.
- Documenter le modele MVP: un paiement `pending` est une echeance/facture simplifiee.
- A moyen terme, creer `invoices` ou `billing_items` si le produit evolue.

Priorite: P1.

### 7. AccountService retourne encore le mot de passe en clair

Gravite: Moyenne

`AccountService` retourne `password` en clair pour permettre l'envoi d'email ou la notification locale. C'est acceptable pour MVP, mais a encadrer.

Amelioration recommandee:

- Garder le mot de passe en clair uniquement en memoire, jamais en log.
- Eviter les notifications persistantes hors local.
- A moyen terme, remplacer par lien de reset password signe/expire.
- Ajouter un test qui verifie que les notifications hors local ne contiennent pas le secret.

Priorite: P1.

## Problemes design / UX

### 8. Interface visuellement ambitieuse mais trop dependante d'overrides

Score design actuel: 7 / 10

Points positifs:

- palette sobre bleu/slate adaptee a un ERP;
- sidebar sombre professionnelle;
- dashboards et cards mieux structures que le Filament par defaut;
- portails parent/staff differencies;
- landing page riche et vendable.

Points faibles:

- beaucoup de styles inline;
- plusieurs pages Blade custom ont leurs propres `<style>`;
- risque d'incoherence entre admin, parent, staff, landing et PDF;
- typographie et espacements forces globalement;
- mobile a tester visuellement, surtout landing et pages staff/parent.

Amelioration recommandee:

- Creer un mini design system dans `resources/css`.
- Centraliser variables: couleurs, radius, shadows, spacing.
- Documenter les composants principaux: KPI, table, section, alert, sidebar.
- Verifier screenshots mobile/desktop sur:
  - `/`
  - `/admin`
  - `/parent/parent-dashboard`
  - `/staff/staff-dashboard`
  - PDF bulletin
  - PDF fiche paie

Priorite: P1.

### 9. Textes et langues

Le projet est oriente francais/arabe/anglais, mais les traductions sont encore melangees:

- labels Filament en anglais et francais;
- certains textes hardcodes dans les Resources;
- mojibake sur plusieurs libelles;
- langue arabe affichee en mojibake dans `LanguageSwitch`.

Amelioration recommandee:

- Centraliser les labels dans `lang/fr`, `lang/ar`, `lang/en`.
- Corriger `LanguageSwitch` avec libelles UTF-8 propres.
- Ne pas traduire progressivement au hasard dans les Resources.

Priorite: P1.

## Dette technique / priorite P2

### 10. Trop de logique metier dans les Resources et Pages Filament

Plusieurs actions Filament font:

- creation/reset de comptes;
- envoi de mails;
- calculs;
- transitions de statut;
- notifications.

Amelioration recommandee:

- Deplacer les workflows dans des services:
  - `AccountService`
  - `PaymentService`
  - `PayrollService`
  - `NotificationService`
  - `DocumentService`
- Garder les Resources comme orchestration UI.

Priorite: P2.

### 11. Tests encore insuffisants pour les vrais parcours UI

Les tests actuels sont utiles, mais il manque:

- exports PDF parent/staff/admin;
- actions Filament sensibles;
- policies appliquees par les pages Filament, pas seulement Gate;
- reset password hors local;
- generation de compte parent/staff;
- paiement avec services + pivot;
- absence d'acces API quand `ENABLE_MVP_API=false`;
- activation API quand `ENABLE_MVP_API=true`.

Priorite: P2, P1 pour PDF bulletin.

### 12. Observabilite et audit

Points positifs:

- `AuditLog` existe;
- `Auditable` trace certains changements;
- notifications in-app existent.

Ameliorations:

- tracer les actions sensibles: reset password, create account, verify/unverify payment, finalize payroll;
- separer audit technique et notification utilisateur;
- ajouter correlation id ou metadata pour requetes sensibles.

Priorite: P2.

## Priorites recommandees pour `main`

### P0 - A corriger avant vraie demo client

1. Corriger l'encodage mojibake visible.
2. Restreindre le PDF bulletin pour les enseignants.
3. Planifier la suppression progressive de `Resource::skipAuthorization()`.

### P1 - A corriger avant beta propre

1. Extraire le CSS Filament inline vers de vrais assets.
2. Migrer la landing page hors Tailwind CDN.
3. Clarifier le modele financier MVP.
4. Ajouter tests PDF et actions sensibles.
5. Centraliser les traductions visibles.

### P2 - A corriger avant production longue duree

1. Refactor workflows Filament vers services.
2. Ajouter audit trail plus complet.
3. Ajouter CI avec tests, Pint, recherche mojibake.
4. Ajouter screenshots QA mobile/desktop.
5. Renforcer les Form Requests/API si l'API est activee.

## Score detaille

| Axe | Score | Commentaire |
| --- | ---: | --- |
| Architecture Laravel | 8/10 | Bonne structure, services presents, modules clairs. |
| Filament | 7/10 | Resources riches, mais authorization ignoree et CSS fragile. |
| Securite MVP | 7/10 | Bon socle, mais PDF bulletin et skipAuthorization a corriger. |
| Tests | 7/10 | 19 tests utiles, manque encore UI/PDF/actions. |
| Design/UI | 7/10 | Visuellement serieux, dette inline et encodage. |
| Maintenabilite | 6.5/10 | Trop de logique dans Resources et styles dans Providers. |

## Conclusion

La branche `main` est la bonne version "one install" a conserver pour le produit single-school. Elle est testable et utilisable pour un MVP avance.

Elle doit maintenant etre nettoyee sur trois fronts:

1. qualite visible: encodage + design system;
2. securite applicative: PDF bulletin + authorization Filament;
3. maintenabilite: extraction CSS et workflows hors Resources.

Apres correction des P0, le score peut monter autour de 8/10 pour une version single-install solide.
