 CREATE TABLE IF NOT EXISTS `album`  (
  `id_int` tinyint(1) NOT NULL,
  `id_char` char(1) NOT NULL,
  `artist` varchar(100) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `deleted` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_int`, `id_char`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;