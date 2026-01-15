DROP TABLE IF EXISTS `#__securitycheck`;
CREATE TABLE `#__securitycheck` (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`Product` VARCHAR(35) NOT NULL,
`Type` VARCHAR(35),
`Installedversion` VARCHAR(30) DEFAULT '---',
`Vulnerable` VARCHAR(10) NOT NULL DEFAULT 'No',
PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `#__securitycheck_db`;
CREATE TABLE `#__securitycheck_db` (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`Product` VARCHAR(35) NOT NULL,
`Type` VARCHAR(35),
`Vulnerableversion` VARCHAR(10) DEFAULT '---',
`modvulnversion` VARCHAR(2) DEFAULT '==',
`Joomlaversion` VARCHAR(30) DEFAULT 'Notdefined',
`modvulnjoomla` VARCHAR(20) DEFAULT '==',
PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `#__securitycheck_db` (`product`,`type`,`vulnerableversion`,`modvulnversion`,`Joomlaversion`,`modvulnjoomla`) VALUES 
('Joomla!','core','4.0.1','<=','4','>='),
('Joomla!','core','4.1.2','<=','4','>='),
('Joomla!','core','4.2.0','==','4','>='),
('Joomla!','core','4.2.3','<=','4','>='),
('com_eshop','component','3.6.0','==','4','>='),
('com_edocman','component','1.23.3','==','4','>='),
('com_joomrecipe','component','4.2.2','==','4','>='),
('com_opencart','component','3.0.3.19','==','4','>='),
('com_vikappointments','component','1.7.3','==','4','>='),
('com_vikrentcar','component','1.14','==','4','>='),
('com_vikbooking','component','1.15.0','==','4','>='),
('com_career','component','3.3.0','==','4','>='),
('com_solidres','component','2.12.9','==','4','>='),
('com_rentalotplus','component','19.05','==','4','>='),
('com_oscommerce','component','3.4','==','4','>='),
('com_easyshop','component','1.4.1','==','4','>='),
('com_jsjobs','component','1.3.6','==','4','>='),
('Joomla!','core','4.2.4','<=','4','>='),
('com_kunena','component','6.0.4','<=','4','>='),
('Joomla!','core','4.2.6','<=','4','>='),
('Joomla!','core','4.2.7','<=','4','>='),
('Joomla!','core','4.3.1','<=','4','>='),
('com_jbusinessdirectory','component','5.7.7','<=','4','>='),
('com_hikashop','component','4.7.2','<=','4','>='),
('com_jchoptimize','component','8.0.4','<=','4','>='),
('com_jlexreview','component','6.0.1','<=','4','>='),
('com_jlexguestbook','component','1.6.4','<=','4','>='),
('Joomla!','core','4.4.2','<=','4','>='),
('Joomla!','core','5.0.2','<=','4','>='),
('Joomla!','core','4.4.1','<=','4','>='),
('Joomla!','core','5.1.1','<=','5','>='),
('Joomla!','core','4.4.6','<=','4','>='),
('Joomla!','core','5.1.2','<=','5','>='),
('com_convertforms','component','4.4.7','<=','4','>='),
('com_convertforms','component','4.4.7','<=','5','>='),
('Joomla!','core','4.4.12','<=','4','>='),
('Joomla!','core','5.2.5','<=','5','>='),
('com_kunena','component','6.4.2','<=','5','>='),
('Joomla!','core','4.4.13','<=','4','>='),
('Joomla!','core','5.3.3','<=','5','>=');

CREATE TABLE IF NOT EXISTS `#__securitycheck_logs` (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ip` VARCHAR(255) NOT NULL,
`time` DATETIME NOT NULL,
`tag_description` VARCHAR(50),
`description` VARCHAR(300) NOT NULL,
`type` VARCHAR(50),
`uri` VARCHAR(100),
`component` VARCHAR(150) DEFAULT '---',
`marked` TINYINT(1) DEFAULT 0,
`original_string` MEDIUMTEXT,
PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `#__securitycheck_file_permissions`;

DROP TABLE IF EXISTS `#__securitycheck_file_manager`;
CREATE TABLE IF NOT EXISTS `#__securitycheck_file_manager` (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`last_check` DATETIME,
`files_scanned` INT(10) DEFAULT 0,
`files_with_incorrect_permissions` INT(10) DEFAULT 0,
`estado` VARCHAR(40) DEFAULT 'IN_PROGRESS',
`estado_clear_data` VARCHAR(40) DEFAULT 'DELETING_ENTRIES',
PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `#__securitycheck_file_manager` (`estado`,`estado_clear_data`) VALUES 
('ENDED','DELETING_ENTRIES');

CREATE TABLE IF NOT EXISTS `#__securitycheck_storage` (
`storage_key` varchar(100) NOT NULL,
`storage_value` longtext NOT NULL,
PRIMARY KEY (`storage_key`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `#__securitycheckpro_update_database`;
CREATE TABLE IF NOT EXISTS `#__securitycheckpro_update_database` (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`version` VARCHAR(10),
`last_check` DATETIME,
`message` VARCHAR(300),
PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `#__securitycheckpro_update_database` (`version`) VALUES ('1.3.31');