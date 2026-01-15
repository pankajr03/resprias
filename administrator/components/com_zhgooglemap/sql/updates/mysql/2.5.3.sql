ALTER TABLE `#__zhgooglemaps_routers` ADD `descriptionhtml` text NOT NULL;
ALTER TABLE `#__zhgooglemaps_routers` ADD `routebymarker` text NOT NULL;
ALTER TABLE `#__zhgooglemaps_markergroups` ADD `overridemarkericon` tinyint(1) NOT NULL default '0';

