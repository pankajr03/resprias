ALTER TABLE `#__zhgooglemaps_markers` ADD `hoverhtml` text NOT NULL;

ALTER TABLE `#__zhgooglemaps_maps` ADD `hovermarker` tinyint(1) NOT NULL default '0';