<?php

require_once __DIR__ . '/../../../inc/includes.php';

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
   echo "<span>Nouvel associé</span>";
   echo "</a>";
   echo "</div>";
}

// Custom table: one row per (supplier, associate) duo showing parts and percentage
global $DB;

// Récupère toutes les parts actives (date_fin IS NULL)
$it = $DB->request([
   'FROM'  => 'glpi_plugin_associatesmanager_parts',
   'WHERE' => ['date_fin' => null]
]);

$pairs = []; // key supplier|associate => nbparts sum
$supplierTotals = []; // supplier_id => total nbparts
$assocIds = [];
$supplierIds = [];

foreach ($it as $r) {
   $sid = $r['supplier_id'];
   $aid = $r['associates_id'];
   $nb  = isset($r['nbparts']) ? (float)$r['nbparts'] : 0.0;
   $key = $sid . '|' . $aid;
   if (!isset($pairs[$key])) {
      $pairs[$key] = 0.0;
   }
   $pairs[$key] += $nb;

   if (!isset($supplierTotals[$sid])) {
      $supplierTotals[$sid] = 0.0;
   }
   $supplierTotals[$sid] += $nb;

   $assocIds[$aid] = $aid;
   $supplierIds[$sid] = $sid;
}

// Fetch associate details
$associates = [];
if (count($assocIds)) {
   $it2 = $DB->request([
      'SELECT' => ['id','name','is_person','email','phonenumber','town'],
      'FROM'   => 'glpi_plugin_associatesmanager_associates',
      'WHERE'  => ['id' => array_values($assocIds)]
   ]);
   foreach ($it2 as $a) {
      $associates[$a['id']] = $a;
   }
}

// Fetch supplier names
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

echo "<div class='center'>";
if (count($pairs)) {
   // Filters toolbar
   echo "<div class='filters' style='margin-bottom:10px;'>";
   echo "<label>Fournisseur: <select id='filter-supplier'><option value=''>Tous</option>";
   foreach ($suppliers as $sid => $sname) {
      echo "<option value='" . $sid . "'>" . htmlspecialchars($sname) . "</option>";
   }
   echo "</select></label> ";

   echo "<label>Associé: <select id='filter-assoc'><option value=''>Tous</option>";
   foreach ($associates as $aid => $a) {
      echo "<option value='" . $aid . "'>" . htmlspecialchars($a['name']) . "</option>";
   }
   echo "</select></label> ";

   echo "<label>Type: <select id='filter-type'><option value=''>Tous</option><option value='1'>Personne</option><option value='0'>Société</option></select></label> ";

   echo "<label>Recherche: <input type='search' id='filter-text' placeholder='Nom, email, ville...'></label> ";

   echo "<button id='sort-nb' class='btn btn-sm btn-light' data-order='desc'>Tri parts ↓</button> ";
   echo "<button id='sort-pct' class='btn btn-sm btn-light' data-order='desc'>Tri % ↓</button> ";
   echo "</div>";
   echo "<table class='tab_cadre_fixehov'>";
   echo "<tr class='noHover'><th colspan='9'>Associés - parts par fournisseur</th></tr>";
   echo "<tr>";
   echo "<th>Nom</th>";
   echo "<th>Type</th>";
   echo "<th>Email</th>";
   echo "<th>Téléphone</th>";
   echo "<th>Ville</th>";
   echo "<th>Nombre de parts</th>";
   echo "<th>Part (%)</th>";
   echo "<th>Fournisseur associé</th>";
   echo "<th>Actions</th>";
   echo "</tr>";

   // tbody for data rows (use an explicit id so JS targets the correct body)
   echo "<tbody id='associatesmanager-table-body'>";

   foreach ($pairs as $key => $nb) {
      list($sid, $aid) = explode('|', $key);
      $assoc = $associates[$aid] ?? null;
      $sname = $suppliers[$sid] ?? ('ID ' . $sid);
      $supplierTotal = $supplierTotals[$sid] ?? 0.0;
      $pct = ($supplierTotal > 0) ? ($nb / $supplierTotal * 100.0) : 0.0;

   // add data attributes for client-side filtering/sorting
   echo "<tr class='tab_bg_1' data-supplier='" . $sid . "' data-assoc='" . $aid . "' data-type='" . ($assoc ? $assoc['is_person'] : '') . "' data-nb='" . $nb . "' data-pct='" . $pct . "'>";
      if ($assoc) {
         echo "<td><a href='" . Plugin::getWebDir('associatesmanager') . "/front/associate.form.php?id=" . $assoc['id'] . "'>" . htmlspecialchars($assoc['name']) . "</a></td>";
            echo "<td>" . ($assoc['is_person'] ? 'Personne' : 'Société') . "</td>";
            echo "<td>" . htmlspecialchars($assoc['email']) . "</td>";
            echo "<td>" . htmlspecialchars($assoc['phonenumber']) . "</td>";
            echo "<td>" . htmlspecialchars($assoc['town']) . "</td>";
         } else {
            echo "<td>Assoc ID " . $aid . "</td>";
            echo "<td>-</td><td>-</td><td>-</td><td>-</td>";
         }

         echo "<td class='left'>" . number_format($nb, 2, ',', ' ') . "</td>";
            echo "<td class='left'>" . sprintf('%.1f', $pct) . "%</td>";
         // Link to GLPI supplier page (core Supplier) instead of plugin supplier form
         $supplier_url = '/front/supplier.form.php?id=' . $sid;
         if (class_exists('Supplier') && method_exists('Supplier', 'getFormURL')) {
            // getFormURL(true) should return the full path to the core form; append id param
            $supplier_url = Supplier::getFormURL(true) . '?id=' . $sid;
         }
         echo "<td><a href='" . $supplier_url . "'>" . htmlspecialchars($sname) . "</a></td>";
         // Actions: link to associate form where user can edit or delete
         echo "<td>";
            echo "<a class='' href='" . Plugin::getWebDir('associatesmanager') . "/front/associate.form.php?id=" . $aid . "' title='Ouvrir la fiche de l\'associé'>";
               echo "<i class='fas fa-edit'></i>";
            echo "</a>";
         echo "</td>";
      echo "</tr>";
   }

      echo "</tbody>";

      echo "</table>";
} else {
   echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th>Aucun couple fournisseur/associé avec parts actives trouvé</th></tr>";
   echo "</table>";
}
echo "</div>";

