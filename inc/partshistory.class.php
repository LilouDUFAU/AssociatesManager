<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginAssociatesmanagerPartshistory extends CommonDBTM {

   static $rightname = 'plugin_associatesmanager';

   static function getTypeName($nb = 0) {
      return _n('Parts History', 'Parts History', $nb, 'associatesmanager');
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getType() == 'PluginAssociatesmanagerAssociate') {
         if ($_SESSION['glpishow_count_on_tabs']) {
            $nb = countElementsInTable(
               $this->getTable(),
               ['plugin_associatesmanager_associates_id' => $item->getID()]
            );
            return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
         }
         return self::getTypeName(Session::getPluralNumber());
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'PluginAssociatesmanagerAssociate') {
         self::showForAssociate($item);
      }
      return true;
   }

   static function showForAssociate(PluginAssociatesmanagerAssociate $associate) {
      global $DB, $CFG_GLPI;

      $associate_id = $associate->getID();
      $canedit = Session::haveRight('plugin_associatesmanager', UPDATE);

      $iterator = $DB->request([
         'SELECT' => [
            'glpi_plugin_associatesmanager_partshistory.*',
            'glpi_plugin_associatesmanager_parts.libelle',
            'glpi_plugin_associatesmanager_parts.valeur'
         ],
         'FROM'  => 'glpi_plugin_associatesmanager_partshistory',
         'LEFT JOIN' => [
            'glpi_plugin_associatesmanager_parts' => [
               'ON' => [
                  'glpi_plugin_associatesmanager_partshistory' => 'plugin_associatesmanager_parts_id',
                  'glpi_plugin_associatesmanager_parts' => 'id'
               ]
            ]
         ],
         'WHERE' => ['plugin_associatesmanager_associates_id' => $associate_id],
         'ORDER' => ['date_attribution DESC']
      ]);

      if ($canedit) {
         echo "<div class='center firstbloc'>";
         echo "<a class='btn btn-primary' href='" . Plugin::getWebDir('associatesmanager') . "/front/partshistory.form.php?plugin_associatesmanager_associates_id=$associate_id'>";
         echo __('Add a parts entry', 'associatesmanager');
         echo "</a>";
         echo "</div>";
      }

      echo "<div class='center'>";
      if (count($iterator)) {
         echo "<table class='tab_cadre_fixehov'>";
         echo "<tr class='noHover'><th colspan='6'>" . self::getTypeName(count($iterator)) . "</th></tr>";
         echo "<tr>";
         echo "<th>" . __('Part', 'associatesmanager') . "</th>";
         echo "<th>" . __('Number of parts', 'associatesmanager') . "</th>";
         echo "<th>" . __('Unit Value', 'associatesmanager') . "</th>";
         echo "<th>" . __('Total Value', 'associatesmanager') . "</th>";
         echo "<th>" . __('Attribution Date', 'associatesmanager') . "</th>";
         echo "<th>" . __('End Date', 'associatesmanager') . "</th>";
         echo "</tr>";

         $total = 0;
         foreach ($iterator as $data) {
            $total_value = $data['nbparts'] * $data['valeur'];
            $total += $total_value;

            echo "<tr class='tab_bg_1'>";
            echo "<td>" . $data['libelle'] . "</td>";
            echo "<td>" . number_format($data['nbparts'], 4, ',', ' ') . "</td>";
            echo "<td>" . number_format($data['valeur'], 4, ',', ' ') . " €</td>";
            echo "<td>" . number_format($total_value, 2, ',', ' ') . " €</td>";
            echo "<td>" . Html::convDate($data['date_attribution']) . "</td>";
            echo "<td>" . Html::convDate($data['date_fin']) . "</td>";
            echo "</tr>";
         }

         echo "<tr class='tab_bg_2'>";
         echo "<td colspan='3' class='right'><strong>" . __('Total', 'associatesmanager') . "</strong></td>";
         echo "<td><strong>" . number_format($total, 2, ',', ' ') . " €</strong></td>";
         echo "<td colspan='2'></td>";
         echo "</tr>";

         echo "</table>";
      } else {
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th>" . __('No parts history found', 'associatesmanager') . "</th></tr>";
         echo "</table>";
      }
      echo "</div>";
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
      echo "<td>" . __('Associate', 'associatesmanager') . " *</td>";
      echo "<td>";
      PluginAssociatesmanagerAssociate::dropdown([
         'name' => 'plugin_associatesmanager_associates_id',
         'value' => $this->fields['plugin_associatesmanager_associates_id']
      ]);
      echo "</td>";

      echo "<td>" . __('Part', 'associatesmanager') . " *</td>";
      echo "<td>";
      PluginAssociatesmanagerPart::dropdown([
         'name' => 'plugin_associatesmanager_parts_id',
         'value' => $this->fields['plugin_associatesmanager_parts_id']
      ]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Number of parts', 'associatesmanager') . " *</td>";
      echo "<td>";
      echo Html::input('nbparts', ['value' => $this->fields['nbparts'], 'type' => 'number', 'step' => '0.0001']);
      echo "</td>";

      echo "<td>" . __('Attribution Date', 'associatesmanager') . " *</td>";
      echo "<td>";
      Html::showDateField('date_attribution', ['value' => $this->fields['date_attribution']]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('End Date', 'associatesmanager') . "</td>";
      echo "<td>";
      Html::showDateField('date_fin', ['value' => $this->fields['date_fin']]);
      echo "</td>";
      echo "<td colspan='2'></td>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }

   function prepareInputForAdd($input) {
      if (empty($input['plugin_associatesmanager_associates_id'])) {
         Session::addMessageAfterRedirect(__('Associate is mandatory', 'associatesmanager'), false, ERROR);
         return false;
      }

      if (empty($input['plugin_associatesmanager_parts_id'])) {
         Session::addMessageAfterRedirect(__('Part is mandatory', 'associatesmanager'), false, ERROR);
         return false;
      }

      if (!isset($input['nbparts']) || $input['nbparts'] === '') {
         Session::addMessageAfterRedirect(__('Number of parts is mandatory', 'associatesmanager'), false, ERROR);
         return false;
      }

      if (empty($input['date_attribution'])) {
         Session::addMessageAfterRedirect(__('Attribution date is mandatory', 'associatesmanager'), false, ERROR);
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
         'table'              => 'glpi_plugin_associatesmanager_associates',
         'field'              => 'name',
         'name'               => __('Associate', 'associatesmanager'),
         'datatype'           => 'dropdown',
      ];

      $tab[] = [
         'id'                 => '3',
         'table'              => 'glpi_plugin_associatesmanager_parts',
         'field'              => 'libelle',
         'name'               => __('Part', 'associatesmanager'),
         'datatype'           => 'dropdown',
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this->getTable(),
         'field'              => 'nbparts',
         'name'               => __('Number of parts', 'associatesmanager'),
         'datatype'           => 'decimal',
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => $this->getTable(),
         'field'              => 'date_attribution',
         'name'               => __('Attribution Date', 'associatesmanager'),
         'datatype'           => 'date',
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => $this->getTable(),
         'field'              => 'date_fin',
         'name'               => __('End Date', 'associatesmanager'),
         'datatype'           => 'date',
      ];

      return $tab;
   }

   /**
    * Get search URL for the itemtype
    */
   static function getSearchURL($full = true) {
      return Plugin::getWebDir('associatesmanager', $full) . '/front/partshistory.php';
   }

   /**
    * Get form URL for the itemtype
    */
   static function getFormURL($full = true) {
      return Plugin::getWebDir('associatesmanager', $full) . '/front/partshistory.form.php';
   }
}
