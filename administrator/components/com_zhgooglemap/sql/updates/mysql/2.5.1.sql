ALTER TABLE `#__zhgooglemaps_maps` ADD `usercontact` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_markers` ADD `contactid` int(11) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_markers` ADD `showcontact` tinyint(1) NOT NULL default '0';

