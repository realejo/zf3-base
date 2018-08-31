<?php
namespace Realejo\Backup;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAbstractServiceFactory;

/**
 * Controle de backup
*
* @todo verificar se exite o mysqldump e zip instalado
*/
class BackupService
{

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $dumpPath;

    /**
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config)
    {
        foreach(['hostname', 'username', 'password', 'database', 'driver'] as $key) {
            if (isset($config[$key])) {
                $this->config[$key] = $config[$key];
            }
        }

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Creates a zip file with selected tables and returns the path
     *
     * @param array $tables OPTIONAL Tables to be dumped
     *
     * @return string
     */
    public function create($tables = null)
    {
        $config = $this->getConfig();

        if (empty($config)) {
            throw new \RuntimeException('Config not defined');
        }

        $dumpPath = $this->getPath();
        if (empty($dumpPath)) {
            throw new \RuntimeException('Dump path not defined');
        }

        // Creates a temp file to save the password.
        // Using it directly in command it's a security hazard and mysqldump will complain
        $tempConfig = tmpfile();
        fwrite($tempConfig, "[client]\nhost={$config['hostname']}\nuser={$config['username']}\npassword={$config['password']}");
        $tempConfigPath = stream_get_meta_data($tempConfig)['uri'];

        // Defines the zip file location and name
        $backupFile = $dumpPath . "/dump-{$config['database']}-" . date('Ymd-Hi') . '.zip';

        if (empty($tables)) {
            $tempFilename = $dumpPath .'/'. date('Ymd-Hi') . '.sql';

            // Creates the mysqldump command
            $command  = "mysqldump --defaults-extra-file=$tempConfigPath"
                . ' --opt --quote-names --default-character-set=utf8 --dump-date'
                . " {$config['database']} > $tempFilename;";
            system($command);

            // Creates ZIP with the dump file and remove it
            $command = "zip -mjq  $backupFile $tempFilename";
            system($command);
        } else {

            // creates a temp path to save the dump files
            $dumpTempFolder = $dumpPath . '/' . date('Ymd-Hi');
            if (!file_exists($dumpTempFolder)) {
                $oldumask = umask(0);
                if (!@mkdir($dumpTempFolder, 0777, true) && !is_dir($dumpTempFolder)) {
                    throw new \RuntimeException("Não foi possível criar a pasta $dumpTempFolder");
                }
                umask($oldumask);
            }

            foreach ($tables as $k => $tableName) {
                $tempFilename = $dumpTempFolder . '/' . $tableName . '.sql';

                // Creates the mysqldump command
                $command  = "mysqldump --defaults-extra-file=$tempConfigPath"
                    . ' --opt --quote-names --default-character-set=utf8 --dump-date'
                    . " {$config['database']} $tableName > $tempFilename;";
                system($command);
            }

            // Creates ZIP with the dump file
            $command = "zip -mjq $backupFile $dumpTempFolder/*.sql";
            system($command);

            // Remove temp folder
            rmdir($dumpTempFolder);
        }

        // Removes tempfile
        fclose($tempConfig);

        // Return the zip filename
        return $backupFile;
    }

    /**
     * Creates a script to restore the dump fle
     * @return string file name
     */
    public function restoreScript()
    {
        // Creates the script
        $script = <<<'RESTORESCRIPT'
#!/bin/bash
ZIPFILE=$1

if [ "$ZIPFILE" = "" ]; then
	echo "Usage: restore.sh zipname";
	exit 1;
fi;

if [ ! -f $ZIPFILE ]; then
	echo "Usage: restore.sh zipname";
	exit 1;
fi;

echo -e "Restoring \e[1m$ZIPFILE\e[0m"

read -p "Restore all tables [Y,n]: " RESTOREALL
case $RESTOREALL in
	N|n)
		RESTOREALL="N"
		;;
	*)
		RESTOREALL="Y"
		;;
esac

read -p "HOST[{{hostname}}]: " HOST
if [ "$HOST" = "" ];	then
	HOST="{{hostname}}"
fi

read -p "DATABASE[{{database}}]: " DATABASE
if [ "$DATABASE" = "" ];	then
	DATABASE="{{database}}"
