<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginAssociatesmanagerConfig extends CommonDBTM {

   static protected $notable = true;

   public static function canView(): bool {
      return Session::haveRight('config', READ);
   }

   public static function canCreate(): bool {
      return Session::haveRight('config', UPDATE);
   }

   public static function getTypeName($nb = 0): string {
   return 'Configuration';
   }

   public function showConfigForm(): bool {
      global $CFG_GLPI;

      if (!Session::haveRight('config', READ)) {
         return false;
      }

      echo "<div class='center'>";
      echo "<form method='post' action='" . Plugin::getWebDir('associatesmanager') . "/front/config.form.php'>";

      echo "<table class='tab_cadre_fixe'>";
   echo "<tr><th colspan='2'>Configuration du plugin Associates Manager</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2'>";
   echo "<h3>Gestion des droits</h3>";
   echo "<p>Gérer les droits du plugin via les profils GLPI (Administration > Profils)</p>";
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
      global $DB, $CFG_GLPI;

      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr>";
   echo "<th>Profil</th>";
   echo "<th>Droits</th>";
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
         READ    => 'Lecture',
         CREATE  => 'Création',
         UPDATE  => 'Modification',
         PURGE   => 'Suppression',
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
            echo 'Aucun';
         }

         $root_doc = is_array($CFG_GLPI) && isset($CFG_GLPI['root_doc']) ? $CFG_GLPI['root_doc'] : '';
         echo " <a href='" . $root_doc . "/front/profile.form.php?id=" . $data['id'] . "' class='btn btn-sm btn-primary'>";
         echo "<i class='fas fa-edit'></i> Modifier";
         echo "</a>";

         echo "</td>";
         echo "</tr>";
      }

      echo "</table>";

      echo "<div class='center' style='margin-top: 20px;'>";
   echo "<p><strong>Droits disponibles : </strong></p>";
      echo "<ul style='text-align: left; display: inline-block;'>";
   echo "<li><strong>Lecture :</strong> Voir les associés, parts et historiques</li>";
   echo "<li><strong>Création :</strong> Ajouter des associés, parts et entrées d'historique</li>";
   echo "<li><strong>Modification :</strong> Modifier des associés, parts et historiques</li>";
   echo "<li><strong>Suppression :</strong> Supprimer des associés, parts et historiques</li>";
      echo "</ul>";
      echo "</div>";
   }
}
