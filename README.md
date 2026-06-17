# School Management

ERP scolaire MVP basé sur Laravel 13 et Filament 5.

## Installation locale

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

## Mode testeurs physiques

Le projet est prévu pour des tests MVP avec des données fictives uniquement.

- Utiliser `APP_DEBUG=false` sur un environnement partagé.
- Garder `ENABLE_MVP_API=false` sauf besoin explicite.
- Ne pas versionner `.env.production`.
- Utiliser `.env.production.example` comme modèle sans secrets.
- Lancer `php artisan storage:link` si les uploads publics sont utilisés.
- Ne jamais utiliser de vrais élèves, vrais parents, vrais salaires ou vrais paiements pendant cette phase.

## Accès

- Admin: `/admin`
- Parents: `/parent`
- Personnel: `/staff`

Les comptes et données démo existants sont conservés. Ne lancez pas de reset de base sans sauvegarde préalable.

## Commandes utiles

```bash
php artisan route:list
php artisan test
composer validate --strict
npm run build
```

## Limites connues du MVP

- Les policies sont volontairement simples et orientées test MVP.
- L'API métier est désactivée par défaut via `ENABLE_MVP_API=false`.
- Le modèle financier reste simplifié: les retards sont basés sur les paiements `pending` échus, sans modèle complet de facturation.
- Le thème Filament contient encore une partie de CSS inline à extraire progressivement.
