<?php

function plugin_associatesmanager_install() {
   global $DB;

   $migration = new Migration(100);

   $default_charset = DBConnection::getDefaultCharset();
   $default_collation = DBConnection::getDefaultCollation();
   $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

   $table_associates = 'glpi_plugin_associatesmanager_associates';
   if (!$DB->tableExists($table_associates)) {
      $query = "CREATE TABLE IF NOT EXISTS `$table_associates` (
         `id` int $default_key_sign NOT NULL AUTO_INCREMENT,
         `name` varchar(255) DEFAULT NULL,
         `is_person` tinyint NOT NULL DEFAULT '0',
         `address` text,
         `postcode` varchar(10) DEFAULT NULL,
         `town` varchar(255) DEFAULT NULL,
         `state` varchar(255) DEFAULT NULL,
         `country` varchar(255) DEFAULT NULL,
         `phonenumber` varchar(20) DEFAULT NULL,
         `email` varchar(255) DEFAULT NULL,
         `suppliers_id` int $default_key_sign NOT NULL DEFAULT '0',
         `contacts_id` int $default_key_sign NOT NULL DEFAULT '0',
         `date_creation` timestamp NULL DEFAULT NULL,
         `date_mod` timestamp NULL DEFAULT NULL,
         PRIMARY KEY (`id`),
         KEY `suppliers_id` (`suppliers_id`),
         KEY `contacts_id` (`contacts_id`),
         KEY `is_person` (`is_person`)
      ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
      $DB->query($query) or die($DB->error());
   }

   $table_parts = 'glpi_plugin_associatesmanager_parts';
   if (!$DB->tableExists($table_parts)) {
      $query = "CREATE TABLE IF NOT EXISTS `$table_parts` (
         `id` int $default_key_sign NOT NULL AUTO_INCREMENT,
         `libelle` varchar(255) NOT NULL,
         `valeur` decimal(15,4) NOT NULL DEFAULT '0.0000',
         `date_creation` timestamp NULL DEFAULT NULL,
         `date_mod` timestamp NULL DEFAULT NULL,
         PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
      $DB->query($query) or die($DB->error());
   }

   $table_partshistory = 'glpi_plugin_associatesmanager_partshistory';
   if (!$DB->tableExists($table_partshistory)) {
      $query = "CREATE TABLE IF NOT EXISTS `$table_partshistory` (
         `id` int $default_key_sign NOT NULL AUTO_INCREMENT,
         `plugin_associatesmanager_associates_id` int $default_key_sign NOT NULL DEFAULT '0',
         `plugin_associatesmanager_parts_id` int $default_key_sign NOT NULL DEFAULT '0',
         `nbparts` decimal(15,4) NOT NULL DEFAULT '0.0000',
         `date_attribution` date DEFAULT NULL,
         `date_fin` date DEFAULT NULL,
         `date_creation` timestamp NULL DEFAULT NULL,
         `date_mod` timestamp NULL DEFAULT NULL,
         PRIMARY KEY (`id`),
         KEY `plugin_associatesmanager_associates_id` (`plugin_associatesmanager_associates_id`),
         KEY `plugin_associatesmanager_parts_id` (`plugin_associatesmanager_parts_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
      $DB->query($query) or die($DB->error());
   }

   $table_config = 'glpi_plugin_associatesmanager_configs';
   if (!$DB->tableExists($table_config)) {
      $query = "CREATE TABLE IF NOT EXISTS `$table_config` (
         `id` int $default_key_sign NOT NULL AUTO_INCREMENT,
         `type` varchar(255) NOT NULL,
         `value` text,
         PRIMARY KEY (`id`),
         UNIQUE KEY `type` (`type`)
      ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
      $DB->query($query) or die($DB->error());
   }

   ProfileRight::addProfileRights(['plugin_associatesmanager']);

   foreach (getAllDataFromTable('glpi_profiles') as $profile) {
      $rights = READ | UPDATE | CREATE | DELETE | PURGE;
      if ($profile['interface'] == 'central') {
         ProfileRight::updateProfileRights($profile['id'], ['plugin_associatesmanager' => $rights]);
      }
   }

   return true;
}

function plugin_associatesmanager_uninstall() {
   global $DB;

   $tables = [
      'glpi_plugin_associatesmanager_associates',
      'glpi_plugin_associatesmanager_parts',
      'glpi_plugin_associatesmanager_partshistory',
      'glpi_plugin_associatesmanager_configs'
   ];

   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`");
   }

   $profileRight = new ProfileRight();
   foreach (getAllDataFromTable('glpi_profilerights', ['name' => 'plugin_associatesmanager']) as $profrights) {
      $profileRight->delete(['id' => $profrights['id']]);
   }

   return true;
}
