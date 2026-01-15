UPDATE `#__zhgooglemaps_markers` SET `baloon`=1 WHERE `baloon` = 11;
UPDATE `#__zhgooglemaps_markers` SET `baloon`=2 WHERE `baloon` = 12;
UPDATE `#__zhgooglemaps_markers` SET `baloon`=3 WHERE `baloon` = 13;

ALTER TABLE `#__zhgooglemaps_markers` DROP COLUMN `infographicstype`;
ALTER TABLE `#__zhgooglemaps_markers` DROP COLUMN `infographicswidth`;
ALTER TABLE `#__zhgooglemaps_markers` DROP COLUMN `infographicsheight`;