// Client-side filtering + sorting script
echo <<<'JS'
<script>
(function($){
   if (!$) return;
   function applyFilters(){
     var fs = $('#filter-supplier').val();
     var fa = $('#filter-assoc').val();
     var ft = $('#filter-type').val();
     var txt = $('#filter-text').val().toLowerCase();
     $('table.tab_cadre_fixehov tbody tr').each(function(){
       var $tr = $(this);
       var ok = true;
   if (fs && String($tr.data('supplier')) !== fs) ok = false;
   if (fa && String($tr.data('assoc')) !== fa) ok = false;
   if (ft !== '' && String($tr.data('type')) !== ft) ok = false;
       if (txt){
         var line = ($tr.find('td').map(function(){ return $(this).text(); }).get().join(' ')).toLowerCase();
         if (line.indexOf(txt) === -1) ok = false;
       }
       $tr.toggle(ok);
     });
   }
   $('#filter-supplier,#filter-assoc,#filter-type').on('change', applyFilters);
   $('#filter-text').on('input', function(){ setTimeout(applyFilters, 100); });

    function sortTableByData(attr, order){
         // Target the specific tbody by id to avoid selecting multiple/implicit tbodies
         var tbody = $('#associatesmanager-table-body');
         if (!tbody.length) return;

         // Ensure visibility state matches current filters
         applyFilters();

         // Get all child rows and record which positions are currently visible
         var all = tbody.children('tr').get();
         var visiblePositions = [];
         for (var i=0;i<all.length;i++){
            if ($(all[i]).is(':visible')) visiblePositions.push(i);
         }
         if (!visiblePositions.length) return;

         // Detach visible rows (preserves handlers/data)
         var visibleNodes = [];
         for (var j=0;j<visiblePositions.length;j++){
            var node = all[visiblePositions[j]];
            var detached = $(node).detach().get(0);
            visibleNodes.push(detached);
         }

         // Sort visible nodes by data attribute
         visibleNodes.sort(function(a,b){
             var va = parseFloat($(a).data(attr)) || 0;
             var vb = parseFloat($(b).data(attr)) || 0;
             return (order === 'asc') ? va - vb : vb - va;
         });

         // Rebuild the tbody content preserving hidden rows positions
         var newOrder = [];
         var si = 0;
         for (var k=0;k<all.length;k++){
            if (visiblePositions.indexOf(k) !== -1){
               newOrder.push(visibleNodes[si++]);
            } else {
               newOrder.push(all[k]);
            }
         }

         // Empty and append in the new order, restoring visibility state
         tbody.empty();
         for (var m=0;m<newOrder.length;m++){
            var nd = newOrder[m];
            tbody.append(nd);
            if (visiblePositions.indexOf(m) !== -1){
               $(nd).show();
            } else {
               $(nd).hide();
            }
         }
    }

   $('#sort-nb').on('click', function(){
     var $b = $(this); var order = $b.data('order') === 'asc' ? 'desc' : 'asc';
     $b.data('order', order).text('Tri parts ' + (order==='asc'?'↑':'↓'));
     sortTableByData('nb', order);
   });
   $('#sort-pct').on('click', function(){
     var $b = $(this); var order = $b.data('order') === 'asc' ? 'desc' : 'asc';
     $b.data('order', order).text('Tri % ' + (order==='asc'?'↑':'↓'));
     sortTableByData('pct', order);
   });

})(window.jQuery);
</script>
JS;

Html::footer();
