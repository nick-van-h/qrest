<?php

namespace Qrest\Models;

use Qrest\Models\Db;

class Setup
{
    private $db;
    private $e;

    private const NO_DB_VERSION = 'no db entry';

    public function __construct()
    {
        $this->checkDatabaseConnection();
    }

    public function getError($clearPreviousErrors = true)
    {
        $err = $this->e;
        if ($clearPreviousErrors) $this->e = '';
        return $err;
    }

    public function checkRequirements()
    {
        return ($this->checkDatabaseConnection() && $this->checkConfig() && $this->checkDbVersion());
    }

    public function checkDatabaseConnection()
    {

        //Set up the connection
        try {
            $this->db = new Db();
            $this->e = '';
            return true;
        } catch (\Exception $e) {
            $this->e = $e->getMessage() . ' - ' . $e->getTraceAsString();
            return false;
        } catch (\Error $e) {
            $this->e = $e->getMessage() . ' - ' . $e->getTraceAsString();
            return false;
        } catch (\mysqli_sql_exception $e) {
            $this->e = $e->getMessage() . ' - ' . $e->getTraceAsString();
            return false;
        }
    }

    private function checkConfig()
    {
        return ($this->checkTable('config'));
    }


    private function checkTable($tableName)
    {
        try {
            $this->db->getOne($tableName);
            $this->e = '';
            return true;
        } catch (\Exception $e) {
            $this->e = $e->getMessage() . ' - ' . $e->getTraceAsString();
            return false;
        }
    }

    public function checkDbVersion()
    {
        try {
            $curRel = $this->getCurrentVersion();
            $tgtRel = $this->getTargetVersion();
            return ($curRel == $tgtRel);
        } catch (\Error $e) {
            $this->e = $e->getMessage() . ' - ' . $e->getTraceAsString();
            return false;
        } catch (\mysqli_sql_exception $e) {
            $this->e = $e->getMessage() . ' - ' . $e->getTraceAsString();
            return false;
        }
    }

    public function getCurrentVersion()
    {
        $rel = false;
        try {
            $this->db->where('setting', 'version');
            $rel = $this->db->getValue('config', 'value_str');
        } catch (\Error $e) {
            // $this->e = $e->getMessage() . ' - ' . $e->getTraceAsString();
        } catch (\mysqli_sql_exception $e) {
            // $this->e = $e->getMessage() . ' - ' . $e->getTraceAsString();
        }
        if (!$rel) $rel = self::NO_DB_VERSION;
        return $rel;
    }

    public function getTargetVersion()
    {
        $json = $this->getReleases();
        $tgtRel = $json->latest;
        return $tgtRel;
    }

    private function getReleases()
    {
        return json_decode(file_get_contents(BASE_PATH . '/config/releaseNotes.json'));
    }

    public function updateToLatestVersion($username = null, $password = null)
    {
        //Prep data
        $curRel = $this->getCurrentVersion();
        if (!$curRel) $curRel = 'v0.0.0';
        $json = $this->getReleases();

        //Sort ascending by release version
        usort($json->releases, function ($a, $b) {
            return strcmp($a->version, $b->version);
        });

        //Loop trough the release and update if required
        foreach ($json->releases as $rel) {
            if ($rel->version > $curRel) {
                $this->updateTableDefinitions($rel->version, $username, $password);
            }
        }
        return ($this->e === '');
    }

    private function updateTableDefinitions($version, $username = null, $password = null)
    {
        //If current Db version is already latest nothing needs to be updated
        if ($this->checkDbVersion()) return;

        //Get the Db config
        $config = parse_ini_file(BASE_PATH . '/config/db.ini');
        if (is_null($username)) $username = $config['username'];
        if (is_null($password)) $password = $config['password'];

        // Connect to database & check connection
        try {
            $conn = new \mysqli($config['host'], $username, $password, $config['database']);
            if ($conn->connect_error) {
                $this->e = $conn->connect_error;
                return false;
            }
        } catch (\mysqli_sql_exception $e) {
            $this->e = $e->getMessage() . ' - ' . $e->getTraceAsString();
            return false;
        }

        //Create array of .sql files to execute
        $path    = BASE_PATH . '/config/setup/' . $version . '/';
        if (is_dir($path)) {
            $files = scandir($path);
            $files = array_diff($files, array('.', '..'));

            //Loop through list of queries & execute them
            foreach ($files as $file) {
                $this->execSingleQuery($conn, $path . $file);
            }
        }

        //Create array of post install .sql files to execute
        $path    = BASE_PATH . '/config/setup/' . $version . '/post//';
        if (is_dir($path)) {
            $files = scandir($path);
            $files = array_diff($files, array('.', '..'));

            //Loop through list of queries & execute them
            foreach ($files as $file) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);

                if ($extension === 'sql') {
                    $this->execSingleQuery($conn, $path . $file);
                } else {
                    require_once $path . $file;
                }
            }
        }

        //Update the Db version
        $this->checkDatabaseConnection(); //TODO: Investigate why/where previous 'where' statements are set tha mess up the query if this line gets removed
        $spool = $this->db->get('config');

        $data = array(
            'value_str' => $version,
            'type' => 'str',
            'setting' => 'version'
        );
        $this->db->replace('config', $data);

        // $curVersion = $this->getCurrentVersion();
        // if ($curVersion != self::NO_DB_VERSION) {
        //     $data = array(
        //         'value_str' => $version,
        //         'type' => 'str'
        //     );
        //     $this->db->where('setting', 'version');
        //     $this->db->update('config', $data);
        // } else {
        //     $data = array(
        //         'value_str' => $version,
        //         'type' => 'str',
        //         'setting' => 'version'
        //     );
        //     $this->db->insert('config', $data);
        // }

        // Close the connection
        $conn->close();

        //Return the result
        return ($this->e === '');
    }

    private function execSingleQuery($conn, $file)
    {
        if (!is_dir($file)) {
            //Read the content
            $sql = file_get_contents($file);

            // Execute the SQL query
            try {
                if (!$conn->multi_query($sql) === TRUE) {
                    $this->e .= $conn->error . '; ';
                }
            } catch (\Error $e) {
                // $this->e .= $e->getMessage() . ' - ' . $e->getTraceAsString() . '; ';
            } catch (\mysqli_sql_exception $e) {
                // $this->e .= $e->getMessage() . ' - ' . $e->getTraceAsString() . '; ';
            }
        }
    }
}
