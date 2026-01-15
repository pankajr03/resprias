ALTER TABLE `#__zhgooglemaps_maps` ADD `panoramioenable` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_maps` ADD `panoramiofiltercontrol` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_maps` ADD `panoramiofiltercontrolpos` tinyint(1) NOT NULL default '2';
ALTER TABLE `#__zhgooglemaps_maps` ADD `panoramiotag` varchar(250) NOT NULL default '';
ALTER TABLE `#__zhgooglemaps_maps` ADD `panoramiouser` varchar(250) NOT NULL default '';

