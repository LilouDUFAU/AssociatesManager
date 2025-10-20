<?php

require '../../../inc/includes.php';

Session::checkLoginUser();
Session::checkRight('config', READ);

Html::header(
   __('Associates Manager Configuration', 'associatesmanager'),
   $_SERVER['PHP_SELF'],
   'config',
   'PluginAssociatesmanagerConfig'
);

$config = new PluginAssociatesmanagerConfig();
$config->showConfigForm();

Html::footer();
