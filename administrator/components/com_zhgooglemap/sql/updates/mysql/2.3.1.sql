ALTER TABLE `#__zhgooglemaps_maps` ADD `findcontrol` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_maps` ADD `findwidth` int(5) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_maps` ADD `findpos` tinyint(1) NOT NULL default '2';
ALTER TABLE `#__zhgooglemaps_maps` ADD `findroute` tinyint(1) NOT NULL default '0';

ALTER TABLE `#__zhgooglemaps_markers` ADD `zoombyclick` int(3) NOT NULL default '100';
