ALTER TABLE `#__zhgooglemaps_paths` ADD `v_min_value` varchar(20) NOT NULL default '';
ALTER TABLE `#__zhgooglemaps_paths` ADD `v_max_value` varchar(20) NOT NULL default '';
ALTER TABLE `#__zhgooglemaps_paths` ADD `v_baseline_color` varchar(250) NOT NULL default '';
ALTER TABLE `#__zhgooglemaps_paths` ADD `v_gridline_color` varchar(250) NOT NULL default '';
ALTER TABLE `#__zhgooglemaps_paths` ADD `v_gridline_count` int(3) NOT NULL default '5';
ALTER TABLE `#__zhgooglemaps_paths` ADD `v_minor_gridline_color` varchar(250) NOT NULL default '';
ALTER TABLE `#__zhgooglemaps_paths` ADD `v_minor_gridline_count` int(3) NOT NULL default '0';
