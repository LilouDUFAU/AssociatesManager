# Associates Manager - Guide d'utilisation

## Vue d'ensemble

Le plugin **Associates Manager** permet de gérer les associés et leurs parts dans GLPI.

## Fonctionnalités

### 1. Gestion des Associés

Accédez à la page des associés via : **Administration → Associates Manager → Associates**

#### Créer un nouvel associé
1. Cliquez sur le bouton **"Nouveau"** (avec l'icône +) en haut de la page
2. Remplissez le formulaire :
   - **Nom** * (obligatoire)
   - **Type** * : Personne ou Société (obligatoire)
   - **Fournisseur** * (obligatoire)
   - **Contact** : Lien vers un contact existant
   - **Email**
   - **Téléphone**
   - **Adresse complète** : Adresse, Code postal, Ville, État, Pays

3. Cliquez sur **"Ajouter"**

> **Note** : Si vous créez un associé de type "Personne" sans contact lié, un contact sera automatiquement créé et associé au fournisseur.

#### Visualiser les associés d'un fournisseur
1. Allez sur la fiche d'un fournisseur
2. Cliquez sur l'onglet **"Associates"**
3. Vous verrez la liste de tous les associés liés à ce fournisseur

### 2. Gestion des Parts

Accédez à la page des parts via : **Administration → Associates Manager → Parts**

#### Créer une nouvelle part
1. Cliquez sur le bouton **"Nouveau"** en haut de la page
2. Remplissez :
   - **Libellé** * (obligatoire) : Nom de la part
   - **Valeur** * (obligatoire) : Valeur numérique décimale

3. Cliquez sur **"Ajouter"**

### 3. Historique des Parts

Accédez à l'historique via : **Administration → Associates Manager → Parts History**

#### Créer un nouvel historique
1. Cliquez sur le bouton **"Nouveau"** en haut de la page
2. Remplissez :
   - **Associé** * (obligatoire)
   - **Part** * (obligatoire)
   - **Nombre de parts** * (obligatoire)
   - **Date d'attribution**
   - **Date de fin**

3. Cliquez sur **"Ajouter"**

#### Visualiser l'historique d'un associé
1. Ouvrez la fiche d'un associé
2. Cliquez sur l'onglet **"Parts History"**
3. Vous verrez tout l'historique des parts attribuées à cet associé

## Permissions

Le plugin utilise un système de droits dédié : `plugin_associatesmanager`

Les droits disponibles :
- **Lecture (READ)** : Voir les données
- **Création (CREATE)** : Créer de nouveaux éléments
- **Mise à jour (UPDATE)** : Modifier des éléments existants
- **Suppression (DELETE)** : Supprimer des éléments
- **Purge (PURGE)** : Supprimer définitivement

> Les boutons "Nouveau" n'apparaissent que si vous avez le droit **CREATE**.

## Navigation

Le plugin ajoute un nouveau menu dans **Administration** :

```
Administration
  └── Associates Manager
       ├── Associates
       ├── Parts
       └── Parts History
```

## Support

Pour signaler un bug ou demander une fonctionnalité, veuillez contacter l'administrateur système.

---

**Version** : 1.0.4  
**Auteur** : Lilou DUFAU  
**Licence** : GPLv3+
