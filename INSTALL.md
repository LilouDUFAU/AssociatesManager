# Guide d'installation détaillé - Associates Manager pour GLPI

## Méthode 1 : Installation manuelle (Recommandée pour préproduction)

### Prérequis
- Accès SSH au serveur GLPI
- Droits root ou sudo
- GLPI 11.0+ installé et fonctionnel

### Étapes d'installation

#### 1. Connexion au serveur
```bash
ssh user@votre-serveur-glpi
```

#### 2. Navigation vers le répertoire des plugins
```bash
cd /var/www/html/glpi/plugins
# ou selon votre installation :
# cd /usr/share/glpi/plugins
```

#### 3. Copie des fichiers du plugin

**Option A : Depuis une archive**
```bash
# Télécharger l'archive
wget https://votre-depot/associatesmanager.tar.gz

# Extraire
tar -xzf associatesmanager.tar.gz

# Renommer le dossier (important!)
mv glpi-plugin-associatesmanager associatesmanager
```

**Option B : Depuis le dépôt de développement**
```bash
# Copier depuis le dossier de développement
cp -r /tmp/cc-agent/58924725/project/glpi-plugin-associatesmanager ./associatesmanager
```

**Option C : Via Git (si disponible)**
```bash
git clone https://LilouDUFAU/associatesmanager.git
```

#### 4. Vérification de la structure
```bash
ls -la associatesmanager/
```

Vous devez voir :
```
associatesmanager/
├── front/          (pages web)
├── inc/            (classes PHP)
├── locales/        (traductions)
├── setup/          (configuration)
├── hook.php        (installation DB)
├── setup.php       (définition plugin)
└── README.md
```

#### 5. Définir les permissions

**Pour Apache (Ubuntu/Debian)**
```bash
chown -R www-data:www-data associatesmanager
chmod -R 755 associatesmanager
```

**Pour Apache (CentOS/RHEL)**
```bash
chown -R apache:apache associatesmanager
chmod -R 755 associatesmanager
```

**Pour Nginx**
```bash
chown -R nginx:nginx associatesmanager
chmod -R 755 associatesmanager
```

#### 6. Vérifier les permissions
```bash
ls -la associatesmanager/
# Tous les fichiers doivent appartenir à www-data (ou apache/nginx)
```

#### 7. Installation via l'interface GLPI

1. Ouvrez votre navigateur et connectez-vous à GLPI
2. Allez dans **Configuration > Plugins**
3. Recherchez **Associates Manager** dans la liste
4. Cliquez sur le bouton **Installer** (icône de téléchargement)
5. Attendez la confirmation "Plugin installé avec succès"
6. Cliquez sur le bouton **Activer** (icône de lecture)

#### 8. Configuration des droits

1. Allez dans **Administration > Profils**
2. Pour chaque profil concerné :
   - Cliquez sur le profil (ex: Super-Admin)
   - Cherchez **Associates Manager** dans la liste des droits
   - Cochez les droits appropriés :
     - ✅ **Lecture** pour consulter
     - ✅ **Création** pour ajouter
     - ✅ **Mise à jour** pour modifier
     - ✅ **Suppression** pour supprimer
   - Cliquez sur **Sauvegarder**

#### 9. Vérification de l'installation

1. Vérifiez que les tables ont été créées :
```bash
mysql -u glpi_user -p glpi_db
```

```sql
SHOW TABLES LIKE 'glpi_plugin_associatesmanager%';
```

Vous devez voir :
```
glpi_plugin_associatesmanager_associates
glpi_plugin_associatesmanager_parts
glpi_plugin_associatesmanager_configs
```

2. Vérifiez l'accès au plugin :
   - Menu **Administration** doit contenir **Associates Manager**
   - Les fiches **Fournisseurs** doivent avoir un onglet **Associés**

## Méthode 2 : Installation via FTP/SFTP

### Pour ceux sans accès SSH

#### 1. Préparer le plugin localement
- Téléchargez le dossier `glpi-plugin-associatesmanager`
- Renommez-le en `associatesmanager`

#### 2. Connexion FTP/SFTP
- Utilisez FileZilla, WinSCP ou votre client FTP préféré
- Connectez-vous à votre serveur

#### 3. Upload du plugin
- Naviguez vers `/var/www/html/glpi/plugins/`
- Uploadez le dossier `associatesmanager`
- Assurez-vous que tous les sous-dossiers sont transférés

