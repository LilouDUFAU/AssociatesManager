<?php
require __DIR__ . '/bootstrap.php';

$errors = [];

$classes = [
    'PluginAssociatesmanagerAssociate',
    'PluginAssociatesmanagerPart',
    'PluginAssociatesmanagerPartshistory'
];

foreach ($classes as $c) {
    if (!class_exists($c)) {
        $errors[] = "Missing class: $c";
        continue;
    }
    // Basic static method test
    if (!method_exists($c, 'getTypeName')) {
        $errors[] = "Missing getTypeName() in $c";
        continue;
    }
    try {
        $res = $c::getTypeName(1);
        if (!is_string($res) || $res === '') {
            $errors[] = "getTypeName() returned invalid value for $c";
        }
    } catch (Throwable $e) {
        $errors[] = "Exception calling getTypeName() on $c: " . $e->getMessage();
    }
}

// Check presence of key methods on Part class
if (class_exists('PluginAssociatesmanagerPart')) {
    $needed = ['addPart', 'getPartsForAssociate', 'computeSharePercent', 'endPart'];
    foreach ($needed as $m) {
        if (!method_exists('PluginAssociatesmanagerPart', $m)) {
            $errors[] = "PluginAssociatesmanagerPart::{$m}() missing";
        }
    }
}

if (empty($errors)) {
    echo "ALL TESTS PASSED\n";
    exit(0);
} else {
    foreach ($errors as $e) {
        echo "ERROR: $e\n";
    }
    exit(1);
}
