ALTER TABLE `#__zhgooglemaps_markers` ADD `markergroup` int(11) NOT NULL default '0';

CREATE TABLE `#__zhgooglemaps_markergroups` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `icontype` varchar(50) NOT NULL default '',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM CHARACTER SET `utf8`;



ALTER TABLE `#__zhgooglemaps_maps` ADD `markermanager` tinyint(1) NOT NULL default '0';

