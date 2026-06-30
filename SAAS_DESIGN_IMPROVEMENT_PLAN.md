# Plan Design SaaS Pro - School Management

Date: 30 juin 2026  
Projet: `school-management`  
Branches concernées: `main`, `saas`, `fix-main-one-install-hardening`

## Objectif

Faire passer l'interface d'un MVP Filament fonctionnel vers une expérience SaaS professionnelle, cohérente et présentable à des testeurs, clients et décideurs.

Score design estimé aujourd'hui:

- `main`: 7.2 / 10
- `saas`: 7.3 / 10
- `fix-main-one-install-hardening`: 7.8 / 10

Score cible après une vraie passe design:

- P1 terminé: 8.5 / 10
- P1 + P2 terminé: 9.0 / 10
- Niveau SaaS très propre: 9.2 / 10

## Constat Actuel

Points positifs:

- Sidebar sombre sérieuse et lisible.
- Palette bleu/slate adaptée à un ERP scolaire.
- Panels séparés: admin, parent, staff.
- Landing page riche.
- Plusieurs widgets et dashboards donnent déjà une impression produit.
- Icône notification inutile supprimée.
- Sélecteur de langue nettoyé visuellement.

Points faibles:

- Trop de CSS inline dans les providers, Blade, widgets et PDF.
- `main` et `saas` gardent encore un gros bloc CSS dans `AdminPanelProvider`.
- Les pages custom parent/staff utilisent beaucoup de `style=""`.
- Les PDF et emails ont chacun leur style propre.
- Les textes alternent parfois anglais/français.
- Le dark mode est partiel.
- La landing reste très chargée et manque de vrais visuels produit.
- Les états vides, erreurs et chargements sont encore trop bruts.

## Priorité P1 - Fondation Design System

### 1. Créer Des Assets CSS Dédiés

Objectif: sortir progressivement le style des providers et vues.

Créer ou consolider:

- `resources/css/filament/admin.css`
- `resources/css/filament/portal.css`
- `resources/css/public/landing.css`
- `resources/css/pdf.css`
- `resources/css/email.css`

Règle:

- Les providers Filament ne doivent plus contenir de gros `<style>`.
- Les vues Blade ne doivent plus porter de styles globaux.
- Les styles inline restent acceptables uniquement pour les valeurs dynamiques très courtes.

### 2. Centraliser Les Tokens UI

Définir:

- couleurs principales;
- couleurs d'état: success, warning, danger, info;
- radius: 8px pour outils, 10-12px pour cards;
- shadows;
- spacing;
- typographie;
- densité de tables;
- badges;
- boutons;
- inputs.

Décision recommandée:

- Dominante: bleu professionnel + slate.
- Accent secondaire: emerald pour finance/success.
- Éviter une interface trop monochrome bleu.

### 3. Harmoniser Sidebar Et Topbar

Sidebar:

- Garder la sidebar sombre.
- Réduire les effets trop lourds.
- Normaliser hover/active/focus.
- Éviter les labels trop gros.
- Améliorer la hiérarchie des groupes.

Topbar:

- Garder une barre claire, fine, utile.
- Aligner langue, profil, recherche et actions.
- Supprimer bordures parasites.
- Éviter les icônes sans fonction.

### 4. Standardiser Cards, Tables Et Formulaires

Cards:

- mêmes radius/paddings;
- shadows sobres;
- titres compacts;
- pas de cards imbriquées.

Tables:

- densité ERP;
- en-têtes sobres;
- badges cohérents;
- actions alignées;
- empty states propres.

Formulaires:

- labels cohérents;
- sections plus courtes;
- aides contextuelles utiles;
- erreurs lisibles;
- boutons primaires/secondaires standardisés.

## Priorité P2 - Expérience SaaS Complète

### 5. Refaire La Landing Page

Objectif: passer d'une landing MVP à une landing SaaS crédible.

À améliorer:

- hero plus premium;
- screenshots réels du dashboard;
- preuves sociales ou cas d'usage;
- section pricing plus crédible;
- CTA clair;
- mobile plus propre;
- texte moins dense.

À éviter:

- blocs trop abstraits;
- excès de gradients;
- sections trop marketing sans preuve produit.

### 6. Repenser Le Portail Parent

