ALTER TABLE `#__zhgooglemaps_markers` ADD `alias` varchar(255) NOT NULL default '';

ALTER TABLE `#__zhgooglemaps_markers` ADD INDEX `idx_alias` (`alias`);