ALTER TABLE `#__zhgooglemaps_maps` ADD `markergroupctlmarker` tinyint(1) NOT NULL default '1';
ALTER TABLE `#__zhgooglemaps_maps` ADD `markergroupctlpath` tinyint(1) NOT NULL default '0';

ALTER TABLE `#__zhgooglemaps_paths` ADD `markergroup` int(11) NOT NULL default '0';
