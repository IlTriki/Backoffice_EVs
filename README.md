# Electric Vehicles Webshop Backoffice

## Installation

### 1. Cloner le repository

```bash
git clone https://github.com/IlTriki/Backoffice_EVs.git
cd Backoffice_EVs
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Créer la base de données et exécuter les migrations

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 4. Charger les fixtures

```bash
php bin/console doctrine:fixtures:load
```

### 5. Compiler le CSS Tailwind

```bash
php bin/console tailwind:init
php bin/console tailwind:build
```

### 6. Démarrer le serveur Symfony

```bash
symfony local:server:start
```

## Fonctionnalités

- Animation de goat dans la page d'accueil en cliquant sur les fleches
- Création et connexion d'un compte
- Gestion des produits (créer, lire, mettre à jour, supprimer)
- Gestion des clients (créer, lire, mettre à jour, supprimer)
- Rôles utilisateurs (Admin, Manager, User)
- Exportation des produits en CSV
- Dark mode

## Rôles utilisateurs

- **Admin**: Accès à toutes les fonctionnalités
- **Manager**: Peut gérer les clients et les produits
- **User**: Peut voir les produits

## Exécuter les tests

```bash
./vendor/bin/phpunit
```

## Commandes de la console

Créer un nouveau client:

```bash
php bin/console app:create-client
```

Importer des produits depuis un fichier CSV:

```bash
php bin/console app:import-products path/to/file.csv
```
