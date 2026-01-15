DROP TABLE IF EXISTS `#__zhgooglemaps_widgettypes`;
DROP TABLE IF EXISTS `#__zhgooglemaps_adsences`;

ALTER TABLE `#__zhgooglemaps_maps` DROP COLUMN `panoramiotypeid`;
ALTER TABLE `#__zhgooglemaps_maps` DROP COLUMN `panoramioenable`;
ALTER TABLE `#__zhgooglemaps_maps` DROP COLUMN `panoramiofiltercontrol`;
ALTER TABLE `#__zhgooglemaps_maps` DROP COLUMN `panoramiofiltercontrolpos`;
ALTER TABLE `#__zhgooglemaps_maps` DROP COLUMN `panoramiouser`;
ALTER TABLE `#__zhgooglemaps_maps` DROP COLUMN `panoramiotag`;
ALTER TABLE `#__zhgooglemaps_maps` DROP COLUMN `panoramiocontrolpos`;

