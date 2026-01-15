ALTER TABLE `#__zhgooglemaps_text_overrides` ADD `panelcontrol_hint` text NOT NULL;
ALTER TABLE `#__zhgooglemaps_text_overrides` ADD `panel_detail_title` text NOT NULL;
ALTER TABLE `#__zhgooglemaps_text_overrides` ADD `panel_placemarklist_title` text NOT NULL;
ALTER TABLE `#__zhgooglemaps_text_overrides` ADD `panel_route_title` text NOT NULL;

ALTER TABLE `#__zhgooglemaps_maps` ADD `panelstate` tinyint(1) NOT NULL default '0';

