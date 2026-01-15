ALTER TABLE `#__zhgooglemaps_markers` ADD `hrefcontact` text NOT NULL;
ALTER TABLE `#__zhgooglemaps_markers` ADD `hrefarticle` text NOT NULL;
ALTER TABLE `#__zhgooglemaps_markers` ADD `hrefdetail` text NOT NULL;
ALTER TABLE `#__zhgooglemaps_markers` ADD `toolbarcontact` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_markers` ADD `toolbararticle` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_markers` ADD `toolbardetail` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_markers` ADD `articleid` int(11) NOT NULL default '0';