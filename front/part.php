<?php

require_once __DIR__ . '/../../../inc/includes.php';

Session::checkLoginUser();
Session::checkRight('plugin_associatesmanager', READ);

Html::header(
   PluginAssociatesmanagerPart::getTypeName(Session::getPluralNumber()),
   $_SERVER['PHP_SELF'],
   'admin',
   'PluginAssociatesmanagerMenu',
   'part'
);

// Add "New" button in header if user has CREATE right
if (Session::haveRight('plugin_associatesmanager', CREATE)) {
   echo "<div class='spaced'>";
   echo "<a href='" . PluginAssociatesmanagerPart::getFormURL() . "' class='btn btn-primary'>";
   echo "<i class='fas fa-plus'></i> ";
   echo "<span>Nouvelle part</span>";
   echo "</a>";
   echo "</div>";
}

Search::show('PluginAssociatesmanagerPart');

Html::footer();
