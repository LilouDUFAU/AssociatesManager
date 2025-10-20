# GestionAssociés – Plugin GLPI

[![GLPI Version](https://img.shields.io/badge/GLPI-v10.0.19+-blue.svg)](https://glpi-project.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4+-green.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPLv2+-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Status](https://img.shields.io/badge/Status-Stable-brightgreen.svg)]()


Le **Plugin Associates Manager** est un plugin avancé pour GLPI (v10.0+ recommandé) permettant la gestion complète des associés liés aux fournisseurs, le suivi des parts sociales, l'historique des modifications, et l'intégration native dans le menu **Administration**.


### ✨ Fonctionnalités principales
- Gestion des associés (personnes ou sociétés) liés à un fournisseur
- Gestion des parts sociales et historique d'attribution
- Liaison automatique avec les contacts GLPI pour les personnes physiques
- CRUD complet pour associés, parts, historique
- Redirections et confirmations visuelles après chaque action
- Droits fins par profils GLPI (lecture, création, modification, suppression)
- Support multilingue (français)


## 📦 Installation

### Prérequis
- GLPI 10.0+ recommandé
- PHP 7.4+ (ou 8.1+ selon version GLPI)
- MySQL 5.7+ ou MariaDB

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
3. Installer le plugin puis l'activer
4. Vous trouverez le plugin dans le menu `Administration`

### Gestion des associés
#### 1. Vue d'ensemble des associés
- Liste des associés avec recherche par nom ou fournisseur
- Affichage des informations principales : nom, fournisseur, nombre de parts

### Base de données
Le plugin crée 3 tables principales :
- `glpi_plugin_associatesmanager_associates` : Informations sur les associés
- `glpi_plugin_associatesmanager_parts` : Définition des types de parts
- `glpi_plugin_associatesmanager_partshistory` : Historique des attributions de parts

#### 2. Types d'associés possibles

| Droit | Description |
|-------|-------------|
| **Personne physique** | Associé lié à un contact GLPI |
| **Autre** | Associé non lié à un contact GLPI (ex. entreprise) |

## 🏗️ Architecture

### Structure des fichiers
```
📁 associatesmanager/
├── 📄 AUTHORS.txt
├── 📄 CHANGELOG.md              → changement par version
├── 📄 hook.php
├── 📄 INSTALL.md                → guide installation
├── 📄 README.md                 → ce que vous êtes en train de lire
├── 📄 setup.php
├── 📄 USER_GUIDE.md             → guide utilisateur 
├── 📁 front/
│   ├── 📄 associate.form.php
│   ├── 📄 associate.php
│   ├── 📄 config.form.php
│   ├── 📄 part.form.php
│   ├── 📄 part.php
│   ├── 📄 partshistory.form.php
│   └── 📄 partshistory.php
├── 📁 inc/
│   ├── 📄 associate.class.php
│   ├── 📄 config.class.php
│   ├── 📄 menu.class.php
│   ├── 📄 part.class.php
│   └── 📄 partshistory.class.php
└── 📁 locale/
   └── 📄 fr_FR.po
```



## 🧠 Concepts clés
- **Modularité** : chaque entité est gérée via une classe dédiée
- **Historisation** : chaque modification de parts est enregistrée
- **Interopérabilité GLPI** : lien avec les contacts GLPI pour les personne physiques et avec les fournisseurs (pour lier fournisseur et associés)

## 📚 Documentation développeur
### Hooks disponibles
```php
// faire
```

### Exemple de création d’un associé
```php
// faire
```

## 📈 Roadmap
### À venir
- 🔄 Synchronisation automatique des contacts GLPI
- 📁 Export CSV des historiques de parts
- 🔔 Notifications sur modification de parts
- 🧩 Compatibilité avec d’autres plugins GLPI

En cas de problème :
- Vérifiez les logs GLPI : `files/_log/`
- Vérifiez les permissions sur le dossier du plugin et le cache GLPI
- Videz le cache GLPI si besoin
- Consultez la documentation GLPI officielle
- Confirmez que le plugin est activé

**Problèmes de droits**
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
- Inclure les traductions FR

## 📝 Changelog
Consulter le fichier [CHANGELOG.md](./CHANGELOG.MD)

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

**Lilou DUFAU** - [Lilou DUFAU](https://github.com/LilouDUFAU)

## 🙏 Remerciements

- Équipe GLPI pour le framework
- Communauté GLPI pour les retours et suggestions
- Contributeurs du projet

---

⭐ **N'hésitez pas à mettre une étoile si ce plugin vous a été utile !**
