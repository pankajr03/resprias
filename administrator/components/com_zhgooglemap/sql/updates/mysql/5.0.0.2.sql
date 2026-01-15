ALTER TABLE `#__zhgooglemaps_markers` ADD INDEX `idx_access` (`access`);

UPDATE `#__zhgooglemaps_markers` SET `access` = '1' WHERE `access` = '0';
