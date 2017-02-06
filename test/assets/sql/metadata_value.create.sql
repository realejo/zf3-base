CREATE TABLE IF NOT EXISTS `metadata_value` (
  `fk_reference` smallint(4) unsigned NOT NULL,
  `fk_info` tinyint(1) unsigned NOT NULL,
  `value_date` date DEFAULT NULL,
  `value_datetime` datetime DEFAULT NULL,
  `value_integer` int(11) DEFAULT NULL,
  `value_boolean` tinyint(1) DEFAULT NULL,
  `value_text` text,
  `value_decimal` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`fk_reference`,`fk_info`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
