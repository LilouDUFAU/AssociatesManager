<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginAssociatesmanagerConfig extends CommonDBTM {

   static protected $notable = true;

   static function canView() {
      return Session::haveRight('config', READ);
   }

   static function canCreate() {
      return Session::haveRight('config', UPDATE);
   }

   static function getTypeName($nb = 0) {
      return __('Configuration', 'associatesmanager');
   }

   function showConfigForm() {
      global $CFG_GLPI;

      if (!Session::haveRight('config', READ)) {
         return false;
      }

      echo "<div class='center'>";
      echo "<form method='post' action='" . Plugin::getWebDir('associatesmanager') . "/front/config.form.php'>";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>" . __('Associates Manager Configuration', 'associatesmanager') . "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2'>";
      echo "<h3>" . __('Rights Management', 'associatesmanager') . "</h3>";
      echo "<p>" . __('Manage plugin rights through GLPI profiles (Administration > Profiles)', 'associatesmanager') . "</p>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' class='center'>";
      $this->showProfileRights();
      echo "</td>";
      echo "</tr>";

      echo "</table>";

      Html::closeForm();
      echo "</div>";

      return true;
   }

   function showProfileRights() {
      global $DB;

      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr>";
      echo "<th>" . __('Profile') . "</th>";
      echo "<th>" . __('Rights') . "</th>";
      echo "</tr>";

      $iterator = $DB->request([
         'SELECT' => [
            'glpi_profiles.id',
            'glpi_profiles.name',
            'glpi_profilerights.rights'
         ],
         'FROM' => 'glpi_profiles',
         'LEFT JOIN' => [
            'glpi_profilerights' => [
               'ON' => [
                  'glpi_profilerights' => 'profiles_id',
                  'glpi_profiles' => 'id',
                  ['AND' => ['glpi_profilerights.name' => 'plugin_associatesmanager']]
               ]
            ]
         ],
         'ORDER' => 'glpi_profiles.name'
      ]);

      $rights_values = [
         READ    => __('Read'),
         CREATE  => __('Create'),
         UPDATE  => __('Update'),
         PURGE   => __('Delete'),
      ];

      foreach ($iterator as $data) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . $data['name'] . "</td>";
         echo "<td>";

         if ($data['rights']) {
            $active_rights = [];
            foreach ($rights_values as $right => $label) {
               if ($data['rights'] & $right) {
                  $active_rights[] = $label;
               }
            }
            echo implode(', ', $active_rights);
         } else {
            echo __('None');
         }

         echo " <a href='" . $CFG_GLPI['root_doc'] . "/front/profile.form.php?id=" . $data['id'] . "' class='btn btn-sm btn-primary'>";
         echo "<i class='fas fa-edit'></i> " . __('Edit');
         echo "</a>";

         echo "</td>";
         echo "</tr>";
      }

      echo "</table>";

      echo "<div class='center' style='margin-top: 20px;'>";
      echo "<p><strong>" . __('Available rights:', 'associatesmanager') . "</strong></p>";
      echo "<ul style='text-align: left; display: inline-block;'>";
      echo "<li><strong>" . __('Read') . ":</strong> " . __('View associates, parts and history', 'associatesmanager') . "</li>";
      echo "<li><strong>" . __('Create') . ":</strong> " . __('Add new associates, parts and history entries', 'associatesmanager') . "</li>";
      echo "<li><strong>" . __('Update') . ":</strong> " . __('Modify existing associates, parts and history', 'associatesmanager') . "</li>";
      echo "<li><strong>" . __('Delete') . ":</strong> " . __('Remove associates, parts and history', 'associatesmanager') . "</li>";
      echo "</ul>";
      echo "</div>";
   }
}
