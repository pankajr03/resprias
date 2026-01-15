DROP TABLE IF EXISTS `#__rsform_pdfs`;

DELETE FROM #__rsform_config WHERE SettingName LIKE 'pdf.%';
