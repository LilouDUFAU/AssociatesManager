# GestionAssociés – Plugin GLPI

[![GLPI Version](https://img.shields.io/badge/GLPI-v10.0.19+-blue.svg)](https://glpi-project.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4+-green.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPLv2+-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Status](https://img.shields.io/badge/Status-Stable-brightgreen.svg)]()

Le **Plugin Associates Manager** est un plugin avancé pour GLPI qui permet une gestion des associés liés aux fournisseurs. Il offre un suivi précis des parts sociales, une historisation des modifications, et s’intègre dans le menu **Gestion** sans créer de menu supplémentaire.

### ✨ Fonctionnalités principales

- **🔗 Liaison d’un associé à un fournisseur GLPI** 
- **📊 Suivi du nombre total de parts par fournisseur**
- **🕓 Historisation des parts par associé**
- **👤 Sélection d’un contact GLPI si l’associé est une personne physique**
- **🛡️ Intégration avec le système de droits du plugin de gestion des permissions**

## 📦 Installation

### Prérequis

- GLPI 10.0.19 ou supérieur
- PHP 7.4 ou supérieur  
- MySQL 5.7 ou supérieur
- Plugin [PluginRightsManager](https://github.com/LilouDUFAU/PluginRightsManager) installé et activé

### Méthode 1 : Installation depuis GitHub

```bash
cd /var/www/glpi/plugins
git clone https://github.com/LilouDUFAU/associatesmanager.git
chown -R www-data:www-data associatesmanager
chmod -R 755 associatesmanager
```

### Méthode 2 : Installation manuelle

1. Téléchargez la dernière release
2. Extrayez l'archive dans `/var/www/glpi/plugins/associatesmanager/`

### Activation

1. Connectez-vous à GLPI avec un compte super-administrateur
2. Allez dans **Configuration → Plugins**
3. Trouvez "Gestion Associés" et cliquez sur **Installer**
4. Cliquez sur **Activer**

## 🔐 Permissions

Ce plugin utilise le système de droits du plugin PluginRightsManager. Les utilisateurs doivent disposer du droit gestion_associés_access pour accéder aux fonctionnalités.

Exemple de vérification dans le code :
```php
if (!PluginPluginrightsmanagerRightsValidator::hasPluginAccess(Session::getLoginUserID(), 'gestion_associés', 'read')) {
   Html::displayRightError();
}
```

## 🚀 Utilisation
### Accès au plugin

Le plugin est accessible via : **Gestion → Associates Manager**

> ⚠️ **Important** : Seuls les utilisateurs ayant le droit **access** peuvent accéder au plugin.

### Gestion des associés
#### 1. Vue d'ensemble des associés
- Liste des associés avec recherche par nom ou fournisseur
- Affichage des informations principales : nom, fournisseur, nombre de parts
- Boutons : **Voir plus**, **Modifier**, **Supprimer**




#### 2. Types d'associés possibles

| Droit | Description |
|-------|-------------|
| **Personne** | Associé lié à un contact GLPI |
| **Autre** | Associé non lié à un contact GLPI |

## 🏗️ Architecture

### Structure des fichiers
```
📁 pluginrightsmanager/
├── 📄 setup.php
├── 📄 hook.php
├── 📄 README.md
├── 📁 inc/
│   ├── 📄 plugin_associatesmanager_associe.class.php
│   ├── 📄 plugin_associatesmanager_associefournisseur.class.php
│   ├── 📄 plugin_associatesmanager_associepart.class.php
├── 📁 front/
│   ├── 📄 associe.form.php
│   ├── 📄 associefournisseur.form.php
│   ├── 📄 associepart.form.php
├── 📁 ajax/
│   ├── 📄 associatesmanager.ajax.php
├── 📁 css/
│   └── 📄 associatesmanager.css
├── 📁 js/
│   └── 📄 associatesmanager.js
└── 📁 locales/
    ├── 📄 fr_FR.php
    └── 📄 en_GB.php
```

### Base de données

Le plugin crée 3 tables :
- `glpi_plugin_associatesmanager_associes` : Informations sur les associés
- `glpi_plugin_associatesmanager_associefournisseurs` : Lien entre associé et fournisseur
- `glpi_plugin_associatesmanager_associeparts` : Historique des parts par associé

## 🧠 Concepts clés
- **Modularité** : chaque entité est gérée via une classe dédiée
- **Historisation** : chaque modification de parts est enregistrée
- **Sécurité** : accès contrôlé via PluginRightsManager
- **Interopérabilité GLPI** : lien avec les contacts GLPI pour les personne physiques

## 📚 Documentation développeur
### Hooks disponibles
- `plugin_init_associatesmanager()`
- `plugin_associatesmanager_install()`
- `plugin_associatesmanager_uninstall()`

### Exemple de création d’un associé
```php
$associe = new PluginAssociatesmanagerAssocie();
$associe->fields['name'] = 'Jean Dupont';
$associe->fields['is_person'] = 1;
$associe->fields['contact_id'] = 42;
$associe->add();
```

## 📈 Roadmap
### À venir
- 🔄 Synchronisation automatique des contacts GLPI
- 📁 Export CSV des historiques de parts
- 🔔 Notifications sur modification de parts
- 🧩 Compatibilité avec d’autres plugins GLPI

## 🛠️ Dépannage

### Problèmes courants
**Plugin non visible dans le menu**
- Vérifiez que votre profil utilisateur à le droit d'accès au plugin
- Confirmez que le plugin est activé

**Erreurs JavaScript**
- Vérifiez la console navigateur
- Assurez-vous que jQuery est chargé

**Problèmes de droits**
- Vérifiez la table `glpi_plugin_pluginrightsmanager_rights`
- Testez avec un compte ayant tous les droits

### Logs

Les erreurs sont enregistrées dans les logs GLPI :
```
files/_log/php-errors.log
files/_log/sql-errors.log
```

## 🤝 Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. **Fork** le projet
2. **Créez** une branche pour votre fonctionnalité (`git checkout -b feature/nouvelle-fonctionnalite`)
3. **Committez** vos changements (`git commit -am 'Ajouter nouvelle fonctionnalité'`)
4. **Push** vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. **Ouvrez** une Pull Request

### Standards de code

- Respecter les conventions de codage GLPI
- Documenter les nouvelles fonctions
- Tester les modifications avant soumission
- Inclure les traductions FR/EN

## 📝 Changelog

### Version 1.0.0
- ✨ Première version stable
- 🔗 Liaison associés/fournisseurs
- 📊 Suivi et historisation des parts
- 🔐 Intégration PluginRightsManager

## 🐛 Signaler un bug

Si vous rencontrez un problème :

1. Vérifiez que le problème n'est pas déjà signalé dans les [Issues](../../issues)
2. Créez une nouvelle issue en incluant :
   - Version de GLPI
   - Version du plugin
   - Description détaillée du problème
   - Étapes pour reproduire
   - Logs d'erreur si disponibles

## 📄 Licence

Ce projet est sous licence **GPL v2+** - voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 👨‍💻 Auteur

**Lilou DUFAU** - [Votre GitHub](https://github.com/LilouDUFAU)

## 🙏 Remerciements

- Équipe GLPI pour le framework
- Communauté GLPI pour les retours et suggestions
- Contributeurs du projet

---

⭐ **N'hésitez pas à mettre une étoile si ce plugin vous a été utile !**
