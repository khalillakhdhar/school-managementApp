# Plan Maître - Améliorations MVP Code Et Design

Date: 30 juin 2026  
Projet: `school-management`  
Stack: Laravel 13, Filament, MySQL, Vite/Tailwind  
Branches concernées: `main`, `saas`, `fix-main-one-install-hardening`

## Objectif

Continuer les améliorations du projet avec deux objectifs:

1. rendre le code plus fiable pour une bêta physique contrôlée;
2. généraliser un design SaaS professionnel sur toutes les branches, sans merger `main` et `saas` tant que cette séparation produit est voulue.

Positionnement:

- `main`: version one-install / single-school.
- `saas`: version SaaS séparée.
- `fix-main-one-install-hardening`: branche de durcissement/refactoring issue de `main`.

## Déjà Fait

- Icône notification inutile supprimée sur les trois branches.
- Ligne/ring disgracieux près du sélecteur de langue nettoyé sur les trois branches.
- Accès admin local restauré:

```text
URL: /admin/login
Email: admin@example.com
Mot de passe: demo1234
```

Sur `fix-main-one-install-hardening`:

- retrait de `Resource::skipAuthorization()`;
- PDF bulletin protégé via `Gate::authorize('view', $student)`;
- tests PDF parent/teacher/admin;
- clarification `PaymentService::getStudentBalance()`;
- test du pivot `payment_service.amount`;
- smoke test mojibake;
- extraction du CSS admin vers `resources/views/filament/admin-theme.blade.php`;
- landing migrée hors CDN Tailwind/Alpine;
- build Vite validé.

Validations observées:

- `main`: 19 tests, 46 assertions.
- `saas`: 33 tests, 92 assertions.
- `fix-main-one-install-hardening`: 25 tests, 60 assertions.

## Scores Actuels

| Branche | Code MVP | Design | Commentaire |
| --- | ---: | ---: | --- |
| `main` | 7.4 / 10 | 7.2 / 10 | Stable MVP, mais garde plus de dette CSS et sécurité Filament que la branche de durcissement. |
| `saas` | 7.5 / 10 | 7.3 / 10 | Plus orientée SaaS, mais design encore trop inline et finition incomplète. |
| `fix-main-one-install-hardening` | 8.4 / 10 | 7.8 / 10 | Meilleure base pour futur `main`, plus propre côté sécurité/tests/design. |

Objectif:

- Code MVP: 8.8 / 10.
- Design SaaS: 8.8 à 9.2 / 10.

## Règles Multi-Branches

- Ne pas merger `main` et `saas` sans décision explicite.
- Garder `main` comme version one-install.
- Garder `saas` comme version SaaS séparée.
- Appliquer les corrections UI communes sur les trois branches quand elles sont génériques.
- Appliquer les corrections métier selon la branche concernée.
- Ne pas modifier les mots de passe démo sans accord.
- Ne pas lancer `php artisan migrate:fresh --seed` sur une base à préserver.

## Priorité P0 - Stabilisation MVP

1. Décider si `fix-main-one-install-hardening` devient le futur `main`.
2. Pousser les commits locaux après validation visuelle.
3. Nettoyer l'encodage restant, surtout dans `DemoDataService` et les textes visibles.
4. Garder un smoke test mojibake pour éviter les régressions.

## Priorité P1 - Code MVP

### Sécurité Et Autorisations

- Généraliser le retrait de `Resource::skipAuthorization()` si nécessaire.
- Vérifier que chaque Resource Filament critique a une policy.
- Tester admin/parent/staff sur les routes critiques.
- Protéger les exports PDF sensibles par policy.

Tests attendus:

- admin accède aux ressources admin;
- parent refusé sur admin;
- teacher/staff refusé sur admin;
- parent voit uniquement ses enfants;
- teacher voit uniquement ses classes;
- PDF bulletin refusé hors périmètre.

### AccountService Et Reset Password

- Remplacer progressivement les mots de passe temporaires par liens de reset signés et expirables.
- Ajouter un test qui prouve qu'aucun secret n'est affiché hors `APP_ENV=local`.
- Tracer création de compte et reset password dans l'audit.

### Audit Métier

Tracer explicitement:

- création de compte parent/staff;
- reset password;
- verify/unverify payment;
- finalisation paie;
- marquage paie payée;
- export PDF bulletin;
- export PDF paie.

### Paiements Et Facturation MVP

- Documenter le modèle financier MVP.
- Ajouter tests sur retards par `due_date`.
- Ne pas créer `Invoice` avant validation produit.
- Préparer à moyen terme `Invoice` ou `BillingItem` si le besoin se confirme.

### Tests À Ajouter

- actions Filament sensibles;
- création compte parent/staff;
- reset password;
- exports PDF admin/parent/staff;
- `ENABLE_MVP_API=false`;
- `ENABLE_MVP_API=true`;
- smoke test accès panels.

## Priorité P1 - Design SaaS Commun

### Design System Centralisé

Créer ou consolider:

- `resources/css/filament/admin.css`
- `resources/css/filament/portal.css`
- `resources/css/public/landing.css`
- `resources/css/pdf.css`
- `resources/css/email.css`

Tokens à définir:

- couleurs principales;
- couleurs success/warning/danger/info;
- spacing;
- radius;
- shadows;
- typo;
- density table;
- badges;
- buttons;
- inputs.

Décision visuelle:

- garder bleu/slate comme base;
- ajouter emerald en accent finance/success;
- éviter une interface 100% bleue;
- limiter les gradients forts.

### Sortir Le CSS Inline

Dette actuelle:

