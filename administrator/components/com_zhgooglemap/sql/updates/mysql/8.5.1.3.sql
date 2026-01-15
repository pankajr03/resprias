DROP TABLE IF EXISTS `#__zhgooglemaps_log`;

CREATE TABLE `#__zhgooglemaps_log` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `extension` varchar(250) NOT NULL default '',
  `kind` varchar(250) NOT NULL default '',
  `title` varchar(250) NOT NULL default '',
  `description` text NOT NULL,
  `remarks` text NOT NULL,
  `id_target` int(11) NOT NULL default '0',
  `id_source` int(11) NOT NULL default '0',
  `id_find` int(11) NOT NULL default '0',
  `published` tinyint(1) NOT NULL default '0',
  `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

ALTER TABLE `#__zhgooglemaps_log` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhgooglemaps_log` ADD INDEX `idx_ext` (`extension`);