ALTER TABLE `#__zhgooglemaps_markergroups` ADD `markermanagerminzoom` int(3) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_markergroups` ADD `markermanagermaxzoom` int(3) NOT NULL default '18';

ALTER TABLE `#__zhgooglemaps_maps` ADD `markerclustergroup` tinyint(1) NOT NULL default '0';