ZF3 Library
===========

Biblioteca com models comuns utilizados nos projetos ZF3.

Db\TableAdapter
---------------

Model para utilizar o TableGateway com funções mais comuns.

Permite criar o campo '''deleted''' onde o registro é marcado como removido e não definitavemente removido da tabela no banco do dados.

Realejo\Mail
------------

Model utilizado para enviar emails via smtp. É necessário ter as configurações definidas na pasta /config/autoload/config_email.php ou enviá-las no momento de construção do objeto.

Exemplo do arquivo:
```
<?php
return [
    'name'       => 'Nome do remetente',
    'email'      => 'email@do.remetente',
    'returnPath' => 'email@do.remetente',
    'host'       => '***REMOVED***',
    'username'   => '',
    'password'   => '',
    'port'       => '2525',
];
```

