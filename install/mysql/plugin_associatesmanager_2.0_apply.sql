-- Apply DB changes for AssociatesManager v2.0
-- Adds matricule to associates and converts parts table into pivot (associates_id, supplier_id, nbparts, dates)

-- Add matricule to associates (if missing) and drop legacy columns if present
ALTER TABLE `glpi_plugin_associatesmanager_associates`
  ADD COLUMN IF NOT EXISTS `matricule` VARCHAR(64) DEFAULT NULL AFTER `name`;

-- Convert parts table to pivot-style columns (idempotent)
ALTER TABLE `glpi_plugin_associatesmanager_parts`
  ADD COLUMN IF NOT EXISTS `associates_id` INT UNSIGNED NOT NULL DEFAULT '0',
  ADD COLUMN IF NOT EXISTS `supplier_id` INT UNSIGNED NOT NULL DEFAULT '0',
  ADD COLUMN IF NOT EXISTS `nbparts` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  ADD COLUMN IF NOT EXISTS `date_attribution` DATE DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `date_fin` DATE DEFAULT NULL;

-- Add indexes if not present (MySQL 8+ supports IF NOT EXISTS)
CREATE INDEX IF NOT EXISTS `idx_associates_id` ON `glpi_plugin_associatesmanager_parts` (`associates_id`);
CREATE INDEX IF NOT EXISTS `idx_supplier_id` ON `glpi_plugin_associatesmanager_parts` (`supplier_id`);

-- Remove legacy columns if present
ALTER TABLE `glpi_plugin_associatesmanager_parts` DROP COLUMN IF EXISTS `valeur`;
ALTER TABLE `glpi_plugin_associatesmanager_associates` DROP COLUMN IF EXISTS `state`;
ALTER TABLE `glpi_plugin_associatesmanager_associates` DROP COLUMN IF EXISTS `suppliers_id`;

-- Migrate any existing partshistories rows into the parts table so all records live in `parts`.
-- This preserves history inside the `parts` table. After verification, the table can be dropped.
-- Copy fields where possible (best-effort, non-destructive)
INSERT INTO `glpi_plugin_associatesmanager_parts` (
  libelle, nbparts, associates_id, supplier_id, date_attribution, date_fin, date_creation, date_mod
)
SELECT
  -- part history table doesn't have its own libelle; use the linked part libelle when available
  COALESCE(p.libelle, ''),
  -- prefer historical nbparts, fallback to part.nbparts
  COALESCE(ph.nbparts, p.nbparts, 0.0000),
  -- historical table stores associate id in plugin_associatesmanager_associates_id
  COALESCE(ph.plugin_associatesmanager_associates_id, p.associates_id, 0),
  -- supplier id may only live on the parts row; prefer that or 0
  COALESCE(p.supplier_id, 0),
  COALESCE(ph.date_attribution, p.date_attribution),
  COALESCE(ph.date_fin, p.date_fin),
  COALESCE(ph.date_creation, p.date_creation),
  COALESCE(ph.date_mod, p.date_mod)
FROM `glpi_plugin_associatesmanager_partshistories` ph
LEFT JOIN `glpi_plugin_associatesmanager_parts` p ON p.id = ph.plugin_associatesmanager_parts_id;

-- After verifying the copied rows, you can drop the old partshistories table. Keep commented for preview.
-- DROP TABLE IF EXISTS `glpi_plugin_associatesmanager_partshistories`;
