<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginAssociatesmanagerMenu extends CommonGLPI {

   static $rightname = 'plugin_associatesmanager';

   static function getMenuName() {
   return 'Gestion des associés';
   }

   static function getMenuContent() {
      global $CFG_GLPI;

      $menu = [];

      $menu['title'] = self::getMenuName();
      $menu['page']  = Plugin::getWebDir('associatesmanager') . '/front/associate.php';
      $menu['icon']  = 'fas fa-users';

      $menu['options']['associate'] = [
         'title' => PluginAssociatesmanagerAssociate::getTypeName(Session::getPluralNumber()),
         'page'  => Plugin::getWebDir('associatesmanager') . '/front/associate.php',
         'icon'  => 'fas fa-user-tie'
      ];

      $menu['options']['part'] = [
         'title' => PluginAssociatesmanagerPart::getTypeName(Session::getPluralNumber()),
         'page'  => Plugin::getWebDir('associatesmanager') . '/front/part.php',
         'icon'  => 'fas fa-percentage'
      ];

      $menu['options']['partshistory'] = [
         'title' => PluginAssociatesmanagerPartshistory::getTypeName(Session::getPluralNumber()),
         'page'  => Plugin::getWebDir('associatesmanager') . '/front/partshistory.php',
         'icon'  => 'fas fa-history'
      ];

      return $menu;
   }
}
