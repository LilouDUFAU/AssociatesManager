# GestionAssociés – Plugin GLPI

**GestionAssociés** est un plugin pour [GLPI](https://glpi-project.org/) permettant de gérer les associés liés aux fournisseurs. Il offre un suivi précis des parts sociales, une historisation des modifications, et s’intègre dans le menu **Gestion** sans créer de menu supplémentaire.

## 📦 Fonctionnalités

- Liaison d’un associé à un fournisseur GLPI
- Suivi du nombre total de parts par fournisseur
- Historisation des parts par associé
- Sélection d’un contact GLPI si l’associé est une personne physique
- Intégration avec le système de droits du plugin de gestion des permissions

## 🛠️ Installation

1. Copier le dossier `associatesmanager` dans le répertoire `plugins/` de votre instance GLPI.
2. Se rendre dans **Configuration > Plugins** et activer **GestionAssociés**.
3. Vérifier que le plugin de gestion des droits est également activé.

## 🔐 Permissions

Ce plugin utilise le système de droits du plugin [PluginRightsManager](https://github.com/LilouDUFAU/PluginRightsManager). Assurez-vous que les utilisateurs disposent des permissions nécessaires (`gestion_associés_access`) pour accéder aux fonctionnalités.

## 🧱 Structure des données

- `glpi_plugin_gestionassocies_associes` : informations de base sur les associés
- `glpi_plugin_gestionassocies_associefournisseurs` : lien entre associé et fournisseur
- `glpi_plugin_gestionassocies_associeparts` : historisation des parts

## 📋 Utilisation

- Accédez au menu **Gestion > Associés**.
- Créez un nouvel associé, liez-le à un fournisseur.
- Définissez le nombre de parts et suivez les modifications dans l’historique.

## 🧑‍💻 Développement

Ce plugin suit la structure standard GLPI :
