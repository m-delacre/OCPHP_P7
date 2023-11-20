# OCPHP_P7

*Ce projet fait partie de mon parcours de formation, l'objectif de ce projet est de créer une API qui utilise les token JWT pour l'authentification.* 

### Prérequis :

* Un serveur de bdd
* Composer
* Symfony CLI

  
## Installer le projet

Pour commencer, clonez ce projet avec la commande suivante :

```

 git clone https://github.com/m-delacre/OCPHP_P7

```

Ouvrez votre terminal favori et rendez-vous dans le dossier où se trouve le clone du projet. Puis entrez cette commande : 

```

 composer install

```

## Configurer le projet

Maintenant que le projet est installé, il faut le configurer.

À la racine du projet créer un fichier .env.local et ajoutez c'est lignes avec vos options :

```env
DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=8.0.32"
```
Ensuite créer la bdd avec :

```
php bin/console doctrine:database:create
```

Ensuite ajoutez les migrations récupérer dans ce repository 

```
php bin/console doctrine:migrations:migrate
```

Ajoutez les fixtures :

```
php bin/console doctrine:fixtures:load
```

## Lancer le projet

### Pour lancer le projet :

* Lancer votre serveur de bdd
* Lancer le serveur php avec :
  ```
  symfony server:start
  ```

## Versions

### Les versions utilisées pendant le développement :

* Wampserver: 3.3.0
* PHP: 8.0.26
* MySQL: 8.0.31
