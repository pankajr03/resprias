ALTER TABLE `#__zhgooglemaps_routers` ADD `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__zhgooglemaps_routers` ADD `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__zhgooglemaps_paths` ADD `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__zhgooglemaps_paths` ADD `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__zhgooglemaps_markergroups` ADD `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__zhgooglemaps_markergroups` ADD `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__zhgooglemaps_maptypes` ADD `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__zhgooglemaps_maptypes` ADD `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__zhgooglemaps_infobubbles` ADD `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__zhgooglemaps_infobubbles` ADD `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';



