DROP TABLE IF EXISTS `#__zhgooglemaps_marker_buffer`;

CREATE TABLE `#__zhgooglemaps_marker_buffer` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `latitude` varchar(20) NOT NULL default '',
  `longitude` varchar(20) NOT NULL default '',
  `addresstext` text NOT NULL,
  `icontype` varchar(250) NOT NULL default '',
  `iconofsetx` tinyint(1) NOT NULL default '0',
  `iconofsety` tinyint(1) NOT NULL default '0',
  `description` text NOT NULL,
  `descriptionhtml` text NOT NULL,
  `hrefimage` text NOT NULL,
  `markergroup` int(11) NOT NULL default '0',
  `createdbyuser` int(11) NOT NULL default '0',
  `showuser` tinyint(1) NOT NULL default '0',
  `createddate` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `showgps` tinyint(1) NOT NULL default '0',
  `preparecontent` tinyint(1) NOT NULL default '0',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

ALTER TABLE `#__zhgooglemaps_marker_buffer` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhgooglemaps_marker_buffer` ADD INDEX `idx_markergroup` (`markergroup`);
ALTER TABLE `#__zhgooglemaps_marker_buffer` ADD INDEX `idx_createdbyuser` (`createdbyuser`);
