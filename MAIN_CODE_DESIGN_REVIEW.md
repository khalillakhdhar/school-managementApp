# Code Review - Branche `main`

Date: 30 juin 2026  
Projet: `school-management`  
Branche évaluée: `fix-main-one-install-hardening`, issue de `main`  
Positionnement: version "one install", single-school, non-SaaS.

## Score global

Score initial de `main`: **7.4 / 10**  
Score après refactoring MVP: **8.4 / 10**

La branche est maintenant solide pour une bêta physique contrôlée: accès par rôle mieux verrouillés, PDF bulletin protégé par policy, API métier désactivée par défaut, encodage surveillé par test, CSS admin sorti du provider, landing migrée hors CDN Tailwind/Alpine, et tests élargis.

Ce n'est pas encore une version production longue durée: le design system reste partiellement inline, certains workflows métier vivent encore dans les Resources Filament, l'audit fonctionnel doit être élargi, et le reset de mot de passe devrait évoluer vers des liens signés/expirables.

## Corrections réalisées

| Sujet | Statut | Résultat |
| --- | --- | --- |
| `Resource::skipAuthorization()` | Corrigé | Retiré du code applicatif; Filament repasse par les policies. |
| PDF bulletin | Corrigé | `DocumentPdfController::bulletin()` utilise `Gate::authorize('view', $student)`. |
| Accès admin/parent/teacher | Corrigé | Tests ajoutés sur panel admin, PDF parent/teacher autorisé et refusé. |
| Encodage mojibake | Corrigé MVP | Smoke test ajouté sur fichiers visibles. |
| Paiements | Corrigé MVP | `pending_amount`, `paid_amount`, `overdue_amount`, `overdue_count` ajoutés. |
| Pivot services paiement | Corrigé/testé | Le pivot `payment_service.amount` est couvert par test. |
| CSS admin | Amélioré | Le gros style inline est extrait dans `resources/views/filament/admin-theme.blade.php`. |
| Landing CDN | Corrigé | Tailwind et Alpine passent par Vite/npm. |
| AccountService | Encadré | Le retour du mot de passe clair est documenté comme transitoire et non persistant. |

## Validation technique

Commandes validées:

```bash
php artisan test
composer validate --strict
php artisan route:list
npm run build
```

Résultat attendu après cette passe:

- Tests Laravel: 25 tests, 60 assertions.
- Composer: valide.
- Routes: chargement OK.
- Build frontend: Vite OK.
- API métier: désactivée par défaut via `ENABLE_MVP_API=false`.

## Points forts actuels

- Architecture Laravel/Filament claire avec trois panels: `/admin`, `/parent`, `/staff`.
- Policies présentes et réellement utilisées sur les zones sensibles.
- `Gate::before` garde le bypass admin sans désactiver l'autorisation Filament.
- Données démo et mots de passe démo préservés.
- Socle de tests utile pour rôles, PDF, paiements, audit, CNSS, notifications et encodage.
- Landing plus saine côté dépendances runtime.

## Ce qui reste à améliorer

### P1 - Avant bêta plus propre

1. **Design system**
   - Extraire progressivement les autres `<style>` inline des vues custom.
   - Centraliser les tokens UI: couleurs, radius, shadows, spacing, typo.
   - Harmoniser admin, parent, staff, PDF et emails.

2. **Reset password et création de compte**
   - Remplacer à moyen terme les mots de passe temporaires par liens de reset signés et expirables.
   - Ajouter un test prouvant qu'aucun secret n'est affiché hors `APP_ENV=local`.

3. **Audit fonctionnel**
   - Tracer explicitement: création compte, reset password, verify/unverify payment, finalisation paie, exports PDF sensibles.
   - Séparer audit technique et notifications utilisateur.

4. **Tests UI métier**
   - Couvrir les actions Filament sensibles.
   - Tester les exports PDF admin/parent/staff.
   - Tester `ENABLE_MVP_API=true` en plus du mode désactivé.

### P2 - Avant production longue durée

1. Déplacer davantage de workflows hors Resources Filament vers services dédiés.
2. Ajouter CI avec `php artisan test`, `composer validate`, `npm run build`, recherche mojibake.
3. Ajouter QA screenshots mobile/desktop pour `/`, `/admin`, `/parent`, `/staff`.
4. Préparer un modèle financier plus explicite si le produit dépasse le MVP: `Invoice` ou `BillingItem`.
5. Ajouter observabilité légère: metadata d'audit, acteur, IP, contexte métier.

## Score détaillé après correction

| Axe | Score | Commentaire |
| --- | ---: | --- |
| Architecture Laravel | 8.2/10 | Structure claire, services présents, dette workflows encore dans Resources. |
| Filament | 8/10 | Policies réactivées, panel admin mieux contrôlé, CSS admin extrait. |
| Sécurité MVP | 8.4/10 | PDF bulletin corrigé, API fermée par défaut, rôles mieux testés. |
| Tests | 8/10 | 25 tests utiles, reste actions Filament et parcours visuels. |
| Design/UI | 7.8/10 | Landing hors CDN, admin plus propre, mais styles inline encore nombreux. |
| Maintenabilité | 7.6/10 | Moins de logique/provider bloat, refactor services à poursuivre. |

## Conclusion

La branche est maintenant prête pour une bêta MVP contrôlée en version one-install.  
Score global recommandé: **8.4 / 10**.

Le prochain meilleur investissement n'est plus la sécurité basique, mais la maintenabilité: design system, workflows hors Resources, audit métier et tests UI plus proches des vrais parcours testeurs.
