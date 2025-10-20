# Changelog - Associates Manager

Toutes les modifications importantes de ce projet seront documentées dans ce fichier.


## [1.0.5] - 2025-10-20

### Ajouté
- Affichage forcé des colonnes Nom, Email, Téléphone, Type, Fournisseur dans la recherche des associés
- Suppression des filtres par défaut dans la recherche des associés
- Redirections et confirmations visuelles après les actions CRUD
- Suppression du code mort/commenté (Event::log)
- Correction et documentation sur les permissions du cache GLPI

### Modifié
- Correction du nom de fichier/class partshistory
- Ajout de l'autoloader dans setup.php
- Remplacement de tous les `include` par `require` dans front/
- Mise à jour de la documentation de déploiement et utilisateur
- Les boutons "Nouveau" s'affichent uniquement si l'utilisateur a le droit CREATE

### Corrigé
- Problème de conformité CSRF (déclaration déplacée)
- Problème de cache GLPI (instructions de permissions)
- Problème d'entité manquante lors de la création d'un contact
- Problème de préférences utilisateur sur la recherche

## [1.0.4] - 2025-10-20

### Ajouté
- Bouton "Nouveau" sur la page de liste des associés (`/front/associate.php`)
- Bouton "Nouveau" sur la page de liste des parts (`/front/part.php`)
- Bouton "Nouveau" sur la page de liste de l'historique (`/front/partshistory.php`)
- Méthodes `getSearchURL()` et `getFormURL()` dans toutes les classes principales
- Guide utilisateur (USER_GUIDE.md)
- Ce fichier de changelog

### Modifié
- Les boutons "Nouveau" s'affichent uniquement si l'utilisateur a le droit CREATE

## [1.0.3] - 2025-10-20

### Ajouté
- Autoloader pour les classes du plugin dans `setup.php`
- Fichier de test `front/test_includes.php` pour diagnostiquer les problèmes

### Modifié
- Remplacement de `include` par `require` dans tous les fichiers front/
- Amélioration de la documentation de déploiement

### Corrigé
- Problème potentiel de chargement des classes GLPI

## [1.0.2] - 2025-10-20

### Corrigé
- Renommage du fichier `inc/parthistory.class.php` en `inc/partshistory.class.php`
- Correspondance entre le nom du fichier et le nom de la classe

## [1.0.1] - 2025-10-20

### Ajouté
- `Session::checkLoginUser()` dans tous les fichiers front/ pour la sécurité

### Corrigé
- Position de la déclaration CSRF dans `setup.php` (maintenant AVANT toute condition)
- Le plugin est maintenant conforme CSRF et peut être activé

## [1.0.0] - 2025-10-20

### Ajouté
- Version initiale du plugin
- Gestion des associés (personnes ou sociétés)
- Gestion des parts
- Historique des parts par associé
- Onglet "Associates" sur les fiches fournisseurs
- Onglet "Parts History" sur les fiches associés
- Menu dédié dans Administration
- Support multilingue (français)
- Droits d'accès dédiés

### Fonctionnalités principales
- CRUD complet pour les associés
- CRUD complet pour les parts
- CRUD complet pour l'historique des parts
- Création automatique de contact lors de l'ajout d'un associé de type "Personne"
- Recherche et filtres sur tous les éléments
