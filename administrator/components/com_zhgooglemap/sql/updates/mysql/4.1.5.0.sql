ALTER TABLE `#__zhgooglemaps_maps` ADD INDEX `idx_catid` (`catid`);

ALTER TABLE `#__zhgooglemaps_markers` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhgooglemaps_markers` ADD INDEX `idx_mapid` (`mapid`);
ALTER TABLE `#__zhgooglemaps_markers` ADD INDEX `idx_markergroup` (`markergroup`);
ALTER TABLE `#__zhgooglemaps_markers` ADD INDEX `idx_createdbyuser` (`createdbyuser`);

ALTER TABLE `#__zhgooglemaps_routers` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhgooglemaps_routers` ADD INDEX `idx_mapid` (`mapid`);

ALTER TABLE `#__zhgooglemaps_paths` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhgooglemaps_paths` ADD INDEX `idx_mapid` (`mapid`);
ALTER TABLE `#__zhgooglemaps_paths` ADD INDEX `idx_markergroup` (`markergroup`);

ALTER TABLE `#__zhgooglemaps_markergroups` ADD INDEX `idx_catid` (`catid`);

ALTER TABLE `#__zhgooglemaps_maptypes` ADD INDEX `idx_catid` (`catid`);

ALTER TABLE `#__zhgooglemaps_infobubbles` ADD INDEX `idx_catid` (`catid`);

ALTER TABLE `#__zhgooglemaps_streetviews` ADD INDEX `idx_catid` (`catid`);

ALTER TABLE `#__zhgooglemaps_adsences` ADD INDEX `idx_catid` (`catid`);

ALTER TABLE `#__zhgooglemaps_weathertypes` ADD INDEX `idx_catid` (`catid`);

ALTER TABLE `#__zhgooglemaps_widgettypes` ADD INDEX `idx_catid` (`catid`);

ALTER TABLE `#__zhgooglemaps_marker_rates` ADD INDEX `idx_markerid` (`markerid`);
