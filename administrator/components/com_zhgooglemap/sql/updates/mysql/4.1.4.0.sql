DROP TABLE IF EXISTS `#__zhgooglemaps_marker_rates`;

CREATE TABLE `#__zhgooglemaps_marker_rates` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `rating_value` FLOAT NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

