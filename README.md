# Ina Zaoui

## Refactorisez le code d'un site pour l'optimiser

Ce projet est le 15ème projet du parcours développeur PHP/Symfony d'OpenClassrooms. L'objectif est de mettre en oeuvre divers aspects du développement web et de l'assurance qualité logicielle en utilisant le framework Symfony.

## Description du projet

Site web pour la photographe Ina Zaoui spécialisée dans les photos de paysages du monde entier.

## Pré-requis

* PHP >= 8.1
* Composer
* Extension PHP Xdebug

## Installation

### Composer

Dans un premier temps, installer les dépendances :

```bash
composer install
```

## Configuration

Créer le fichier `.env.local` et configurer l'accès à la base de données. Exemple pour une base de données MySQL :

DATABASE_URL="mysql://root:Password@127.0.0.1:3306/inazaoui?serverVersion=10.4.32-MariaDB&charset=utf8mb4"

### Base de données

#### Supprimer la base de données

```bash
symfony console doctrine:database:drop --force --if-exists
```

#### Créer la base de données

```bash
symfony console doctrine:database:create
```

#### Exécuter les migrations

```bash
symfony console make:migration
symfony console doctrine:migrations:migrate -n
```

#### Charger les fixtures

```bash
symfony console doctrine:fixtures:load -n 
```

### Tests

#### Exécuter les tests

```bash
symfony php bin/phpunit
```

#### Création rapport de couverture du code

```bash
symfony php bin/phpunit --coverage-html public/test-coverage
```

#### Exécuter les tests PHPStan

```bash
vendor/bin/phpstan analyse --memory-limit=512M
```

### Serveur web

```bash
symfony serve
```