fi

read -p "USER[{{user}}]: " USER
if [ "$USER" = "" ]; then
	USER="{{user}}"
fi

read -s -p "PASSWORD: " PASSWORD

TEMPCONFIG="
[client]
host = $HOST
database = $DATABASE
user = $USER
password = $PASSWORD
"

echo
echo -n "Checking connectivity $USER@$HOST, DATABASE:$DATABASE ... "

if mysql --defaults-extra-file=<(printf "$TEMPCONFIG") -e ";"; then
	echo "ok"
else
	echo "Could not connect to MySQL server"
	exit 1
fi
echo

echo -n "Creating temp folder... "
TEMPDIR="$(mktemp -d $(basename $0).XXXXXXXXXX)"
echo "ok"
echo

echo -n "Extracting zip file $ZIPFILE ... "
if unzip -q $ZIPFILE -d $TEMPDIR; then
	echo "ok"
else
	echo "Zip file not found"
	rmdir $TEMPDIR;
	exit 1
fi
echo

for SQLFILE in "$TEMPDIR"/*.sql
do
	RESTOREFILE="Y"
	if [ "$RESTOREALL" == "N" ]; then
		echo
		read -p "Restore $(basename "$SQLFILE") [y,N]: " RESTOREFILE
		case $RESTOREFILE in
			Y|y)
				RESTOREFILE="Y"
				;;
			*)
				RESTOREFILE="N"
				;;
		esac
	fi
	if [ "$RESTOREFILE" == "N" ]; then
		echo -n -e "\e[2mSkipping $(basename "$SQLFILE") ... "
	else
		echo -n -e "Restoring \e[1m$(basename "$SQLFILE")\e[0m ... "
		mysql --defaults-extra-file=<(printf "$TEMPCONFIG") < $SQLFILE;
	fi
	rm -f $SQLFILE;
	echo -e "ok\e[0m"
done

echo -n "Finishing ... "
rmdir $TEMPDIR;
echo "ok"

echo
echo "*** Restore complete ***"
echo
RESTORESCRIPT;

        $config = $this->getConfig();
        $script = str_replace(
            ['{{hostname}}',      '{{database}}',      '{{user}}'],
            [$config['hostname'], $config['database'], $config['username']],
        $script);

	    // Returns the script
	    return $script;
    }

    /**
     * Return the path where the dump should be saved
     *
     * @return string
     */
    public function getPath():string
    {
        return $this->dumpPath;
    }

    /**
     * Defines the path to save the dump
     * @param string $path
     * @return BackupService
     */
    public function setDumpPath(string $path)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Path '$path' does not exists");
        }

        if (!is_writable($path)) {
            throw new \InvalidArgumentException("Path '$path' is not writable");
        }

        $this->dumpPath = $path;

        return $this;
    }

    public function getTableInfo()
    {
        $config = $this->getConfig();

        // Recover tables from database
        $adapter =  new Adapter($config);
        $tables = $adapter->query(
            "SELECT * FROM `INFORMATION_SCHEMA`.`TABLES` where `TABLE_SCHEMA` ='{$config['database']}' and `TABLE_TYPE` = 'BASE TABLE'",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray();

        // Collect table information
        $totalTableSize = $totalIndexSize = $totalOverheadSize = 0;
        foreach ($tables as $k => $v) {
            $tables[$k] = [
                'table-name' => $v['TABLE_NAME'],
                'table-size' => $v['DATA_LENGTH'],
                'index-size' => $v['INDEX_LENGTH'],
                'overhead-size' => $v['DATA_FREE'],
                'total-size' => $v['DATA_LENGTH'] + $v['INDEX_LENGTH'] + $v['DATA_FREE']
            ];

            $totalTableSize += $tables[$k]['total-size'];
            $totalIndexSize += $v['INDEX_LENGTH'];
            $totalOverheadSize += $v['DATA_FREE'];
        }

        // Return view data
        return [
            'tables' => $tables,
            'totalTables' => count($tables),
            'totalTableSize' => $totalTableSize,
            'totalIndexSize' => $totalIndexSize,
            'totalOverheadSize' => $totalOverheadSize,
        ];
    }
}
