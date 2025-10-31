-- Uninstall SQL for AssociatesManager plugin
DROP TABLE IF EXISTS `glpi_plugin_associatesmanager_configs`;
-- The dedicated partshistories table was removed; historical rows live in `parts` now.
DROP TABLE IF EXISTS `glpi_plugin_associatesmanager_parts`;
DROP TABLE IF EXISTS `glpi_plugin_associatesmanager_associates`;
