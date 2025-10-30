<?php

define('PLUGIN_ASSOCIATESMANAGER_VERSION', '1.1.0');
// Schema version used for DB migrations (major.minor)
define('PLUGIN_ASSOCIATESMANAGER_SCHEMA_VERSION', '2.0');
define('PLUGIN_ASSOCIATESMANAGER_MIN_GLPI', '11.0.0');
define('PLUGIN_ASSOCIATESMANAGER_MAX_GLPI', '11.99.99');

// Autoloader for plugin classes
spl_autoload_register(function ($classname) {
   if (strpos($classname, 'PluginAssociatesmanager') === 0) {
      $classname = str_replace('PluginAssociatesmanager', '', $classname);
      $filename = __DIR__ . '/inc/' . strtolower($classname) . '.class.php';
      if (is_readable($filename)) {
         include_once $filename;
         return true;
      }
   }
   return false;
});

function plugin_init_associatesmanager() {
   global $PLUGIN_HOOKS;

   // CSRF compliance must be declared BEFORE any check
   $PLUGIN_HOOKS['csrf_compliant']['associatesmanager'] = true;

   $plugin = new Plugin();

   if ($plugin->isInstalled('associatesmanager') && $plugin->isActivated('associatesmanager')) {
      // Force plugin UI to French for all users if translations are available.
      // This binds the gettext domain for the plugin to the local `locale/` folder
      // and forces the locale to fr_FR where possible.
      $localeDir = __DIR__ . '/locale';
      if (function_exists('bindtextdomain')) {
         // Bind domain and set UTF-8
         bindtextdomain('associatesmanager', $localeDir);
         if (function_exists('bind_textdomain_codeset')) {
            bind_textdomain_codeset('associatesmanager', 'UTF-8');
         }
         textdomain('associatesmanager');
      }
      // Try to set the locale to French (best-effort)
      if (function_exists('setlocale')) {
         setlocale(LC_ALL, 'fr_FR.UTF-8', 'fr_FR', 'fr');
      }
      Plugin::registerClass('PluginAssociatesmanagerAssociate', [
         'addtabon' => ['Supplier']
      ]);

      Plugin::registerClass('PluginAssociatesmanagerPart');
      Plugin::registerClass('PluginAssociatesmanagerPartshistory');

   // Place the plugin under the 'Gestion' (management) menu instead of 'Administration'
   $PLUGIN_HOOKS['menu_toadd']['associatesmanager'] = ['management' => 'PluginAssociatesmanagerMenu'];

      if (Session::haveRight('plugin_associatesmanager', READ)) {
         $PLUGIN_HOOKS['menu_entry']['associatesmanager'] = 'front/associate.php';
      }

      $PLUGIN_HOOKS['config_page']['associatesmanager'] = 'front/config.form.php';

      $PLUGIN_HOOKS['item_add']['associatesmanager'] = [
         'PluginAssociatesmanagerAssociate' => ['PluginAssociatesmanagerAssociate', 'postItemAdd']
      ];
   }
}

function plugin_version_associatesmanager() {
   return [
   'name'           => 'Gestion des associés',
      'version'        => PLUGIN_ASSOCIATESMANAGER_VERSION,
      'author'         => 'Lilou DUFAU',
      'license'        => 'GPLv2+',
      'homepage'       => '',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_ASSOCIATESMANAGER_MIN_GLPI,
            'max' => PLUGIN_ASSOCIATESMANAGER_MAX_GLPI
         ]
      ]
   ];
}

function plugin_associatesmanager_check_prerequisites() {
   if (version_compare(GLPI_VERSION, PLUGIN_ASSOCIATESMANAGER_MIN_GLPI, 'lt')) {
      echo "This plugin requires GLPI >= " . PLUGIN_ASSOCIATESMANAGER_MIN_GLPI;
      return false;
   }
   if (version_compare(GLPI_VERSION, PLUGIN_ASSOCIATESMANAGER_MAX_GLPI, 'gt')) {
      echo "This plugin does not support GLPI > " . PLUGIN_ASSOCIATESMANAGER_MAX_GLPI;
      return false;
   }
   return true;
}

function plugin_associatesmanager_check_config() {
   return true;
}
