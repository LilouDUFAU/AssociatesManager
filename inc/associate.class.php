<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginAssociatesmanagerAssociate extends CommonDBTM {

   static $rightname = 'plugin_associatesmanager';

   static function getTypeName($nb = 0) {
      return _n('Associate', 'Associates', $nb, 'associatesmanager');
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getType() == 'Supplier') {
         if ($_SESSION['glpishow_count_on_tabs']) {
            $nb = countElementsInTable(
               $this->getTable(),
               ['suppliers_id' => $item->getID()]
            );
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

      $iterator = $DB->request([
         'FROM'  => 'glpi_plugin_associatesmanager_associates',
         'WHERE' => ['suppliers_id' => $supplier_id]
      ]);

      if ($canedit) {
         echo "<div class='center firstbloc'>";
         echo "<a class='btn btn-primary' href='" . Plugin::getWebDir('associatesmanager') . "/front/associate.form.php?suppliers_id=$supplier_id'>";
         echo __('Add an associate', 'associatesmanager');
         echo "</a>";
         echo "</div>";

         echo "<div class='center firstbloc'>";
         echo "<a class='btn btn-primary' href='" . Plugin::getWebDir('associatesmanager') . "/front/part.form.php?suppliers_id=$supplier_id'>";
         echo __('Add a part', 'associatesmanager');
         echo "</a>";
         echo "</div>";
      }

      echo "<div class='center'>";
      if (count($iterator)) {
         echo "<table class='tab_cadre_fixehov'>";
         echo "<tr class='noHover'><th colspan='6'>" . self::getTypeName(count($iterator)) . "</th></tr>";
         echo "<tr>";
         echo "<th>" . __('Name') . "</th>";
         echo "<th>" . __('Type', 'associatesmanager') . "</th>";
         echo "<th>" . __('Email') . "</th>";
         echo "<th>" . __('Phone') . "</th>";
         echo "<th>" . __('Town') . "</th>";
         echo "<th>" . __('Actions') . "</th>";
         echo "</tr>";

         foreach ($iterator as $data) {
            echo "<tr class='tab_bg_1'>";
            echo "<td><a href='" . Plugin::getWebDir('associatesmanager') . "/front/associate.form.php?id=" . $data['id'] . "'>" . $data['name'] . "</a></td>";
            echo "<td>" . ($data['is_person'] ? __('Person', 'associatesmanager') : __('Company', 'associatesmanager')) . "</td>";
            echo "<td>" . $data['email'] . "</td>";
            echo "<td>" . $data['phonenumber'] . "</td>";
            echo "<td>" . $data['town'] . "</td>";
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
         echo "<tr><th>" . __('No associate found', 'associatesmanager') . "</th></tr>";
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
      echo "<td>" . __('Name') . " *</td>";
      echo "<td>";
      echo Html::input('name', ['value' => $this->fields['name'], 'size' => 50]);
      echo "</td>";

      echo "<td>" . __('Type', 'associatesmanager') . " *</td>";
      echo "<td>";
      Dropdown::showYesNo('is_person', $this->fields['is_person']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Supplier') . " *</td>";
      echo "<td>";
      Supplier::dropdown(['name' => 'suppliers_id', 'value' => $this->fields['suppliers_id']]);
      echo "</td>";

      echo "<td>" . __('Contact') . "</td>";
      echo "<td>";
      Contact::dropdown(['name' => 'contacts_id', 'value' => $this->fields['contacts_id']]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Email') . "</td>";
      echo "<td>";
      echo Html::input('email', ['value' => $this->fields['email'], 'size' => 50]);
      echo "</td>";

      echo "<td>" . __('Phone') . "</td>";
      echo "<td>";
      echo Html::input('phonenumber', ['value' => $this->fields['phonenumber'], 'size' => 30]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Address') . "</td>";
      echo "<td>";
      echo "<textarea name='address' rows='3' cols='50'>" . $this->fields['address'] . "</textarea>";
      echo "</td>";

      echo "<td>" . __('Postal code') . "</td>";
      echo "<td>";
      echo Html::input('postcode', ['value' => $this->fields['postcode'], 'size' => 10]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Town') . "</td>";
      echo "<td>";
      echo Html::input('town', ['value' => $this->fields['town'], 'size' => 50]);
      echo "</td>";

      echo "<td>" . __('State') . "</td>";
      echo "<td>";
      echo Html::input('state', ['value' => $this->fields['state'], 'size' => 50]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Country') . "</td>";
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
         Session::addMessageAfterRedirect(__('Name is mandatory', 'associatesmanager'), false, ERROR);
         return false;
      }

      if (empty($input['suppliers_id'])) {
         Session::addMessageAfterRedirect(__('Supplier is mandatory', 'associatesmanager'), false, ERROR);
         return false;
      }

      return $input;
   }

   function prepareInputForUpdate($input) {
      return $input;
   }

   function post_addItem() {
      if ($this->fields['is_person'] == 1 && $this->fields['contacts_id'] == 0) {
         $contact = new Contact();
         $contact_data = [
            'name'        => $this->fields['name'],
            'email'       => $this->fields['email'],
            'phone'       => $this->fields['phonenumber'],
            'address'     => $this->fields['address'],
            'postcode'    => $this->fields['postcode'],
            'town'        => $this->fields['town'],
            'state'       => $this->fields['state'],
            'country'     => $this->fields['country']
         ];

         $contact_id = $contact->add($contact_data);

         if ($contact_id) {
            $this->update([
               'id'          => $this->fields['id'],
               'contacts_id' => $contact_id
            ]);

            $contact_supplier = new Contact_Supplier();
            $contact_supplier->add([
               'contacts_id'  => $contact_id,
               'suppliers_id' => $this->fields['suppliers_id']
            ]);
         }
      }
   }

   static function dropdown($options = []) {
      global $DB, $CFG_GLPI;

      $p = [
         'name'     => 'plugin_associatesmanager_associates_id',
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
         'id'                 => '2',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
      ];

      $tab[] = [
         'id'                 => '3',
         'table'              => $this->getTable(),
         'field'              => 'is_person',
         'name'               => __('Type', 'associatesmanager'),
         'datatype'           => 'bool',
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this->getTable(),
         'field'              => 'email',
         'name'               => __('Email'),
         'datatype'           => 'email',
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => $this->getTable(),
         'field'              => 'phonenumber',
         'name'               => __('Phone'),
         'datatype'           => 'string',
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => 'glpi_suppliers',
         'field'              => 'name',
         'name'               => __('Supplier'),
         'datatype'           => 'dropdown',
      ];

      return $tab;
   }
}
