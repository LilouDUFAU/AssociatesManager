<?php

require_once __DIR__ . '/../../../inc/includes.php';

Session::checkLoginUser();
Session::checkRight('config', READ);

Html::header(
   'Configuration du plugin Associates Manager',
   $_SERVER['PHP_SELF'],
   'config',
   'PluginAssociatesmanagerConfig'
);

$config = new PluginAssociatesmanagerConfig();
$config->showConfigForm();

Html::footer();
