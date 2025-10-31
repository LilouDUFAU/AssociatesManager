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

// Filters: supplier, associate, part type (libelle), search, date range
global $DB;

$supplier_id = (int)($_GET['supplier_id'] ?? 0);
$associate_id = (int)($_GET['associates_id'] ?? 0);
$part_label  = trim($_GET['libelle'] ?? '');
$q_search    = '';
$date_from   = '';
$date_to     = '';
$sort_by     = ($_GET['sort_by'] ?? 'date_attribution');
$sort_dir    = (strtoupper($_GET['sort_dir'] ?? 'DESC') === 'ASC') ? 'ASC' : 'DESC';

echo "<form id='parts-filters' method='get' class='form-inline spaced'>";
echo Html::hidden('id', ['value' => 0]);
// Supplier filter
echo "<label class='mr-2'>Fournisseur</label>";
Supplier::dropdown(['name' => 'supplier_id', 'value' => $supplier_id]);
// Associate filter
echo "<label class='ml-3 mr-2'>Associé</label>";
PluginAssociatesmanagerAssociate::dropdown(['name' => 'associates_id', 'value' => $associate_id]);
// Part type filter (distinct libelle)
echo "<label class='ml-3 mr-2'>Type</label>";
// build libelle options
// Some DB wrapper configurations may not handle SELECT DISTINCT expressions
// reliably in all environments. Fetch all libelle values and deduplicate in PHP.
$libelle_it = $DB->request([
   'SELECT' => ['libelle'],
   'FROM'   => 'glpi_plugin_associatesmanager_parts',
   'ORDER'  => 'libelle'
]);
$libelle_vals = [ '' => Dropdown::EMPTY_VALUE ];
$seen = [];
foreach ($libelle_it as $r) {
   $lbl = trim((string)($r['libelle'] ?? ''));
   if ($lbl === '' || isset($seen[$lbl])) { continue; }
   $seen[$lbl] = true;
   $libelle_vals[$lbl] = $lbl;
}
Dropdown::showFromArray('libelle', $libelle_vals, ['value' => $part_label]);

// Search removed per request

// Date range removed per request

// Sort controls
echo "<label class='ml-3 mr-2'>Trier par</label>";
$sort_options = [ 'date_attribution' => 'Date d\'attribution', 'nbparts' => 'Nombre de parts', 'percent' => 'Pourcentage' ];
Dropdown::showFromArray('sort_by', $sort_options, ['value' => $sort_by]);
Dropdown::showFromArray('sort_dir', [ 'DESC' => 'Desc', 'ASC' => 'Asc' ], ['value' => $sort_dir]);

echo "<button class='btn btn-secondary ml-3' type='submit'>Filtrer</button>";
// Reset button: restore filter fields to their base state (server-provided defaults)
// reset button removed per request
echo "</form>";

// JavaScript: restore base filter values and submit the form
// Base/default values are taken from PHP variables above
$defaults = [
   // Reset targets: clear filters to base state
   'supplier_id' => 0,
   'associates_id' => 0,
   'libelle' => '',
   // search removed
   // date_from/date_to removed
   'sort_by' => 'date_attribution',
   'sort_dir' => 'DESC'
];
// reset JS removed

// Build WHERE conditions
$where = [];
$params = [];
if ($supplier_id > 0) { $where[] = "p.supplier_id = :supplier_id"; $params[':supplier_id'] = $supplier_id; }
if ($associate_id > 0) { $where[] = "p.associates_id = :associate_id"; $params[':associate_id'] = $associate_id; }
if ($part_label !== '') { $where[] = "p.libelle = :libelle"; $params[':libelle'] = $part_label; }
// Search-based raw WHERE removed

// Date range overlap: we want assignments that intersect [date_from, date_to]
// Date filtering removed per user request.

// Build structured query arguments for DB wrapper
// Simpler flat WHERE map: keys are fields and values are either scalars or
// operator arrays. Use 'OR' for text search across multiple fields.
$where_criteria = [];
if ($supplier_id > 0) { $where_criteria['p.supplier_id'] = $supplier_id; }
if ($associate_id > 0) { $where_criteria['p.associates_id'] = $associate_id; }
if ($part_label !== '') { $where_criteria['p.libelle'] = $part_label; }
// Search-based structured WHERE removed
// Date filters: show parts whose attribution date is within the requested range.
// If both bounds provided, add both operators on the same field.
// Date filters removed per user request.

// Sorting
if ($sort_by === 'percent') {
   // percent is computed per row; we will fetch rows and sort in PHP
   $order_sql = 'p.date_attribution DESC';
} else {
   $allowed = ['date_attribution','nbparts'];
   if (!in_array($sort_by, $allowed)) { $sort_by = 'date_attribution'; }
   $order_sql = 'p.' . $sort_by . ' ' . $sort_dir;
}