#### 4. Définir les permissions via FTP
- Sélectionnez le dossier `associatesmanager`
- Clic droit > Permissions
- Définissez : `755` pour les dossiers, `644` pour les fichiers

#### 5. Suivre les étapes 7-9 de la Méthode 1

## Vérification post-installation

### Tests fonctionnels

#### Test 1 : Créer un associé
1. Allez dans **Administration > Associates Manager > Associés**
2. Cliquez sur **+**
3. Remplissez le formulaire
4. Enregistrez

#### Test 2 : Créer une part
1. Allez dans **Administration > Associates Manager > Parts**
2. Créez un type de part
3. Vérifiez qu'elle apparaît dans la liste

#### Test 3 : Attribuer des parts
1. Ouvrez la fiche d'un associé
2. Onglet **Historique des Parts**
3. Ajoutez une attribution
4. Vérifiez le calcul de la valeur totale

#### Test 4 : Liaison avec les fournisseurs
1. Allez dans **Gestion > Fournisseurs**
2. Ouvrez une fiche fournisseur
3. Vérifiez la présence de l'onglet **Associés**

#### Test 5 : Création automatique de contact
1. Créez un associé de type "Personne" sans contact
2. Vérifiez qu'un contact a été créé automatiquement
3. Vérifiez que le contact est lié au fournisseur

## Dépannage

### Le plugin n'apparaît pas dans la liste

**Vérifications :**
```bash
# Le dossier existe ?
ls -la /var/www/html/glpi/plugins/associatesmanager

# Les permissions sont correctes ?
ls -la /var/www/html/glpi/plugins/

# Le fichier setup.php est présent ?
cat /var/www/html/glpi/plugins/associatesmanager/setup.php
```

### Erreur lors de l'installation

**Consulter les logs GLPI :**
```bash
tail -f /var/www/html/glpi/files/_log/php-errors.log
tail -f /var/www/html/glpi/files/_log/sql-errors.log
```

### Erreur de permissions

**Réappliquer les permissions :**
```bash
cd /var/www/html/glpi/plugins
chown -R www-data:www-data associatesmanager
chmod -R 755 associatesmanager
```

### Les tables ne sont pas créées

**Vérifier la connexion MySQL :**
```bash
mysql -u root -p -e "USE glpi_db; SHOW TABLES LIKE 'glpi_plugin_associatesmanager%';"
```

**Créer manuellement les tables :**
```bash
# Désinstaller le plugin via l'interface GLPI
# Puis réinstaller
```

### Le menu n'apparaît pas

**Vérifier les droits du profil :**
1. Administration > Profils > [Votre profil]
2. Cherchez "Associates Manager"
3. Activez au minimum le droit "Lecture"

## Mise à jour du plugin

### Étapes de mise à jour

1. **Sauvegarder les données**
```bash
mysqldump -u root -p glpi_db \
   glpi_plugin_associatesmanager_associates \
   glpi_plugin_associatesmanager_parts \
   > backup_associatesmanager.sql
```

2. **Désactiver le plugin**
   - Configuration > Plugins
   - Cliquer sur "Désactiver"

3. **Remplacer les fichiers**
```bash
cd /var/www/html/glpi/plugins
rm -rf associatesmanager.old
mv associatesmanager associatesmanager.old
# Copier la nouvelle version
cp -r /chemin/nouvelle-version/associatesmanager ./
chown -R www-data:www-data associatesmanager
```

4. **Réactiver le plugin**
   - Configuration > Plugins
   - Cliquer sur "Activer"

## Désinstallation

### Désinstallation complète

1. **Via l'interface GLPI**
   - Configuration > Plugins
   - Désactiver "Associates Manager"
   - Cliquer sur "Désinstaller"

2. **Supprimer les fichiers**
```bash
rm -rf /var/www/html/glpi/plugins/associatesmanager
```

3. **Vérifier la suppression des tables**
```sql
SHOW TABLES LIKE 'glpi_plugin_associatesmanager%';
-- Ne doit rien retourner
```

## Support

En cas de problème :
1. Consultez les logs GLPI
2. Vérifiez la version de GLPI (doit être >= 11.0)
3. Vérifiez la version de PHP (doit être >= 8.1)
4. Consultez la documentation GLPI officielle

## Informations complémentaires

- Documentation GLPI : https://glpi-project.org/documentation/
- Forum GLPI : https://forum.glpi-project.org/
- Version minimale GLPI : 11.0.0
- Licence : GPLv3+