Objectif: interface simple, rassurante, centrée sur l'enfant.

Écran idéal:

- sélecteur enfant clair;
- résumé paiement;
- prochaine échéance;
- dernières présences;
- dernier bulletin;
- annonces récentes;
- bouton PDF visible.

Ton UI:

- moins admin;
- plus humain;
- hiérarchie très simple.

### 7. Repenser Le Portail Staff

Objectif: interface opérationnelle, rapide, dense.

Écran idéal:

- planning du jour;
- classes assignées;
- notes à saisir;
- présences à prendre;
- fiche de paie récente;
- actions rapides.

Ton UI:

- utilitaire;
- peu décoratif;
- navigation rapide.

### 8. Refaire PDF Et Emails

PDF bulletin:

- logo et identité école;
- en-tête institutionnel;
- tableau notes propre;
- moyenne/rang/mention lisibles;
- signature direction;
- footer.

PDF paie:

- layout administratif;
- sections salaire brut, retenues, net;
- statut clair;
- période visible.

Emails:

- un template partagé;
- logo;
- titre;
- contenu;
- CTA;
- footer;
- style léger compatible clients email.

## Priorité P3 - Finition Produit

### 9. Responsive Et QA Visuelle

Tester:

- `/`
- `/admin`
- `/parent/parent-dashboard`
- `/staff/staff-dashboard`
- pages paiement;
- pages bulletin;
- PDF.

Viewports:

- mobile 390px;
- tablette 768px;
- desktop 1440px.

À vérifier:

- pas de texte coupé;
- pas de boutons qui débordent;
- tables scrollables;
- sidebar utilisable;
- topbar propre.

### 10. Microcopy Et Langues

À faire:

- uniformiser français admin;
- supprimer les restes anglais visibles;
- centraliser les labels fréquents dans `lang/fr`;
- préparer `lang/ar` et `lang/en` plus tard;
- éviter les libellés trop longs dans menus/cards.

Exemples:

- `Overdue Payments` -> `Paiements en retard`
- `Collection Rate` -> `Taux d'encaissement`
- `Pay slip finalized` -> `Fiche de paie finalisée`

### 11. Dark Mode

Décision à prendre:

- soit désactiver proprement le dark mode;
- soit le finir complètement.

Pour une bêta MVP, recommandation:

- désactiver visuellement le dark mode si la finition est partielle.

## Plan D'Implémentation Recommandé

1. Créer une branche dédiée: `design-system-pass`.
2. Partir de `fix-main-one-install-hardening` si l'objectif est le futur `main`.
3. Extraire le CSS admin vers un vrai asset.
4. Extraire `portal-theme` vers un asset.
5. Créer les tokens design.
6. Harmoniser boutons/cards/tables/forms.
7. Refaire landing avec assets Vite.
8. Refaire parent dashboard.
9. Refaire staff dashboard.
10. Refaire PDF bulletin et paie.
11. Refaire emails.
12. Faire QA screenshots mobile/desktop.
13. Mettre à jour le score design.

## Tests Et Validation

Commandes:

```bash
composer validate --strict
php artisan test
php artisan route:list
npm run build
php artisan optimize:clear
```

Validation visuelle:

- capture desktop et mobile pour chaque panel;
- vérifier login admin/parent/staff;
- vérifier PDF bulletin;
- vérifier PDF paie;
- vérifier landing sans CDN externe inutile.

## Critères D'Acceptation

- Plus de gros CSS inline dans `AdminPanelProvider`.
- Plus de styles globaux dispersés sans raison.
- Tables et forms cohérents dans admin.
- Portail parent plus simple et lisible.
- Portail staff orienté actions.
- Landing crédible avec vrais signaux produit.
- PDF propres à partager physiquement.
- Emails cohérents.
- Aucun élément UI inutile dans la topbar.
- Aucun texte mojibake visible.

## Recommandation Finale

La prochaine discussion devrait commencer par:

```text
Lis SAAS_DESIGN_IMPROVEMENT_PLAN.md et implémente la phase P1 sur une branche design-system-pass sans merger main et saas.
```

Priorité absolue: extraire le CSS, centraliser les tokens, harmoniser admin/parent/staff. C'est ce qui fera le plus monter la perception SaaS.
