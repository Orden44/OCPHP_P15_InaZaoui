# Contribuer au projet Ina Zaoui

Merci pour l'intérêt que vous manifestez envers ce projet. Voici quelques bonnes pratiques à respecter.

## Pré-requis

Avant de débuter, veillez à avoir installé les outils suivants.

* PHP >= 8.1
* Composer
* Extension PHP Xdebug

## Comment contribuer

1. **Clonez le projet** : `git clone https://github.com/Orden44/OCPHP_P15_InaZaoui`
2. **Ouvrez votre projet cloné via votre éditeur de code**
3. **Créez une nouvelle branche** : `git checkout -b +nom de la branche`
4. **Développez** : Faites vos modifications.
5. **Envoyez vos modifications** : `git push origin +nom de la branche`

## Tests

Assurez-vous que tous les tests sont corrects :

```bash
symfony php bin/phpunit
```

```bash
vendor/bin/phpstan analyse --memory-limit=512M
```
