ALTER TABLE `#__zhgooglemaps_maps` ADD `earth` tinyint(1) NOT NULL default '0';

ALTER TABLE `#__zhgooglemaps_markers` ADD `hrefsite` text NOT NULL;
ALTER TABLE `#__zhgooglemaps_markers` ADD `hrefimage` text NOT NULL;

ALTER TABLE `#__zhgooglemaps_markers` ADD `hrefsitename` text NOT NULL;
