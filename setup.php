<?php

define('PLUGIN_ASSOCIATESMANAGER_VERSION', '1.0.6');
define('PLUGIN_ASSOCIATESMANAGER_MIN_GLPI', '10.0.0');

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
      Plugin::registerClass('PluginAssociatesmanagerAssociate', [
         'addtabon' => ['Supplier']
      ]);

      Plugin::registerClass('PluginAssociatesmanagerPart');
      Plugin::registerClass('PluginAssociatesmanagerPartshistory');

      $PLUGIN_HOOKS['menu_toadd']['associatesmanager'] = ['admin' => 'PluginAssociatesmanagerMenu'];

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
      'name'           => __('Associates Manager', 'associatesmanager'),
      'version'        => PLUGIN_ASSOCIATESMANAGER_VERSION,
      'author'         => 'Lilou DUFAU',
      'license'        => 'GPLv2+',
      'homepage'       => '',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_ASSOCIATESMANAGER_MIN_GLPI,
         ]
      ]
   ];
}

function plugin_associatesmanager_check_prerequisites() {
   if (version_compare(GLPI_VERSION, PLUGIN_ASSOCIATESMANAGER_MIN_GLPI, 'lt')) {
      echo "This plugin requires GLPI >= " . PLUGIN_ASSOCIATESMANAGER_MIN_GLPI;
      return false;
   }
   return true;
}

function plugin_associatesmanager_check_config() {
   return true;
}
