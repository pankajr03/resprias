DROP TABLE IF EXISTS `#__zhgooglemaps_maps`;

CREATE TABLE `#__zhgooglemaps_maps` (
  `id` int(11) NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `width` int(5) NOT NULL default '0',
  `height` int(5) NOT NULL default '0',
  `latitude` varchar(20) NOT NULL default '',
  `longitude` varchar(20) NOT NULL default '',
  `zoom` int(3) NOT NULL default '0',
  `minzoom` int(3) NOT NULL default '0',
  `maxzoom` int(3) NOT NULL default '0',
  `draggable` tinyint(1) NOT NULL default '1',
  `doubleclickzoom` tinyint(1) NOT NULL default '0',
  `scrollwheelzoom` tinyint(1) NOT NULL default '1',
  `zoomcontrol` tinyint(1) NOT NULL default '0',
  `scalecontrol` tinyint(1) NOT NULL default '0',
  `maptype` tinyint(1) NOT NULL default '0',
  `maptypecontrol` tinyint(1) NOT NULL default '0',
  `overviewmapcontrol` tinyint(1) NOT NULL default '0',
  `rotatecontrol` tinyint(1) NOT NULL default '0',
  `pancontrol` tinyint(1) NOT NULL default '0',
  `trafficcontrol` tinyint(1) NOT NULL default '0',
  `transitcontrol` tinyint(1) NOT NULL default '0',
  `streetviewcontrol` tinyint(1) NOT NULL default '0',
  `streetview` tinyint(1) NOT NULL default '0',
  `streetviewstyleid` int(11) NOT NULL default '0',
  `bikecontrol` tinyint(1) NOT NULL default '0',
  `balloon` tinyint(1) NOT NULL default '0',
  `openballoon` tinyint(1) NOT NULL default '0',
  `pospan` tinyint(1) NOT NULL default '0',
  `posmaptype` tinyint(1) NOT NULL default '0',
  `poszoom` tinyint(1) NOT NULL default '0',
  `posscale` tinyint(1) NOT NULL default '0',
  `posstreet` tinyint(1) NOT NULL default '0',
  `description` text NOT NULL,
  `markermanager` tinyint(1) NOT NULL default '0',
  `published` tinyint(1) NOT NULL default '0',
  `markercluster` tinyint(1) NOT NULL default '0',
  `markerclustergroup` tinyint(1) NOT NULL default '0',
  `clusterzoom` int(3) NOT NULL default '0',
  `kmllayer` text NOT NULL,
  `markergroupcontrol` tinyint(1) NOT NULL default '0',
  `markergrouptype` tinyint(1) NOT NULL default '0',
  `markergroupwidth` int(5) NOT NULL default '20',
  `markergroupshowicon` tinyint(1) NOT NULL default '0',
  `markergroupshowiconall` tinyint(1) NOT NULL default '100',
  `markergroupcss` int(5) NOT NULL default '0',
  `markergroupdesc1` text NOT NULL,
  `markergroupdesc2` text NOT NULL,
  `markergrouptitle` varchar(255) NOT NULL default '',
  `markergroupsep1` tinyint(1) NOT NULL default '0',
  `markergroupsep2` tinyint(1) NOT NULL default '0',
  `markergrouporder` tinyint(1) NOT NULL default '0',
  `markergroupsearch` tinyint(1) NOT NULL default '0',
  `markerlist` tinyint(1) NOT NULL default '0',
  `markerlistpos` tinyint(1) NOT NULL default '0',
  `markerlistwidth` int(5) NOT NULL default '0',
  `markerlistheight` int(5) NOT NULL default '0',
  `markerlistbgcolor` text NOT NULL,
  `markerlistaction` tinyint(1) NOT NULL default '0',
  `markerlistcontent` tinyint(1) NOT NULL default '0',
  `markerlistbuttonpos` tinyint(1) NOT NULL default '3',
  `markerlistbuttontype` tinyint(1) NOT NULL default '0',
  `markerlistsearch` tinyint(1) NOT NULL default '0',
  `markerlistsync` tinyint(1) NOT NULL default '0',
  `headerhtml` text NOT NULL,
  `footerhtml` text NOT NULL,
  `headersep` tinyint(1) NOT NULL default '0',
  `footersep` tinyint(1) NOT NULL default '0',
  `openstreet` tinyint(1) NOT NULL default '0',
  `opentopomap` tinyint(1) NOT NULL default '0',
  `nztopomaps` tinyint(1) NOT NULL default '0',
  `placesenable` tinyint(1) NOT NULL default '0',
  `placesautocomplete` tinyint(1) NOT NULL default '0',
  `placesacwidth` int(5) NOT NULL default '70',
  `placestypeac` text NOT NULL,
  `placestype` text NOT NULL,
  `placesradius` int(10) NOT NULL default '0',
  `placesdirection` tinyint(1) NOT NULL default '0',
  `findcontrol` tinyint(1) NOT NULL default '0',
  `findwidth` int(5) NOT NULL default '0',
  `findpos` tinyint(1) NOT NULL default '2',
  `findroute` tinyint(1) NOT NULL default '0',
  `elevation` tinyint(1) NOT NULL default '0',
  `usercontact` tinyint(1) NOT NULL default '0',
  `useruser` tinyint(1) NOT NULL default '0',
  `usermarkers` tinyint(1) NOT NULL default '0',
  `usermarkersfilter` tinyint(1) NOT NULL default '0',
  `usermarkerspublished` tinyint(1) NOT NULL default '0',
  `usermarkersicon` tinyint(1) NOT NULL default '1',
  `usercontactpublished` tinyint(1) NOT NULL default '0',
  `usermarkersinsert` tinyint(1) NOT NULL default '1',
  `usermarkersupdate` tinyint(1) NOT NULL default '1',
  `usermarkersdelete` tinyint(1) NOT NULL default '1',
  `routedraggable` tinyint(1) NOT NULL default '0',
  `routeshowpanel` tinyint(1) NOT NULL default '0',
  `routeaddress` text NOT NULL,
  `autoposition` tinyint(1) NOT NULL default '0',
  `geolocationcontrol` tinyint(1) NOT NULL default '0',
  `geolocationpos` tinyint(1) NOT NULL default '2',
  `geolocationbutton` tinyint(1) NOT NULL default '1',
  `lang` varchar(20) NOT NULL default '',
  `custommaptype` tinyint(1) NOT NULL default '0',
  `custommaptypelist` text NOT NULL,
  `usercontactattributes` text NOT NULL,
  `mapstyles` text NOT NULL,
  `css2load` text NOT NULL,
  `js2load` text NOT NULL,
  `cssclassname` text NOT NULL,
  `mapbounds` varchar(100) NOT NULL default '',
  `weathertypeid` int(11) NOT NULL default '0',
  `routedriving` tinyint(1) NOT NULL default '1',
  `routewalking` tinyint(1) NOT NULL default '1',
  `routebicycling` tinyint(1) NOT NULL default '1',
  `routetransit` tinyint(1) NOT NULL default '0',
  `routeavoidhighways` tinyint(1) NOT NULL default '0',
  `routeavoidtolls` tinyint(1) NOT NULL default '0',
  `routeunitsystem` tinyint(1) NOT NULL default '0',
  `useajax` tinyint(1) NOT NULL default '0',
  `useajaxobject` tinyint(1) NOT NULL default '0',
  `zoombyfind` int(3) NOT NULL default '100',
  `markergroupctlmarker` tinyint(1) NOT NULL default '1',
  `markergroupctlpath` tinyint(1) NOT NULL default '0',
  `placemark_rating` tinyint(1) NOT NULL default '0',
  `hovermarker` tinyint(1) NOT NULL default '0',
  `hoverinfobubble` int(11) NOT NULL default '0',
  `defaultmaptypes` tinyint(1) NOT NULL default '1',
  `disableautopan` tinyint(1) NOT NULL default '0',
  `ajaxbufferplacemark` int(5) NOT NULL default '0',
  `ajaxbufferpath` int(5) NOT NULL default '0',
  `ajaxbufferroute` int(5) NOT NULL default '0',
  `ajaxgetplacemark` tinyint(1) NOT NULL default '0',
  `region` varchar(20) NOT NULL default '',
  `country` varchar(20) NOT NULL default '',
  `trafficcontrolpos` tinyint(1) NOT NULL default '3',
  `transitcontrolpos` tinyint(1) NOT NULL default '3',
  `bikecontrolpos` tinyint(1) NOT NULL default '3',
  `mapcentercontrol` tinyint(1) NOT NULL default '0',
  `mapcentercontrolpos` tinyint(1) NOT NULL default '2',
  `markerorder` tinyint(1) NOT NULL default '0',
  `markerspinner` tinyint(1) NOT NULL default '0',
  `showcreateinfo` tinyint(1) NOT NULL default '0',
  `override_id` int(11) NOT NULL default '0',
  `panelinfowin` tinyint(1) NOT NULL default '0',
  `panelwidth` int(3) NOT NULL default '300',
  `panelstate` tinyint(1) NOT NULL default '0',
  `overlayopacitycontrol` tinyint(1) NOT NULL default '0',
  `overlayopacitycontrolpos` tinyint(1) NOT NULL default '2',  
  `gogoogle` tinyint(1) NOT NULL default '0',
  `gogoogle_map` tinyint(1) NOT NULL default '0',
  `auto_center_zoom` tinyint(1) NOT NULL default '0',
  `closepopuponclick` tinyint(1) NOT NULL default '0',
  `circle_border` tinyint(1) NOT NULL default '0',
  `params` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

DROP TABLE IF EXISTS `#__zhgooglemaps_markers`;

CREATE TABLE `#__zhgooglemaps_markers` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `latitude` varchar(20) NOT NULL default '',
  `longitude` varchar(20) NOT NULL default '',
  `addresstext` text,
  `mapid` int(11) NOT NULL default '0',
  `openbaloon` tinyint(1) NOT NULL default '0',
  `actionbyclick` tinyint(1) NOT NULL default '1',
  `zoombyclick` int(3) NOT NULL default '100',
  `baloon` tinyint(1) NOT NULL default '0',
  `icontype` varchar(250) NOT NULL default '',
  `iconofsetx` tinyint(1) NOT NULL default '0',
  `iconofsety` tinyint(1) NOT NULL default '0',
  `description` text,
  `descriptionhtml` text,
  `descriptionfullhtml` text,
  `hoverhtml` text,
  `published` tinyint(1) NOT NULL default '0',
  `hrefsite` text,
  `hrefimage` text,
  `hrefimagecss` text,
  `hrefimagethumbnail` text,
  `hrefsitename` text,
  `markergroup` int(11) NOT NULL default '0',
  `markercontent` tinyint(1) NOT NULL default '0',
  `contactid` int(11) NOT NULL default '0',
  `createdbyuser` int(11) NOT NULL default '0',
  `showcontact` tinyint(1) NOT NULL default '0',
  `showuser` tinyint(1) NOT NULL default '0',
  `publish_up` DATETIME,
  `publish_down` DATETIME,
  `createddate` DATETIME,
  `userprotection` tinyint(1) NOT NULL default '0',
  `streetviewstyleid` int(11) NOT NULL default '0',
  `streetviewinfowin` tinyint(1) NOT NULL default '0',
  `streetviewinfowinw` int(3) NOT NULL default '400',
  `streetviewinfowinh` int(3) NOT NULL default '250',
  `params` text,
  `attribute1` text,
  `attribute2` text,
  `attribute3` text,
  `attribute4` text,
  `attribute5` text,
  `attribute6` text,
  `attribute7` text,
  `attribute8` text,
  `attribute9` text,
  `tabid` int(11) NOT NULL default '0',
  `tab1` text,
  `tab2` text,
  `tab3` text,
  `tab4` text,
  `tab5` text,
  `tab6` text,
  `tab7` text,
  `tab8` text,
  `tab9` text,
  `tab10` text,
  `tab11` text,
  `tab12` text,
  `tab13` text,
  `tab14` text,
  `tab15` text,
  `tab16` text,
  `tab17` text,
  `tab18` text,
  `tab19` text,
  `tab1title` varchar(250),
  `tab2title` varchar(250),
  `tab3title` varchar(250),
  `tab4title` varchar(250),
  `tab5title` varchar(250),
  `tab6title` varchar(250),
  `tab7title` varchar(250),
  `tab8title` varchar(250),
  `tab9title` varchar(250),
  `tab10title` varchar(250),
  `tab11title` varchar(250),
  `tab12title` varchar(250),
  `tab13title` varchar(250),
  `tab14title` varchar(250),
  `tab15title` varchar(250),
  `tab16title` varchar(250),
  `tab17title` varchar(250),
  `tab18title` varchar(250),
  `tab19title` varchar(250),
  `tab1image` text,
  `tab2image` text,
  `tab3image` text,
  `tab4image` text,
  `tab5image` text,
  `tab6image` text,
  `tab7image` text,
  `tab8image` text,
  `tab9image` text,
  `tab10image` text,
  `tab11image` text,
  `tab12image` text,
  `tab13image` text,
  `tab14image` text,
  `tab15image` text,
  `tab16image` text,
  `tab17image` text,
  `tab18image` text,
  `tab19image` text,
  `tab_info` tinyint(1) NOT NULL default '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `rating_value` FLOAT NOT NULL DEFAULT '0',
  `rating_count` int(11) NOT NULL DEFAULT '0',
  `labelinbackground` tinyint(1) NOT NULL default '0',
  `labelanchorx` int(5) NOT NULL default '0',
  `labelanchory` int(5) NOT NULL default '0',
  `labelclass` varchar(250),
  `labelcontent` text,
  `includeinlist` tinyint(1) NOT NULL default '1',
  `access` int(11) NOT NULL DEFAULT '1',
  `alias` varchar(255),
  `hrefcontact` text,
  `hrefarticle` text,
  `hrefdetail` text,
  `toolbarcontact` tinyint(1) NOT NULL default '0',
  `toolbararticle` tinyint(1) NOT NULL default '0',
  `toolbardetail` tinyint(1) NOT NULL default '0',
  `articleid` int(11) NOT NULL default '0',
  `attributesdetail` text,
  `userorder` int(11) NOT NULL default '0',
  `showgps` tinyint(1) NOT NULL default '0',
  `iframearticleclass` varchar(250),
  `gogoogle` tinyint(1) NOT NULL default '0',
  `preparecontent` tinyint(1) NOT NULL default '0',
  `tag_show` tinyint(1) NOT NULL default '0',
  `tag_style` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

DROP TABLE IF EXISTS `#__zhgooglemaps_routers`;

CREATE TABLE `#__zhgooglemaps_routers` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `providealt` tinyint(1) NOT NULL default '0',
  `optimizewaypoints` tinyint(1) NOT NULL default '0',
  `avoidhighways` tinyint(1) NOT NULL default '0',
  `avoidtolls` tinyint(1) NOT NULL default '0',
  `travelmode` tinyint(1) NOT NULL default '0',
  `unitsystem` tinyint(1) NOT NULL default '0',
  `route` text NOT NULL,
  `routebymarker` text NOT NULL,
  `csv_file` text NOT NULL,
  `csv_sep` varchar(1) NOT NULL default '',
  `route_data` tinyint(1) NOT NULL default '0',
  `mapid` int(11) NOT NULL default '0',
  `description` text NOT NULL,
  `descriptionhtml` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `showtype` tinyint(1) NOT NULL default '0',
  `draggable` tinyint(1) NOT NULL default '0',
  `showpanel` tinyint(1) NOT NULL default '0',
  `showpaneltotal` tinyint(1) NOT NULL default '1',
  `showdescription` tinyint(1) NOT NULL default '0',  
  `suppressmarkers` tinyint(1) NOT NULL default '0',
  `publish_up` DATETIME,
  `publish_down` DATETIME,
  `weight` tinyint(1) NOT NULL default '5',
  `color` varchar(250) NOT NULL default '#4FA4FF',
  `opacity` varchar(20) NOT NULL default '0.7',
  `params` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

DROP TABLE IF EXISTS `#__zhgooglemaps_paths`;

CREATE TABLE `#__zhgooglemaps_paths` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `weight` tinyint(1) NOT NULL default '0',
  `color` varchar(250) NOT NULL default '',
  `hover_color` varchar(250) NOT NULL default '',
  `opacity` varchar(20) NOT NULL default '',
  `path` text NOT NULL,
  `kmllayer` text NOT NULL,
  `mapid` int(11) NOT NULL default '0',
  `description` text NOT NULL,
  `descriptionhtml` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `elevation` tinyint(1) NOT NULL default '0',
  `showtype` tinyint(1) NOT NULL default '0',
  `suppressinfowindows` tinyint(1) NOT NULL default '0',
  `geodesic` tinyint(1) NOT NULL default '0',
  `publish_up` DATETIME,
  `publish_down` DATETIME,
  `infowincontent` tinyint(1) NOT NULL default '0',
  `actionbyclick` tinyint(1) NOT NULL default '0',
  `objecttype` tinyint(1) NOT NULL default '0',
  `fillcolor` varchar(250) NOT NULL default '',
  `fillopacity` varchar(20) NOT NULL default '',
  `hover_fillcolor` varchar(250) NOT NULL default '',
  `radius` varchar(250) NOT NULL default '',
  `elevationwidth` int(5) NOT NULL default '400',
  `elevationheight` int(5) NOT NULL default '200',
  `elevationcount` int(5) NOT NULL default '256',
  `elevationcountkml` int(5) NOT NULL default '0',
  `elevationicontype` varchar(250) NOT NULL default '',
  `elevationbaseline` int(11) NOT NULL default '0',
  `v_min_value` varchar(20) NOT NULL default '',
  `v_max_value` varchar(20) NOT NULL default '',
  `v_baseline_color` varchar(250) NOT NULL default '',
  `v_gridline_color` varchar(250) NOT NULL default '',
  `v_gridline_count` int(3) NOT NULL default '5',
  `v_minor_gridline_color` varchar(250) NOT NULL default '',
  `v_minor_gridline_count` int(3) NOT NULL default '0',
  `background_color_stroke` varchar(250) NOT NULL default '',
  `background_color_width` tinyint(1) NOT NULL default '0',
  `background_color_fill` varchar(250) NOT NULL default '',
  `markergroup` int(11) NOT NULL default '0',
  `hoverhtml` text NOT NULL,
  `hrefsite` text NOT NULL,
  `hrefsitename` text NOT NULL, 
  `imgurl` text NOT NULL,
  `imgclickable` tinyint(1) NOT NULL default '0',  
  `imgbounds` varchar(100) NOT NULL default '',
  `imgopacity` varchar(20) NOT NULL default '',
  `imgopacitymanage` tinyint(1) NOT NULL default '1',
  `params` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

DROP TABLE IF EXISTS `#__zhgooglemaps_markergroups`;

CREATE TABLE `#__zhgooglemaps_markergroups` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `icontype` varchar(250) NOT NULL default '',
  `iconofsetx` tinyint(1) NOT NULL default '0',
  `iconofsety` tinyint(1) NOT NULL default '0',
  `overridegroupicon` tinyint(1) NOT NULL default '0',
  `overridemarkericon` tinyint(1) NOT NULL default '0',
  `markermanagerminzoom` int(3) NOT NULL default '0',
  `markermanagermaxzoom` int(3) NOT NULL default '18',
  `activeincluster` tinyint(1) NOT NULL default '0',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `publish_up` DATETIME,
  `publish_down` DATETIME,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `userorder` int(11) NOT NULL default '0',
  `params` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

DROP TABLE IF EXISTS `#__zhgooglemaps_maptypes`;

CREATE TABLE `#__zhgooglemaps_maptypes` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `gettileurl` text NOT NULL,
  `tilewidth` int(5) NOT NULL default '256',
  `tileheight` int(5) NOT NULL default '256',
  `ispng` tinyint(1) NOT NULL default '1',
  `minzoom` int(3) NOT NULL default '0',
  `maxzoom` int(3) NOT NULL default '18',
  `opacity` varchar(20) NOT NULL default '',
  `opacitymanage` tinyint(1) NOT NULL default '1',
  `layertype` tinyint(1) NOT NULL default '1',
  `projectionglobal` text NOT NULL,
  `projectiondefinition` text NOT NULL,
  `fromlatlngtopoint` text NOT NULL,
  `frompointtolatlng` text NOT NULL,
  `publish_up` DATETIME,
  `publish_down` DATETIME,
  `params` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

DROP TABLE IF EXISTS `#__zhgooglemaps_infobubbles`;

CREATE TABLE `#__zhgooglemaps_infobubbles` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `shadowstyle` int(3) NOT NULL default '0',
  `padding` varchar(50) NOT NULL default '',
  `borderradius` varchar(50) NOT NULL default '',
  `borderwidth` varchar(50) NOT NULL default '',
  `bordercolor` varchar(50) NOT NULL default '',
  `backgroundcolor` varchar(50) NOT NULL default '',
  `minwidth` varchar(50) NOT NULL default '',
  `maxwidth` varchar(50) NOT NULL default '',
  `minheight` varchar(50) NOT NULL default '',
  `maxheight` varchar(50) NOT NULL default '',
  `arrowsize` varchar(50) NOT NULL default '',
  `arrowposition` varchar(50) NOT NULL default '',
  `arrowstyle` int(3) NOT NULL default '0',
  `disableautopan` tinyint(1) NOT NULL default '0',
  `disableanimation` tinyint(1) NOT NULL default '0',
  `hideclosebutton` tinyint(1) NOT NULL default '0',
  `backgroundclassname` varchar(250) NOT NULL default '',
  `publish_up` DATETIME,
  `publish_down` DATETIME,
  `params` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

DROP TABLE IF EXISTS `#__zhgooglemaps_streetviews`;

CREATE TABLE `#__zhgooglemaps_streetviews` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `publish_up` DATETIME,
  `publish_down` DATETIME,
  `heading` varchar(10) NOT NULL default '0',
  `pitch` varchar(10) NOT NULL default '0',
  `zoom` int(3) NOT NULL default '1',
  `params` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

DROP TABLE IF EXISTS `#__zhgooglemaps_weathertypes`;

CREATE TABLE `#__zhgooglemaps_weathertypes` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `publish_up` DATETIME,
  `publish_down` DATETIME,
  `clickable` tinyint(1) NOT NULL default '1',
  `suppressinfowindows` tinyint(1) NOT NULL default '0',
  `labelcolor` tinyint(1) NOT NULL default '0',
  `temperatureunits` tinyint(1) NOT NULL default '1',
  `windspeedunits` tinyint(1) NOT NULL default '2',
  `weatherlayer` tinyint(1) NOT NULL default '1',
  `cloudlayer` tinyint(1) NOT NULL default '1',
  `params` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

DROP TABLE IF EXISTS `#__zhgooglemaps_marker_rates`;

CREATE TABLE `#__zhgooglemaps_marker_rates` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `markerid` int(11) NOT NULL default '0',
  `rating_value` FLOAT NOT NULL DEFAULT '0',
  `rating_date` DATETIME,
  `ip` text NOT NULL,
  `hostname` text NOT NULL,
  `createdbyuser` int(11) NOT NULL default '0',
  `params` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

DROP TABLE IF EXISTS `#__zhgooglemaps_text_overrides`;

CREATE TABLE `#__zhgooglemaps_text_overrides` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `publish_up` DATETIME,
  `publish_down` DATETIME,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `placemark_list_title` text NOT NULL,
  `placemark_list_button_title` text NOT NULL,
  `placemark_list_button_hint` text NOT NULL,
  `panelcontrol_hint` text NOT NULL,
  `panel_detail_title` text NOT NULL,
  `panel_placemarklist_title` text NOT NULL,
  `panel_route_title` text NOT NULL,
  `panel_group_title` text NOT NULL,
  `group_list_title` text NOT NULL,
  `gogoogle_text` text NOT NULL,
  `placemark_list_search` tinyint(1) NOT NULL default '0',
  `placemark_list_mapping_type` tinyint(1) NOT NULL default '0',
  `placemark_list_accent` text NOT NULL,
  `placemark_list_mapping` text NOT NULL,
  `placemark_list_accent_side` tinyint(1) NOT NULL default '3',
  `group_list_search` tinyint(1) NOT NULL default '0',
  `group_list_mapping_type` tinyint(1) NOT NULL default '0',
  `group_list_accent` text NOT NULL,
  `group_list_mapping` text NOT NULL,
  `group_list_accent_side` tinyint(1) NOT NULL default '3',
  `placemark_date_fmt` text NOT NULL,
  `circle_radius` varchar(250) NOT NULL default '',
  `circle_stroke_weight` tinyint(1) NOT NULL default '4',
  `circle_stroke_color` varchar(250) NOT NULL default '',
  `circle_stroke_opacity` varchar(20) NOT NULL default '',
  `circle_fill_color` varchar(250) NOT NULL default '',
  `circle_fill_opacity` varchar(20) NOT NULL default '',
  `circle_draggable` tinyint(1) NOT NULL default '1',
  `circle_editable` tinyint(1) NOT NULL default '1',
  `circle_info` tinyint(1) NOT NULL default '1',
  `params` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

DROP TABLE IF EXISTS `#__zhgooglemaps_marker_content`;

CREATE TABLE `#__zhgooglemaps_marker_content` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `publish_up` DATETIME,
  `publish_down` DATETIME,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

DROP TABLE IF EXISTS `#__zhgooglemaps_marker_buffer`;

CREATE TABLE `#__zhgooglemaps_marker_buffer` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `latitude` varchar(20) NOT NULL default '',
  `longitude` varchar(20) NOT NULL default '',
  `published` tinyint(1) NOT NULL default '0',
  `publish_up` DATETIME,
  `publish_down` DATETIME,
  `addresstext` text,
  `icontype` varchar(250) NOT NULL default '',
  `iconofsetx` tinyint(1) NOT NULL default '0',
  `iconofsety` tinyint(1) NOT NULL default '0',
  `description` text,
  `descriptionhtml` text,
  `hrefimage` text,
  `markergroup` int(11) NOT NULL default '0',
  `createdbyuser` int(11) NOT NULL default '0',
  `showuser` tinyint(1) NOT NULL default '0',
  `createddate` DATETIME,
  `showgps` tinyint(1) NOT NULL default '0',
  `preparecontent` tinyint(1) NOT NULL default '0',
  `markercontent` tinyint(1) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0',
  `params` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

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
  `publish_up` DATETIME,
  `publish_down` DATETIME,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;


ALTER TABLE `#__zhgooglemaps_maps` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhgooglemaps_maps` ADD INDEX `idx_override` (`override_id`);

ALTER TABLE `#__zhgooglemaps_markers` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhgooglemaps_markers` ADD INDEX `idx_mapid` (`mapid`);
ALTER TABLE `#__zhgooglemaps_markers` ADD INDEX `idx_markergroup` (`markergroup`);
ALTER TABLE `#__zhgooglemaps_markers` ADD INDEX `idx_createdbyuser` (`createdbyuser`);
ALTER TABLE `#__zhgooglemaps_markers` ADD INDEX `idx_access` (`access`);
ALTER TABLE `#__zhgooglemaps_markers` ADD INDEX `idx_alias` (`alias`);
ALTER TABLE `#__zhgooglemaps_markers` ADD INDEX `idx_tabid` (`tabid`);
ALTER TABLE `#__zhgooglemaps_markers` ADD INDEX `idx_articleid` (`articleid`);
ALTER TABLE `#__zhgooglemaps_markers` ADD INDEX `idx_contactid` (`contactid`);
ALTER TABLE `#__zhgooglemaps_markers` ADD INDEX `idx_userorder` (`userorder`);

ALTER TABLE `#__zhgooglemaps_routers` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhgooglemaps_routers` ADD INDEX `idx_mapid` (`mapid`);

ALTER TABLE `#__zhgooglemaps_paths` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhgooglemaps_paths` ADD INDEX `idx_mapid` (`mapid`);
ALTER TABLE `#__zhgooglemaps_paths` ADD INDEX `idx_markergroup` (`markergroup`);

ALTER TABLE `#__zhgooglemaps_markergroups` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhgooglemaps_markergroups` ADD INDEX `idx_userorder` (`userorder`);

ALTER TABLE `#__zhgooglemaps_maptypes` ADD INDEX `idx_catid` (`catid`);

ALTER TABLE `#__zhgooglemaps_infobubbles` ADD INDEX `idx_catid` (`catid`);

ALTER TABLE `#__zhgooglemaps_streetviews` ADD INDEX `idx_catid` (`catid`);


ALTER TABLE `#__zhgooglemaps_weathertypes` ADD INDEX `idx_catid` (`catid`);

ALTER TABLE `#__zhgooglemaps_marker_rates` ADD INDEX `idx_markerid` (`markerid`);

ALTER TABLE `#__zhgooglemaps_text_overrides` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhgooglemaps_marker_content` ADD INDEX `idx_catid` (`catid`);

ALTER TABLE `#__zhgooglemaps_marker_buffer` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhgooglemaps_marker_buffer` ADD INDEX `idx_markergroup` (`markergroup`);
ALTER TABLE `#__zhgooglemaps_marker_buffer` ADD INDEX `idx_createdbyuser` (`createdbyuser`);

ALTER TABLE `#__zhgooglemaps_log` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhgooglemaps_log` ADD INDEX `idx_ext` (`extension`);



INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) 
VALUES (	
	'ZhGoogleMap Category', 
	'com_zhgooglemap.category', 
	'{"special":
		{"dbtable":"#__categories"
		,"key":"id"
		,"type":"CategoryTable"
		,"prefix":"Joomla\\\\Component\\\\Categories\\\\Administrator\\\\Table\\\\"
		,"config":"array()"}
	 ,"common":
		{"dbtable":"#__ucm_content"
		,"key":"ucm_id"
		,"type":"Corecontent"
		,"prefix":"Joomla\\\\CMS\\\\Table\\\\"
		,"config":"array()"}}'
	,''
	, '{"common": {
        "core_content_item_id": "id",
        "core_title": "title",
        "core_state": "published",
        "core_alias": "alias",
        "core_created_time": "created_time",
        "core_modified_time": "modified_time",
        "core_body": "description",
        "core_hits": "hits",
        "core_publish_up": "null",
        "core_publish_down": "null",
        "core_access": "access",
        "core_params": "params",
        "core_featured": "null",
        "core_metadata": "metadata",
        "core_language": "language",
        "core_images": "null",
        "core_urls": "null",
        "core_version": "version",
        "core_ordering": "null",
        "core_metakey": "metakey",
        "core_metadesc": "metadesc",
        "core_catid": "parent_id",
        "asset_id": "asset_id"
    },
    "special": {
        "parent_id": "parent_id",
        "lft": "lft",
        "rgt": "rgt",
        "level": "level",
        "path": "path",
        "extension": "extension",
        "note": "note"
    }}'
	,''
	, ''
	);
	

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) 
VALUES (	
	'ZhGoogleMap Marker', 
	'com_zhgooglemap.mapmarker', 
	'{"special":
		{"dbtable":"#__zhgooglemaps_markers"
		,"key":"id"
		,"type":"MapmarkerTable"
		,"prefix":"ZhukDL\\\\Component\\\\ZhGoogleMap\\\\Administrator\\\\Table\\\\"
		,"config":"array()"}
	 ,"common":
		{"dbtable":"#__ucm_content"
		,"key":"ucm_id"
		,"type":"Corecontent"
		,"prefix":"Joomla\\\\CMS\\\\Table\\\\"
		,"config":"array()"}}'
	,''
	, '{"common":
			{"core_content_item_id":"id"
			,"core_title":"title"
			,"core_state":"null"
			,"core_alias":"alias"
			,"core_created_time":"null"
			,"core_modified_time":"null"
			,"core_body":"null"
			, "core_hits":"null"
			,"core_publish_up":"publish_up"
			,"core_publish_down":"publish_down"
			,"core_access":"access"
			, "core_params":"null"
			, "core_featured":"null"
			, "core_metadata":"null"
			, "core_language":"null"
			, "core_images":"null"
			, "core_urls":"null"
			, "core_version":"null"
			, "core_ordering":"ordering"
			, "core_metakey":"null"
			, "core_metadesc":"null"
			, "core_catid":"catid"
			, "core_xreference":"null"
			, "asset_id":"null"}
			, "special":{"fulltext":"null"}}'
	,''
	, ''
	);
	

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) 
VALUES (	
	'ZhGoogleMap Path', 
	'com_zhgooglemap.mappath', 
	'{"special":
		{"dbtable":"#__zhgooglemaps_paths"
		,"key":"id"
		,"type":"MappathTable"
		,"prefix":"ZhukDL\\\\Component\\\\ZhGoogleMap\\\\Administrator\\\\Table\\\\"
		,"config":"array()"}
	 ,"common":
		{"dbtable":"#__ucm_content"
		,"key":"ucm_id"
		,"type":"Corecontent"
		,"prefix":"Joomla\\\\CMS\\\\Table\\\\"
		,"config":"array()"}}'
	,''
	, '{"common":
			{"core_content_item_id":"id"
			,"core_title":"title"
			,"core_state":"null"
			,"core_alias":"alias"
			,"core_created_time":"null"
			,"core_modified_time":"null"
			,"core_body":"null"
			, "core_hits":"null"
			,"core_publish_up":"publish_up"
			,"core_publish_down":"publish_down"
			,"core_access":"access"
			, "core_params":"null"
			, "core_featured":"null"
			, "core_metadata":"null"
			, "core_language":"null"
			, "core_images":"null"
			, "core_urls":"null"
			, "core_version":"null"
			, "core_ordering":"ordering"
			, "core_metakey":"null"
			, "core_metadesc":"null"
			, "core_catid":"catid"
			, "core_xreference":"null"
			, "asset_id":"null"}
			, "special":{"fulltext":"null"}}'
	,''
	, ''
	);
	