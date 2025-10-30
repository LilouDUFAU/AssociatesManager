<?php

require_once __DIR__ . '/../../../inc/includes.php';

Session::checkLoginUser();
Session::checkRight('plugin_associatesmanager', READ);

$partshistory = new PluginAssociatesmanagerPartshistory();

if (isset($_POST['add'])) {
   // Always set entities_id for new partshistory entries
   if (!isset($_POST['entities_id']) || !$_POST['entities_id']) {
      $_POST['entities_id'] = $_SESSION['glpiactive_entity'];
   }
   $partshistory->check(-1, CREATE, $_POST);
   if ($newID = $partshistory->add($_POST)) {
      // Always redirect to the list after creation
      Html::redirect(Plugin::getWebDir('associatesmanager') . '/front/partshistory.php');
   }
   Html::back();

} else if (isset($_POST['update'])) {
   $partshistory->check($_POST['id'], UPDATE);
   $partshistory->update($_POST);
   // Redirect to the partshistory's detail page after update
   Html::redirect($partshistory->getLinkURL());

} else if (isset($_POST['delete'])) {
   $partshistory->check($_POST['id'], DELETE);
   $partshistory->delete($_POST);
   // Always redirect to the list after deletion
   Html::redirect(Plugin::getWebDir('associatesmanager') . '/front/partshistory.php');

} else if (isset($_POST['purge'])) {
   $partshistory->check($_POST['id'], PURGE);
   $partshistory->delete($_POST, 1);
   // Always redirect to the list after purge
   Html::redirect(Plugin::getWebDir('associatesmanager') . '/front/partshistory.php');

} else {
   $partshistory->checkGlobal(READ);

   $menus = ['admin', 'PluginAssociatesmanagerMenu', 'partshistory'];
   PluginAssociatesmanagerPartshistory::displayFullPageForItem($_GET['id'] ?? 0, $menus, $_GET);
}
