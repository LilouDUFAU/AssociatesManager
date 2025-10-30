<?php
// Minimal GLPI bootstrap/stubs for unit testing plugin classes without full GLPI
if (!defined('GLPI_ROOT')) {
    define('GLPI_ROOT', __DIR__ . '/..');
}

// Translation stubs
function __($s, $domain = null) { return $s; }
function _n($singular, $plural, $nb, $domain = null) { return ($nb > 1) ? $plural : $singular; }

// Minimal class stubs used by plugin files
class CommonDBTM {}
class CommonGLPI {}
class Plugin {
    public static function getWebDir($name, $full = true) { return '/plugins/' . $name; }
}
class Session {
    public static function getPluralNumber() { return 1; }
    public static function haveRight($right, $mode = '') { return true; }
    public static function addMessageAfterRedirect($msg, $flag = false, $type = '') { /* noop */ }
}
class Html {
    public static function input($name, $opts = []) { return "<input name='$name'>"; }
    public static function convDate($d) { return $d; }
    public static function showDateField($name, $opts = []) { return "<input type='date' name='$name'>"; }
}
class Dropdown {
    const EMPTY_VALUE = 0;
    public static function showFromArray($name, $values, $params = []) { return ''; }
}
class Supplier {}
class Contact { public function add($data) { return 0; } }

// Prevent notices for undefined global DB
global $DB;
$DB = null;

// Include plugin class files
$base = __DIR__ . '/../inc/';
$files = [
    'associate.class.php',
    'part.class.php',
    'partshistory.class.php',
    'config.class.php'
];
foreach ($files as $f) {
    $path = $base . $f;
    if (file_exists($path)) {
        require_once $path;
    }
}
