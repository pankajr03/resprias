DROP TABLE IF EXISTS `#__zhgooglemaps_maps`;

DROP TABLE IF EXISTS `#__zhgooglemaps_marker_rates`;
DROP TABLE IF EXISTS `#__zhgooglemaps_markers`;

DROP TABLE IF EXISTS `#__zhgooglemaps_routers`;

DROP TABLE IF EXISTS `#__zhgooglemaps_paths`;

DROP TABLE IF EXISTS `#__zhgooglemaps_markergroups`;

DROP TABLE IF EXISTS `#__zhgooglemaps_maptypes`;

DROP TABLE IF EXISTS `#__zhgooglemaps_infobubbles`;

DROP TABLE IF EXISTS `#__zhgooglemaps_streetviews`;
DROP TABLE IF EXISTS `#__zhgooglemaps_weathertypes`;

DROP TABLE IF EXISTS `#__zhgooglemaps_text_overrides`;
DROP TABLE IF EXISTS `#__zhgooglemaps_marker_content`;

DROP TABLE IF EXISTS `#__zhgooglemaps_marker_buffer`;

DROP TABLE IF EXISTS `#__zhgooglemaps_log`;

DELETE FROM `#__content_types`
  WHERE `type_alias` LIKE 'com_zhgooglemap.%';