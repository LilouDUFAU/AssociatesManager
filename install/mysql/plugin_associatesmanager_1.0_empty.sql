-- Plugin AssociatesManager schema v1.0 (empty)

CREATE TABLE IF NOT EXISTS `glpi_plugin_associatesmanager_associates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `is_person` tinyint NOT NULL DEFAULT '0',
  `address` text,
  `postcode` varchar(10) DEFAULT NULL,
  `town` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `phonenumber` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `suppliers_id` int unsigned NOT NULL DEFAULT '0',
  `contacts_id` int unsigned NOT NULL DEFAULT '0',
  `date_creation` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `suppliers_id` (`suppliers_id`),
  KEY `contacts_id` (`contacts_id`),
  KEY `is_person` (`is_person`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `glpi_plugin_associatesmanager_parts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) NOT NULL,
  `valeur` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `date_creation` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Historical per-associate entries are now stored in the main `parts` table
-- (historical rows are kept by setting `date_fin`). The separate
-- `partshistories` table has been removed in this version.

CREATE TABLE IF NOT EXISTS `glpi_plugin_associatesmanager_configs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
