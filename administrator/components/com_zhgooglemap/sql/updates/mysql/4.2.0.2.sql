ALTER TABLE `#__zhgooglemaps_markers` ADD `labelinbackground` tinyint(1) NOT NULL default '0';

ALTER TABLE `#__zhgooglemaps_markers` ADD `labelanchorx` int(5) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_markers` ADD `labelanchory` int(5) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_markers` ADD `labelclass` varchar(250) NOT NULL default '';
ALTER TABLE `#__zhgooglemaps_markers` ADD `labelcontent` text NOT NULL;
