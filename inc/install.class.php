<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class Install {

   /**
    * Run installation: execute SQL schema and create default rights
    * @param Migration $migration
    * @return bool
    */
   public function install(Migration $migration) {
      global $DB;

      $migration->displayMessage("Installing Associates Manager plugin");

      // Execute SQL schema files found in install/mysql in lexicographic order.
      // This allows incremental schema files like plugin_associatesmanager_1.0_empty.sql,
      // plugin_associatesmanager_2.0_schema.sql, etc. Each file should be idempotent
      // (use IF NOT EXISTS / ALTER ... statements).
      $sqlDir = __DIR__ . '/../install/mysql';
      if (is_dir($sqlDir)) {
         $files = scandir($sqlDir);
         sort($files, SORT_STRING);
         foreach ($files as $file) {
            // Skip uninstall and non-sql files
            if (stripos($file, 'plugin_associatesmanager_') !== 0) {
               continue;
            }
            if (stripos($file, 'uninstall') !== false) {
               continue;
            }
            $path = $sqlDir . '/' . $file;
            if (!is_readable($path) || pathinfo($path, PATHINFO_EXTENSION) !== 'sql') {
               continue;
            }
            $migration->displayMessage('Applying SQL file: ' . $file);
            if (!$DB->runFile($path)) {
               $migration->displayWarning("Error executing $file: " . $DB->error(), true);
               return false;
            }
         }

         // Apply additional idempotent SQL apply file (safe, run via runFile)
         $applyFile = $sqlDir . '/plugin_associatesmanager_2.0_apply.sql';
         if (is_readable($applyFile)) {
            $migration->displayMessage('Applying DB updates from ' . basename($applyFile));
            if (!$DB->runFile($applyFile)) {
               $migration->displayWarning('Error executing ' . basename($applyFile) . ': ' . $DB->error(), true);
               return false;
            }
         }
      } else {
         $migration->displayMessage("No SQL directory found, using programmatic creation.");
      }

      // Register/ensure profile rights for central profiles
      // Use updateProfileRights which will create or update entries and avoids duplicate insert errors
      $rights = READ | UPDATE | CREATE | DELETE | PURGE;
      foreach (getAllDataFromTable('glpi_profiles') as $profile) {
         if (!empty($profile['interface']) && $profile['interface'] === 'central') {
            try {
               ProfileRight::updateProfileRights($profile['id'], ['plugin_associatesmanager' => $rights]);
            } catch (Throwable $e) {
               // If update fails for some reason, log a migration warning but continue
               if (isset($migration) && method_exists($migration, 'displayWarning')) {
                  $migration->displayWarning('Could not set profile rights for profile ' . $profile['id'] . ': ' . $e->getMessage(), true);
               }
            }
         }
      }

      $migration->displayMessage("Associates Manager installed successfully.");
      return true;
   }

   /**
    * Run uninstall: drop tables and remove profile rights
    * @return bool
    */
   public function uninstall() {
      global $DB;

      // Prefer using an uninstall SQL file to drop plugin tables when available
      $uninstallFile = __DIR__ . '/../install/mysql/plugin_associatesmanager_uninstall.sql';
      if (is_readable($uninstallFile)) {
         if (!$DB->runFile($uninstallFile)) {
            return false;
         }
      } else {
         $tables = [
            'glpi_plugin_associatesmanager_associates',
            'glpi_plugin_associatesmanager_parts',
            'glpi_plugin_associatesmanager_partshistories',
            'glpi_plugin_associatesmanager_configs'
         ];

         foreach ($tables as $table) {
            // Use doQuery which is acceptable for drop in uninstall context
            $DB->doQuery("DROP TABLE IF EXISTS `" . $DB->escape($table) . "`");
         }
      }

      if (class_exists('ProfileRight')) {
         $profileRight = new ProfileRight();
         foreach (getAllDataFromTable('glpi_profilerights', ['name' => 'plugin_associatesmanager']) as $profrights) {
            if (!empty($profrights['id'])) {
               $profileRight->delete(['id' => $profrights['id']]);
            }
         }
      }

      return true;
   }
}
