ALTER TABLE `#__zhgooglemaps_maps` ADD `override_id` int(11) NOT NULL default '0';

ALTER TABLE `#__zhgooglemaps_maps` ADD INDEX `idx_override` (`override_id`);
