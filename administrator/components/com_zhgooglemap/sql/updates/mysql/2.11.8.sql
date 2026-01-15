ALTER TABLE `#__zhgooglemaps_weathertypes` ADD `clickable` tinyint(1) NOT NULL default '1';
ALTER TABLE `#__zhgooglemaps_weathertypes` ADD `suppressinfowindows` tinyint(1) NOT NULL default '0';

ALTER TABLE `#__zhgooglemaps_weathertypes` ADD `labelcolor` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_weathertypes` ADD `temperatureunits` tinyint(1) NOT NULL default '1';
ALTER TABLE `#__zhgooglemaps_weathertypes` ADD `windspeedunits` tinyint(1) NOT NULL default '2';
