ALTER TABLE `#__zhgooglemaps_maps` ADD  `headerhtml` text NOT NULL;
ALTER TABLE `#__zhgooglemaps_maps` ADD  `footerhtml` text NOT NULL;
ALTER TABLE `#__zhgooglemaps_maps` ADD  `headersep` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_maps` ADD  `footersep` tinyint(1) NOT NULL default '0';
