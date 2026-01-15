ALTER TABLE `#__zhgooglemaps_maps` ADD `lang` varchar(20) NOT NULL default '';

ALTER TABLE `#__zhgooglemaps_markers` ADD `infographicstype` text NOT NULL;
ALTER TABLE `#__zhgooglemaps_markers` ADD `infographicswidth` int(5) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_markers` ADD `infographicsheight` int(5) NOT NULL default '0';