// Use DB wrapper structured request
$request_args = [
   // Only select parts columns here; we'll fetch associate/supplier details
   // in separate queries to avoid ambiguity and aliasing issues.
   'SELECT' => [ 'p.*' ],
   'FROM' => 'glpi_plugin_associatesmanager_parts AS p',
   'LEFT JOIN' => [
      'glpi_plugin_associatesmanager_associates AS a' => ['a.id' => 'p.associates_id'],
      'glpi_suppliers AS s' => ['s.id' => 'p.supplier_id']
   ],
   'WHERE' => $where_criteria,
   'ORDER' => $order_sql
];

$rows = [];
$it = $DB->request($request_args);
foreach ($it as $r) {
   $rows[] = $r;
}

// Debug helper: show constructed WHERE criteria and fetched rows when requested
// When ?debug_filters=1 is present we both echo detailed info and append a
// human-readable dump into a log file in the plugin root for offline
// inspection. This helps diagnose issues that don't appear with a simple
// on-screen dump (formatting, missing fields, etc.).
if (!empty($_GET['debug_filters'])) {
   $debug = [];
   $debug['timestamp'] = date('c');
   $debug['request_uri'] = ($_SERVER['REQUEST_URI'] ?? '');
   $debug['get'] = $_GET;
   $debug['date_from'] = $date_from;
   $debug['date_to'] = $date_to;
   $debug['supplier_id'] = $supplier_id;
   $debug['associates_id'] = $associate_id;
   $debug['part_label'] = $part_label;
   $debug['q_search'] = $q_search;
   $debug['raw_where_clauses'] = $where; // older raw SQL-fragments for reference
   $debug['where_criteria'] = $where_criteria; // structured criteria sent to DB wrapper
   $debug['request_args'] = $request_args;
   $debug['rows_count'] = count($rows);
   $debug['rows_sample'] = array_slice($rows, 0, 10);

   // Compose readable output for browser
   echo "<div class='spaced'><pre style='text-align:left;'>";
   echo htmlspecialchars("Debug dump (frontend) -- " . $debug['timestamp'] . "\n\n");
   echo htmlspecialchars("REQUEST_URI: " . $debug['request_uri'] . "\n\n");
   echo htmlspecialchars("GET:\n" . var_export($debug['get'], true) . "\n\n");
   echo htmlspecialchars("where_criteria:\n" . var_export($debug['where_criteria'], true) . "\n\n");
   echo htmlspecialchars("rows_count: " . $debug['rows_count'] . "\n\n");
   if ($debug['rows_count']) {
      echo htmlspecialchars("rows_sample:\n" . var_export($debug['rows_sample'], true) . "\n\n");
   }
   echo htmlspecialchars("request_args:\n" . var_export($debug['request_args'], true) . "\n");
   echo "</pre></div>";

   // Append full dump to a log file in plugin root for easier sharing/inspection
   $logfile = __DIR__ . '/../associatesmanager_debug.log';
   $entry  = "----\n" . $debug['timestamp'] . "\n";
   $entry .= "REQUEST_URI: " . $debug['request_uri'] . "\n";
   $entry .= "GET: " . print_r($debug['get'], true) . "\n";
   $entry .= "where (raw): " . print_r($debug['raw_where_clauses'], true) . "\n";
   $entry .= "where_criteria: " . print_r($debug['where_criteria'], true) . "\n";
   $entry .= "request_args: " . print_r($debug['request_args'], true) . "\n";
   $entry .= "rows_count: " . $debug['rows_count'] . "\n";
   $entry .= "rows_sample: " . print_r($debug['rows_sample'], true) . "\n";
   file_put_contents($logfile, $entry, FILE_APPEND | LOCK_EX);

   echo "<div class='spaced'>Debug log appended to: <strong>" . htmlspecialchars($logfile) . "</strong></div>";
}

// Build maps for associates and suppliers to show names/phone/town
$assocIds = [];
$supplierIds = [];
foreach ($rows as $r) {
   if (!empty($r['associates_id'])) { $assocIds[$r['associates_id']] = (int)$r['associates_id']; }
   if (!empty($r['supplier_id']))   { $supplierIds[$r['supplier_id']] = (int)$r['supplier_id']; }
}

$associates = [];
if (count($assocIds)) {
   $it2 = $DB->request([
      'SELECT' => ['id','name','is_person','phonenumber','town'],
      'FROM'   => 'glpi_plugin_associatesmanager_associates',
      'WHERE'  => ['id' => array_values($assocIds)]
   ]);
   foreach ($it2 as $a) {
      $associates[$a['id']] = $a;
   }
}

