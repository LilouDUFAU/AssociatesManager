<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginAssociatesmanagerPartshistory extends CommonDBTM {

   static $rightname = 'plugin_associatesmanager';

   static function getTypeName($nb = 0) {
   return ($nb > 1) ? 'Historique des parts' : 'Historique des parts';
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getType() == 'PluginAssociatesmanagerAssociate') {
         if ($_SESSION['glpishow_count_on_tabs']) {
            // history table stores associate id under plugin_associatesmanager_associates_id
            $nb = countElementsInTable(
               static::getTable(),
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

      // History is stored in the partshistories table; join to parts to get labels/values
      $iterator = $DB->request([
         'SELECT' => ['ph.*', 'p.libelle', 'p.valeur'],
         'FROM'  => 'glpi_plugin_associatesmanager_partshistories AS ph',
         'LEFT JOIN' => ['glpi_plugin_associatesmanager_parts AS p' => ['p.id' => 'ph.plugin_associatesmanager_parts_id']],
         'WHERE' => ['ph.plugin_associatesmanager_associates_id' => $associate_id],
         'ORDER' => ['ph.date_attribution DESC']
      ]);

      if ($canedit) {
         echo "<div class='center firstbloc'>";
         echo "<a class='btn btn-primary' href='" . Plugin::getWebDir('associatesmanager') . "/front/partshistory.form.php?associates_id=$associate_id'>";
         echo 'Ajouter une entrée d\'historique';
         echo "</a>";
         echo "</div>";
      }

      echo "<div class='center'>";
      if (count($iterator)) {
         echo "<table class='tab_cadre_fixehov'>";
         echo "<tr class='noHover'><th colspan='6'>" . self::getTypeName(count($iterator)) . "</th></tr>";
         echo "<tr>";
         echo "<th>Part</th>";
         echo "<th>Nombre de parts</th>";
         echo "<th>Valeur unitaire</th>";
         echo "<th>Valeur totale</th>";
         echo "<th>Date d'attribution</th>";
         echo "<th>Date de fin</th>";
         echo "</tr>";

         $total = 0;
         foreach ($iterator as $data) {
            // unit value may have been removed from parts table in older schema
            $unit_value = isset($data['valeur']) ? (float)$data['valeur'] : 0.0;
            $total_value = (float)$data['nbparts'] * $unit_value;
            $total += $total_value;

            echo "<tr class='tab_bg_1'>";
            // libelle may come from the joined parts table
            $label = $data['libelle'] ?? '';
            echo "<td>" . $label . "</td>";
            echo "<td>" . number_format($data['nbparts'], 4, ',', ' ') . "</td>";
            if ($unit_value != 0.0) {
               echo "<td>" . number_format($unit_value, 4, ',', ' ') . " €</td>";
               echo "<td>" . number_format($total_value, 2, ',', ' ') . " €</td>";
            } else {
               echo "<td>-</td>";
               echo "<td>-</td>";
            }
            echo "<td>" . Html::convDate($data['date_attribution']) . "</td>";
            echo "<td>" . Html::convDate($data['date_fin']) . "</td>";
            echo "</tr>";
         }

         echo "<tr class='tab_bg_2'>";
         echo "<td colspan='3' class='right'><strong>Total</strong></td>";
         echo "<td><strong>" . number_format($total, 2, ',', ' ') . " €</strong></td>";
         echo "<td colspan='2'></td>";
         echo "</tr>";

         echo "</table>";
      } else {
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th>Aucun historique de parts trouvé</th></tr>";
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

      // Use the dedicated history table for historical records
      // Must be static to match CommonDBTM::getTable() signature
      public static function getTable($classname = null) {
         return 'glpi_plugin_associatesmanager_partshistories';
      }

   function showForm($ID, array $options = []) {
      if ($ID > 0) {
         $this->check($ID, READ);
      } else {
         $this->check(-1, CREATE);
      }

      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
   echo "<td>Associé *</td>";
      echo "<td>";
        PluginAssociatesmanagerAssociate::dropdown([
          'name' => 'associates_id',
          'value' => $this->fields['associates_id'] ?? 0
      ]);
      echo "</td>";

   echo "<td>Part *</td>";
      echo "<td>";
      PluginAssociatesmanagerPart::dropdown([
         'name' => 'parts_id',
         'value' => $this->fields['parts_id'] ?? 0
      ]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
   echo "<td>Nombre de parts *</td>";
      echo "<td>";
      echo Html::input('nbparts', ['value' => $this->fields['nbparts'], 'type' => 'number', 'step' => '0.0001']);
      echo "</td>";

   echo "<td>Date d'attribution *</td>";
      echo "<td>";
      Html::showDateField('date_attribution', ['value' => $this->fields['date_attribution']]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
   echo "<td>Date de fin</td>";
      echo "<td>";
      Html::showDateField('date_fin', ['value' => $this->fields['date_fin']]);
      echo "</td>";
      echo "<td colspan='2'></td>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }

   function prepareInputForAdd($input) {
             // Require the standardized associates_id field
             if (empty($input['associates_id'])) {
         Session::addMessageAfterRedirect('L\'associé est obligatoire', false, ERROR);
         return false;
      }
            if (empty($input['parts_id'])) {
         Session::addMessageAfterRedirect('La part est obligatoire', false, ERROR);
         return false;
      }

         // Map standardized form fields to legacy DB column names used in history table
         if (isset($input['parts_id'])) {
            $input['plugin_associatesmanager_parts_id'] = $input['parts_id'];
            unset($input['parts_id']);
         }
         if (isset($input['associates_id'])) {
            $input['plugin_associatesmanager_associates_id'] = $input['associates_id'];
            unset($input['associates_id']);
         }

      if (!isset($input['nbparts']) || $input['nbparts'] === '') {
         Session::addMessageAfterRedirect('Le nombre de parts est obligatoire', false, ERROR);
         return false;
      }

      if (empty($input['date_attribution'])) {
         Session::addMessageAfterRedirect('La date d\'attribution est obligatoire', false, ERROR);
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
         'id'                 => '21021',
         'table'              => 'glpi_plugin_associatesmanager_associates',
         'field'              => 'name',
         'name'               => 'Associé',
         'datatype'           => 'dropdown',
         'linkfield'          => 'associates_id',
         'jointype'           => 'LEFT',
      ];

      $tab[] = [
         'id'                 => '21022',
         'table'              => static::getTable(),
         'field'              => 'libelle',
         'name'               => 'Part',
         'datatype'           => 'dropdown',
      ];

      $tab[] = [
         'id'                 => '21023',
         'table'              => static::getTable(),
         'field'              => 'nbparts',
         'name'               => 'Nombre de parts',
         'datatype'           => 'decimal',
      ];

      $tab[] = [
         'id'                 => '21024',
         'table'              => static::getTable(),
         'field'              => 'date_attribution',
         'name'               => 'Date d\'attribution',
         'datatype'           => 'date',
      ];

      $tab[] = [
         'id'                 => '21025',
         'table'              => static::getTable(),
         'field'              => 'date_fin',
         'name'               => 'Date de fin',
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