- `main` et `saas` gardent encore beaucoup de CSS dans `AdminPanelProvider`;
- beaucoup de vues Blade utilisent `style=""`;
- PDF et emails ont leur propre style inline.

À faire:

- sortir le style admin du provider;
- sortir `portal-theme` en asset;
- déplacer les styles des widgets vers classes CSS;
- garder inline uniquement pour valeurs dynamiques simples.

Critère d'acceptation:

- `AdminPanelProvider` ne contient plus de gros `<style>`;
- les vues Blade sont lisibles;
- le design est maintenable.

### Sidebar Et Topbar

Sidebar:

- conserver sidebar sombre;
- rendre les groupes plus lisibles;
- hover/active/focus cohérents;
- labels sans débordement;
- largeur stable desktop/mobile.

Topbar:

- garder topbar claire;
- aligner langue/profil/recherche;
- aucun élément inutile;
- aucune bordure parasite;
- responsive propre.

### Cards, Tables, Forms

Cards:

- radius 8-12px;
- shadows sobres;
- paddings cohérents;
- pas de cards imbriquées.

Tables:

- densité ERP;
- header sobre;
- hover léger;
- actions alignées;
- badges cohérents;
- empty states propres.

Forms:

- labels courts;
- sections mieux groupées;
- messages d'aide utiles;
- erreurs lisibles;
- boutons standardisés.

## Priorité P2 - Expérience SaaS Complète

### Landing Page

- Hero plus premium.
- Screenshots réels produit.
- Sections moins denses.
- Pricing plus crédible.
- CTA clair.
- Preuve sociale ou cas d'usage.
- Mobile impeccable.

### Portail Parent

Écran idéal:

- sélecteur enfant clair;
- résumé paiement;
- prochaine échéance;
- dernières présences;
- dernier bulletin;
- annonces récentes;
- bouton PDF visible.

Ton:

- moins admin;
- plus humain;
- très lisible.

### Portail Staff

Écran idéal:

- planning du jour;
- classes assignées;
- notes à saisir;
- présences à prendre;
- fiche de paie récente;
- actions rapides.

Ton:

- utilitaire;
- calme;
- efficace.

### PDF Et Emails

PDF bulletin:

- logo;
- identité école;
- tableau notes propre;
- moyenne/rang/mention lisibles;
- signature direction;
- footer.

PDF paie:

- layout administratif;
- brut/retenues/net;
- période visible;
- statut clair.

Emails:

- template partagé;
- logo;
- titre;
- CTA;
- footer;
- compatibilité clients email.

## Priorité P3 - Finition Produit

### Responsive QA

Tester:

- `/`
- `/admin`
- `/parent/parent-dashboard`
- `/staff/staff-dashboard`
- pages paiement;
- pages bulletin;
- PDF.

Viewports:

- 390px mobile;
- 768px tablette;
- 1440px desktop.

### Microcopy Et Langues

- Uniformiser le français.
- Supprimer anglais visible.
- Centraliser labels fréquents dans `lang/fr`.
- Préparer `lang/ar` et `lang/en`.
- Éviter labels trop longs dans menus et cards.

Exemples:

- `Overdue Payments` -> `Paiements en retard`
- `Collection Rate` -> `Taux d'encaissement`
- `Pay slip finalized` -> `Fiche de paie finalisée`

### Dark Mode

Décision:

- soit désactiver proprement;
- soit finir complètement.

Recommandation MVP:

- désactiver visuellement le dark mode si la finition reste partielle.

## Plan D'Implémentation Recommandé

### Phase 1 - Consolidation

1. Créer une branche `mvp-code-design-continuation`.
2. Partir de `fix-main-one-install-hardening` si elle doit devenir le futur `main`.
3. Vérifier les tests.
4. Mettre à jour les documents.
5. Décider push/merge.

### Phase 2 - Design System

1. Créer les assets CSS dédiés.
2. Extraire le CSS admin.
3. Extraire le CSS portail.
4. Définir tokens.
5. Harmoniser sidebar/topbar.
6. Harmoniser cards/tables/forms.

### Phase 3 - Expériences Métier

1. Refaire landing.
2. Refaire dashboard parent.
3. Refaire dashboard staff.
4. Refaire PDF.
5. Refaire emails.

### Phase 4 - Sécurité Et Audit

1. Reset password signé.
2. Audit métier.
3. Tests actions Filament.
4. Tests PDF.
5. Tests API.

### Phase 5 - QA

1. `composer validate --strict`
2. `php artisan test`
3. `php artisan route:list`
4. `npm run build`
5. `php artisan optimize:clear`
6. QA mobile/desktop.

## Commandes Utiles

```bash
git branch -vv
git status --short --branch
composer validate --strict
php artisan test
php artisan route:list
npm run build
php artisan optimize:clear
```

## Critères D'Acceptation Globaux

- Branches non mélangées sans décision.
- `main` reste one-install.
- `saas` reste SaaS.
- Admin/parent/staff conservent leurs accès.
- Aucune icône ou ligne topbar inutile.
- Aucun texte mojibake visible.
- CSS maintenable.
- Landing sans CDN runtime inutile.
- PDF partageables physiquement.
- Emails cohérents.
- Tests Laravel passent.
- Build Vite passe.

## Prompt De Reprise Pour Une Nouvelle Discussion

```text
Lis MVP_CODE_DESIGN_MASTER_PLAN.md. Continue les améliorations code MVP et design SaaS. Commence par la Phase 1 puis la Phase 2, sans merger main et saas, et applique les améliorations UI communes sur toutes les branches.
```
