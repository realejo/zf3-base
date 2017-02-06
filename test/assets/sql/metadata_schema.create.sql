CREATE TABLE IF NOT EXISTS  `metadata_schema` (
  `id_info` tinyint(1) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `nick` varchar(30) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '0',
  `ordem` tinyint(3) NOT NULL DEFAULT '0',
  `tipo` char(1) NOT NULL DEFAULT 'T',
  `obrigatorio` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_info`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
