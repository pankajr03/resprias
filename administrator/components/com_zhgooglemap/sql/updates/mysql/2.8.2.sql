ALTER TABLE `#__zhgooglemaps_maptypes` ADD `gettileurl` text NOT NULL;
ALTER TABLE `#__zhgooglemaps_maptypes` ADD `tilewidth` int(5) NOT NULL default '256';
ALTER TABLE `#__zhgooglemaps_maptypes` ADD `tileheight` int(5) NOT NULL default '256';
ALTER TABLE `#__zhgooglemaps_maptypes` ADD `ispng` tinyint(1) NOT NULL default '1';
ALTER TABLE `#__zhgooglemaps_maptypes` ADD `minzoom` int(3) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_maptypes` ADD `maxzoom` int(3) NOT NULL default '18';



