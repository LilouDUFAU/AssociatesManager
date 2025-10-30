<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginAssociatesmanagerAssociate extends CommonDBTM {

   static $rightname = 'plugin_associatesmanager';

   static function getTypeName($nb = 0) {
   return ($nb > 1) ? 'Associés' : 'Associé';
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getType() == 'Supplier') {
         if ($_SESSION['glpishow_count_on_tabs']) {
            global $DB;
            // Count distinct associates linked to this supplier via parts pivot
            // Use SELECT DISTINCT and count in PHP to avoid SQL expression parsing issues
            $supplier_id = (int)$item->getID();
            $it = $DB->request([
               'DISTINCT' => true,
               'SELECT' => ['associates_id'],
               'FROM'   => 'glpi_plugin_associatesmanager_parts',
               'WHERE'  => ['supplier_id' => $supplier_id]
            ]);
            $nb = 0;
            foreach ($it as $r) {
               $nb++;
            }
            return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
         }
         return self::getTypeName(Session::getPluralNumber());
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'Supplier') {
         self::showForSupplier($item);
      }
      return true;
   }

   static function showForSupplier(Supplier $supplier) {
      global $DB, $CFG_GLPI;

      $supplier_id = $supplier->getID();
      $canedit = Session::haveRight('plugin_associatesmanager', UPDATE);

      // Récupère le nombre de parts actif (date_fin IS NULL) par associé pour ce fournisseur
      // Récupère les parts actives pour ce fournisseur et calcule les totaux en PHP
      $assocParts = [];
      $totalParts = 0;
      $it = $DB->request([
         'FROM'  => 'glpi_plugin_associatesmanager_parts',
         'WHERE' => [
            'supplier_id' => $supplier_id,
            'date_fin'    => null
         ]
      ]);
      foreach ($it as $r) {
         $aid = $r['associates_id'];
         $nb  = isset($r['nbparts']) ? (int)$r['nbparts'] : 0;
         if (!isset($assocParts[$aid])) {
            $assocParts[$aid] = 0;
         }
         $assocParts[$aid] += $nb;
         $totalParts += $nb;
      }

      $assocIds = array_keys($assocParts);
      $iterator = [];
      if (count($assocIds)) {
         $iterator = $DB->request([
            'FROM'  => 'glpi_plugin_associatesmanager_associates',
            'WHERE' => ['id' => $assocIds]
         ]);
      }

      if ($canedit) {
         echo "<div class='spaced'>";
         echo "<a class='btn btn-primary' href='" . Plugin::getWebDir('associatesmanager') . "/front/part.form.php?supplier_id=$supplier_id'>";
         echo "<i class='fas fa-plus'></i> ";
         echo '<span>Ajouter une part</span>';
         echo "</a>";
         echo "</div>";
      }

      // Workaround: cancel AJAX requests to the tag plugin endpoint which may be unreachable
      // This prevents console errors like GET https://plugins/tag/ajax/get_entity_tags.php net::ERR_NAME_NOT_RESOLVED
      echo "<script type='text/javascript'>";
      echo "(function($){ if (!$) return; $.ajaxPrefilter(function(options){ try { if (options && options.url && options.url.indexOf('/plugins/tag/ajax/get_entity_tags.php') !== -1) { options.beforeSend = function(){ return false; }; } } catch(e) {} }); })(window.jQuery);";
      echo "</script>";

      echo "<div class='center'>";
      if (count($iterator)) {
         echo "<table class='tab_cadre_fixehov'>";
         echo "<tr class='noHover'><th colspan='8'>" . self::getTypeName(count($iterator)) . "</th></tr>";
         echo "<tr>";
         echo "<th>Nom</th>";
         echo "<th>Type</th>";
         echo "<th>Email</th>";
         echo "<th>Téléphone</th>";
         echo "<th>Ville</th>";
         echo "<th>Nombre de parts</th>";
         echo "<th>Part (%)</th>";
         echo "<th>Actions</th>";
         echo "</tr>";

         foreach ($iterator as $data) {
            echo "<tr class='tab_bg_1'>";
            echo "<td><a href='" . Plugin::getWebDir('associatesmanager') . "/front/associate.form.php?id=" . $data['id'] . "'>" . $data['name'] . "</a></td>";
            echo "<td>" . ($data['is_person'] ? 'Personne' : 'Société') . "</td>";
            echo "<td>" . $data['email'] . "</td>";
            echo "<td>" . $data['phonenumber'] . "</td>";
            echo "<td>" . $data['town'] . "</td>";
            // Nombre de parts pour cet associé (actif)
            $nb = isset($assocParts[$data['id']]) ? $assocParts[$data['id']] : 0;
            echo "<td class='left'>" . $nb . "</td>";
            // Pourcentage de parts représenté
            $pct = ($totalParts > 0) ? ($nb / $totalParts * 100) : 0;
            echo "<td class='left'>" . sprintf('%.1f', $pct) . "%</td>";
            echo "<td>";
            if ($canedit) {
               echo "<a href='" . Plugin::getWebDir('associatesmanager') . "/front/associate.form.php?id=" . $data['id'] . "'>";
               echo "<i class='fas fa-edit'></i>";
               echo "</a>";
            }
            echo "</td>";
            echo "</tr>";
         }
         echo "</table>";
      } else {
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th>Aucun associé trouvé</th></tr>";
         echo "</table>";
      }
      echo "</div>";
   }

   function defineTabs($options = []) {
      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginAssociatesmanagerPartshistory', $ong, $options);
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
   echo "<td>Nom *</td>";
      echo "<td>";
      echo Html::input('name', ['value' => $this->fields['name'], 'size' => 50]);
      echo "</td>";

   echo "<td>Type *</td>";
      echo "<td>";
      // Show a dropdown with explicit labels 'Person' / 'Company' instead of Yes/No
      $type_values = [
         1 => 'Personne',
         0 => 'Société'
      ];
   // Ensure default for new associate is 'Société' (is_person = 0)
   $default_is_person = ($ID > 0) ? $this->fields['is_person'] : 0;
   $p = ['name' => 'is_person', 'value' => $default_is_person];
   Dropdown::showFromArray($p['name'], $type_values, $p);
      echo "</td>";
      echo "</tr>";

   echo "<tr class='tab_bg_1'>";
   echo "<td>Matricule (N° Insee)</td>";
   echo "<td>";
   echo Html::input('matricule', ['value' => $this->fields['matricule'], 'size' => 30]);
   echo "</td>";

   echo "<td>Contact</td>";
   echo "<td>";
   Contact::dropdown(['name' => 'contacts_id', 'value' => $this->fields['contacts_id']]);
   echo "</td>";
   echo "</tr>";

      echo "<tr class='tab_bg_1'>";
   echo "<td>Email</td>";
      echo "<td>";
      echo Html::input('email', ['value' => $this->fields['email'], 'size' => 50]);
      echo "</td>";

   echo "<td>Téléphone</td>";
      echo "<td>";
      echo Html::input('phonenumber', ['value' => $this->fields['phonenumber'], 'size' => 30]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
   echo "<td>Adresse</td>";
      echo "<td>";
      echo "<textarea name='address' rows='3' cols='50'>" . $this->fields['address'] . "</textarea>";
      echo "</td>";

   echo "<td>Code postal</td>";
      echo "<td>";
      echo Html::input('postcode', ['value' => $this->fields['postcode'], 'size' => 10]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
   echo "<td>Ville</td>";
      echo "<td>";
      echo Html::input('town', ['value' => $this->fields['town'], 'size' => 50]);
      echo "</td>";

   echo "<td></td>";
   echo "<td></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
   echo "<td>Pays</td>";
      echo "<td>";
      echo Html::input('country', ['value' => $this->fields['country'], 'size' => 50]);
      echo "</td>";
      echo "<td colspan='2'></td>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }

   function prepareInputForAdd($input) {
      if (empty($input['name'])) {
         Session::addMessageAfterRedirect('Le nom est obligatoire', false, ERROR);
         return false;
      }

      // suppliers_id is no longer stored on associates; supplier linkage is managed via parts pivot.

      return $input;
   }

   function prepareInputForUpdate($input) {
      return $input;
   }

   /**
    * Display value override to show explicit labels for is_person field
    */
   function getValueToDisplay($field, $values, $options = []) {
      if ($field === 'is_person') {
         return ($values[$field]) ? 'Personne' : 'Société';
      }
      return parent::getValueToDisplay($field, $values, $options);
   }

   function post_addItem() {
      // If an associate is a person and no contact is linked, create a Contact in GLPI.
      // Do not create Contact_Supplier mapping here because suppliers are now managed
      // via the parts pivot table.
      if ($this->fields['is_person'] == 1 && $this->fields['contacts_id'] == 0) {
         $contact = new Contact();
         $contact_data = [
            'name'     => $this->fields['name'],
            'email'    => $this->fields['email'],
            'phone'    => $this->fields['phonenumber'],
            'address'  => $this->fields['address'],
            'postcode' => $this->fields['postcode'],
            'town'     => $this->fields['town'],
            'country'  => $this->fields['country']
         ];

         $contact_id = $contact->add($contact_data);
         if ($contact_id) {
            $this->update([
               'id'          => $this->fields['id'],
               'contacts_id' => $contact_id
            ]);
         }
      }
   }

   static function dropdown($options = []) {
      global $DB, $CFG_GLPI;

      $p = [
         'name'     => 'associates_id',
         'value'    => 0,
         'comments' => true,
         'entity'   => -1,
         'entity_sons' => false,
         'on_change' => '',
         'width'    => '',
      ];

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      $iterator = $DB->request([
         'SELECT' => ['id', 'name'],
         'FROM'   => 'glpi_plugin_associatesmanager_associates',
         'ORDER'  => 'name'
      ]);

      $values = [0 => Dropdown::EMPTY_VALUE];
      foreach ($iterator as $data) {
         $values[$data['id']] = $data['name'];
      }

      return Dropdown::showFromArray($p['name'], $values, $p);
   }

   function rawSearchOptions() {
      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'                 => '21001',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => 'Nom',
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
      ];

      $tab[] = [
         'id'                 => '21002',
         'table'              => $this->getTable(),
         'field'              => 'is_person',
         'name'               => 'Type',
         'datatype'           => 'dropdown',
         'values'             => [
            1 => 'Personne',
            0 => 'Société'
         ],
      ];

      $tab[] = [
         'id'                 => '21003',
         'table'              => $this->getTable(),
         'field'              => 'email',
         'name'               => 'Email',
         'datatype'           => 'email',
      ];

      $tab[] = [
         'id'                 => '21004',
         'table'              => $this->getTable(),
         'field'              => 'phonenumber',
         'name'               => 'Téléphone',
         'datatype'           => 'string',
      ];

      // Supplier search option removed: supplier linkage is now via the parts pivot table
      // and no longer stored on the associates table (suppliers_id was dropped).

      return $tab;
   }
}
