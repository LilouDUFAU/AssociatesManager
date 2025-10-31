<?php

require_once __DIR__ . '/../../../inc/includes.php';

Session::checkLoginUser();
Session::checkRight('plugin_associatesmanager', READ);

$part = new PluginAssociatesmanagerPart();

if (isset($_POST['add'])) {
   // Always set entities_id for new parts
   if (!isset($_POST['entities_id']) || !$_POST['entities_id']) {
      $_POST['entities_id'] = $_SESSION['glpiactive_entity'];
   }
   $part->check(-1, CREATE, $_POST);
   if ($newID = $part->add($_POST)) {
      // If supplier_id was provided, return to the supplier page the user came from
      if (!empty($_POST['supplier_id'])) {
         $sid = (int)$_POST['supplier_id'];
         // Redirect to GLPI supplier form page
         Html::redirect('/front/supplier.form.php?id=' . $sid);
      }
      // Fallback: redirect to the parts list
      Html::redirect(Plugin::getWebDir('associatesmanager') . '/front/part.php');
   }
   Html::back();

} else if (isset($_POST['update'])) {
   $part->check($_POST['id'], UPDATE);
   $part->update($_POST);
   // Redirect to the part's detail page after update
   Html::redirect($part->getLinkURL());

} else if (isset($_POST['delete'])) {
   $part->check($_POST['id'], DELETE);
   $part->delete($_POST);
   // Always redirect to the list after deletion
   Html::redirect(Plugin::getWebDir('associatesmanager') . '/front/part.php');

} else if (isset($_POST['purge'])) {
   $part->check($_POST['id'], PURGE);
   $part->delete($_POST, 1);
   // Always redirect to the list after purge
   Html::redirect(Plugin::getWebDir('associatesmanager') . '/front/part.php');

} else {
   $part->checkGlobal(READ);

   $menus = ['admin', 'PluginAssociatesmanagerMenu', 'part'];
   PluginAssociatesmanagerPart::displayFullPageForItem($_GET['id'] ?? 0, $menus, $_GET);
}
