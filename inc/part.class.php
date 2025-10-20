<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginAssociatesmanagerPart extends CommonDBTM {

   static $rightname = 'plugin_associatesmanager';

   static function getTypeName($nb = 0) {
      return _n('Part', 'Parts', $nb, 'associatesmanager');
   }

   static function getMenuName() {
      return self::getTypeName(Session::getPluralNumber());
   }

   static function getMenuContent() {
      $menu = [];
      $menu['title'] = self::getMenuName();
      $menu['page']  = Plugin::getWebDir('associatesmanager') . '/front/part.php';
      $menu['icon']  = 'fas fa-percentage';

      return $menu;
   }

   function defineTabs($options = []) {
      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('Log', $ong, $options);
      return $ong;
   }

   function showForm($ID, array $options = []) {
      if ($ID > 0) {
         $this->check($ID, READ);
      } else {
         $this->check(-1, CREATE);
      }

      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Label', 'associatesmanager') . " *</td>";
      echo "<td>";
      echo Html::input('libelle', ['value' => $this->fields['libelle'], 'size' => 50]);
      echo "</td>";

      echo "<td>" . __('Value', 'associatesmanager') . " *</td>";
      echo "<td>";
      echo Html::input('valeur', ['value' => $this->fields['valeur'], 'type' => 'number', 'step' => '0.0001']);
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }

   function prepareInputForAdd($input) {
      if (empty($input['libelle'])) {
         Session::addMessageAfterRedirect(__('Label is mandatory', 'associatesmanager'), false, ERROR);
         return false;
      }

      if (!isset($input['valeur']) || $input['valeur'] === '') {
         Session::addMessageAfterRedirect(__('Value is mandatory', 'associatesmanager'), false, ERROR);
         return false;
      }

      return $input;
   }

   function prepareInputForUpdate($input) {
      return $input;
   }

   function rawSearchOptions() {
      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'                 => '2',
         'table'              => $this->getTable(),
         'field'              => 'libelle',
         'name'               => __('Label', 'associatesmanager'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
      ];

      $tab[] = [
         'id'                 => '3',
         'table'              => $this->getTable(),
         'field'              => 'valeur',
         'name'               => __('Value', 'associatesmanager'),
         'datatype'           => 'decimal',
      ];

      return $tab;
   }

   /**
    * Get search URL for the itemtype
    */
   static function getSearchURL($full = true) {
      return Plugin::getWebDir('associatesmanager', $full) . '/front/part.php';
   }

   /**
    * Get form URL for the itemtype
    */
   static function getFormURL($full = true) {
      return Plugin::getWebDir('associatesmanager', $full) . '/front/part.form.php';
   }
}
