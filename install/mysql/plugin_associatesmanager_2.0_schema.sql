-- Plugin AssociatesManager schema migration v2.0
-- This file intentionally contains no destructive ALTER statements.
-- Structural changes (add/drop columns) are performed by the PHP migration runner
-- to ensure idempotence across different MySQL versions and existing deployments.

-- Intent (for humans / preview):
--  - Add `matricule` (VARCHAR(64)) to `glpi_plugin_associatesmanager_associates`.
--  - Reuse `glpi_plugin_associatesmanager_parts` as the pivot table and add:
--      `associates_id`, `supplier_id`, `nbparts`, `date_attribution`, `date_fin`.
--  - Remove legacy columns like `state`, `suppliers_id` (associates) and `valeur` (parts).

-- The PHP migration runner (`inc/install.class.php`) will perform safe checks
-- and apply these changes only when needed.