$suppliers = [];
if (count($supplierIds)) {
   $it3 = $DB->request([
      'SELECT' => ['id','name'],
      'FROM'   => 'glpi_suppliers',
      'WHERE'  => ['id' => array_values($supplierIds)]
   ]);
   foreach ($it3 as $s) {
      $suppliers[$s['id']] = $s['name'];
   }
}

// If sorting by percent, compute percent for each row and sort in PHP
if ($sort_by === 'percent' && count($rows)) {
   $part_helper = new PluginAssociatesmanagerPart();
   foreach ($rows as &$r) {
      // Use the row's attribution date for percent calculation
      $r['percent'] = $part_helper->computeSharePercent($r['associates_id'], $r['supplier_id'], $r['date_attribution']);
   }
   usort($rows, function($a, $b) use ($sort_dir) {
      $pa = $a['percent'] ?? 0.0;
      $pb = $b['percent'] ?? 0.0;
      if ($pa == $pb) return 0;
      if ($sort_dir === 'ASC') return ($pa < $pb) ? -1 : 1;
      return ($pa > $pb) ? -1 : 1;
   });
}

echo "<div class='center'>";
if (count($rows)) {
   echo "<table class='tab_cadre_fixehov'>";
   echo "<tr class='noHover'><th colspan='10'>" . PluginAssociatesmanagerPart::getTypeName(count($rows)) . "</th></tr>";
   echo "<tr>";
   echo "<th>Associé</th>";
   echo "<th>Type</th>";
   echo "<th>Téléphone</th>";
   echo "<th>Ville</th>";
   echo "<th>Nombre de parts</th>";
   echo "<th>Part (%)</th>";
   echo "<th>Date d'attribution</th>";
   echo "<th>Date de fin</th>";
   echo "<th>Fournisseur associé</th>";
   echo "<th>Actions</th>";
   echo "</tr>";

   foreach ($rows as $data) {
      // Resolve associate data from the pre-fetched map using the id stored on the part
      $assoc = null;
      if (!empty($data['associates_id']) && isset($associates[$data['associates_id']])) {
         $assoc = $associates[$data['associates_id']];
      }
      $assoc_name = $assoc['name'] ?? '';
      $is_person = (!empty($assoc['is_person'])) ? 'Personne' : 'Société';
      $phone = $assoc['phonenumber'] ?? '';
      $town  = $assoc['town'] ?? '';
      $label = $data['libelle'] ?? '';
      $nbparts = isset($data['nbparts']) ? (float)$data['nbparts'] : 0.0;
      $date_attr = $data['date_attribution'] ?? null;
      $date_fin = $data['date_fin'] ?? null;
   // Resolve supplier name from the pre-fetched map using the id stored on the part
   $supplier_name = '';
   if (!empty($data['supplier_id']) && isset($suppliers[$data['supplier_id']])) {
      $supplier_name = $suppliers[$data['supplier_id']];
   }

   // percent: compute on date_from if provided, else on date_attribution
   // computeSharePercent is an instance method; ensure helper exists
   if (!isset($part_helper)) { $part_helper = new PluginAssociatesmanagerPart(); }
   $pct = $part_helper->computeSharePercent($data['associates_id'], $data['supplier_id'], $date_attr);

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      if ($assoc_name !== '') {
         echo "<a href='" . Plugin::getWebDir('associatesmanager') . "/front/associate.form.php?id=" . $data['associates_id'] . "'>" . htmlspecialchars($assoc_name) . "</a>";
      } else {
         echo "&mdash;";
      }
      echo "</td>";
      echo "<td>" . htmlspecialchars($is_person) . "</td>";
      echo "<td>" . htmlspecialchars($phone) . "</td>";
      echo "<td>" . htmlspecialchars($town) . "</td>";
      echo "<td class='left'>" . number_format($nbparts, 4, ',', ' ') . "</td>";
      echo "<td class='left'>" . sprintf('%.1f', $pct) . "%</td>";
      echo "<td>" . Html::convDate($date_attr) . "</td>";
      echo "<td>" . Html::convDate($date_fin) . "</td>";
      echo "<td>";
      if ($supplier_name !== '') {
         echo "<a href='" . Plugin::getWebDir('associatesmanager') . "/front/supplier.form.php?id=" . $data['supplier_id'] . "'>" . htmlspecialchars($supplier_name) . "</a>";
      } else {
         echo "&mdash;";
      }
      echo "</td>";
      echo "<td><a href='" . Plugin::getWebDir('associatesmanager') . "/front/part.form.php?id=" . $data['id'] . "'><i class='fas fa-edit'></i></a></td>";
      echo "</tr>";
   }
   echo "</table>";
} else {
   echo "<table class='tab_cadre_fixe'>";
   echo "<tr><th>Aucune part trouvée</th></tr>";
   echo "</table>";
}
echo "</div>";

Html::footer();
