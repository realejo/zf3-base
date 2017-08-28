CREATE TABLE IF NOT EXISTS  `metadata_schema` (
  `id_info` tinyint(1) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `nick` varchar(30) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `available` tinyint(1) NOT NULL DEFAULT '0',
  `order` tinyint(3) NOT NULL DEFAULT '0',
  `type` char(1) NOT NULL DEFAULT 'T',
  `required` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_info`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
