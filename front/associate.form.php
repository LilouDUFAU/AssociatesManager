<?php

require '../../../inc/includes.php';

if (!class_exists('Event')) {
   echo '<div style=\"color:red\">Event class not loaded!</div>';
}

Session::checkLoginUser();
Session::checkRight('plugin_associatesmanager', READ);

$associate = new PluginAssociatesmanagerAssociate();

if (isset($_POST['add'])) {
   // Always set entities_id for new associates
   if (!isset($_POST['entities_id']) || !$_POST['entities_id']) {
      $_POST['entities_id'] = $_SESSION['glpiactive_entity'];
   }
   $associate->check(-1, CREATE, $_POST);
   if ($newID = $associate->add($_POST)) {
      Session::addMessageAfterRedirect(__('Associate successfully created', 'associatesmanager'), true, INFO);
      Html::redirect(Plugin::getWebDir('associatesmanager') . '/front/associate.php');
   }
   Html::back();

} else if (isset($_POST['update'])) {
   $associate->check($_POST['id'], UPDATE);
   $associate->update($_POST);
   Session::addMessageAfterRedirect(__('Associate successfully updated', 'associatesmanager'), true, INFO);
   Html::redirect(Plugin::getWebDir('associatesmanager') . '/front/associate.php');

} else if (isset($_POST['delete'])) {
   $associate->check($_POST['id'], DELETE);
   $associate->delete($_POST);
   Session::addMessageAfterRedirect(__('Associate successfully deleted', 'associatesmanager'), true, INFO);
   Html::redirect(Plugin::getWebDir('associatesmanager') . '/front/associate.php');

} else if (isset($_POST['purge'])) {
   $associate->check($_POST['id'], PURGE);
   $associate->delete($_POST, 1);
   Session::addMessageAfterRedirect(__('Associate successfully purged', 'associatesmanager'), true, INFO);
   Html::redirect(Plugin::getWebDir('associatesmanager') . '/front/associate.php');

} else {
   $associate->checkGlobal(READ);

   $menus = ['admin', 'PluginAssociatesmanagerMenu', 'associate'];
   PluginAssociatesmanagerAssociate::displayFullPageForItem($_GET['id'] ?? 0, $menus, $_GET);
}
