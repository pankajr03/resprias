ALTER TABLE `#__zhgooglemaps_markers` ADD `descriptionfullhtml` text NOT NULL;

ALTER TABLE `#__zhgooglemaps_maps` ADD `routeavoidhighways` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_maps` ADD `routeavoidtolls` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_maps` ADD `routeunitsystem` tinyint(1) NOT NULL default '0';
