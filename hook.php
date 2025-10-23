<?php

function plugin_associatesmanager_install() {
   global $DB;

   if (!class_exists('Install')) {
      include_once __DIR__ . '/inc/install.class.php';
   }

   $schema = defined('PLUGIN_ASSOCIATESMANAGER_SCHEMA_VERSION') ? PLUGIN_ASSOCIATESMANAGER_SCHEMA_VERSION : '1.0';
   $migration = new Migration($schema);

   $installer = new Install();
   return $installer->install($migration);
}

function plugin_associatesmanager_uninstall() {
   // Delegate uninstall to Install class
   if (!class_exists('Install')) {
      include_once __DIR__ . '/inc/install.class.php';
   }

   $installer = new Install();
   return $installer->uninstall();
}
