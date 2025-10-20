<?php

require '../../../inc/includes.php';

Session::checkLoginUser();
Session::checkRight('plugin_associatesmanager', READ);

Html::header(
   PluginAssociatesmanagerAssociate::getTypeName(Session::getPluralNumber()),
   $_SERVER['PHP_SELF'],
   'admin',
   'PluginAssociatesmanagerMenu',
   'associate'
);

// Add "New" button in header if user has CREATE right
if (Session::haveRight('plugin_associatesmanager', CREATE)) {
   echo "<div class='spaced'>";
   echo "<a href='" . PluginAssociatesmanagerAssociate::getFormURL() . "' class='btn btn-primary'>";
   echo "<i class='fas fa-plus'></i> ";
   echo "<span>" . __('New associate', 'associatesmanager') . "</span>";
   echo "</a>";
   echo "</div>";
}

Search::show('PluginAssociatesmanagerAssociate');

Html::footer();
