ALTER TABLE `#__zhgooglemaps_text_overrides` ADD `group_list_search` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_text_overrides` ADD `group_list_accent_side` tinyint(1) NOT NULL default '3';
ALTER TABLE `#__zhgooglemaps_text_overrides` ADD `group_list_accent` text NOT NULL;
ALTER TABLE `#__zhgooglemaps_text_overrides` ADD `group_list_mapping_type` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhgooglemaps_text_overrides` ADD `group_list_mapping` text NOT NULL;

