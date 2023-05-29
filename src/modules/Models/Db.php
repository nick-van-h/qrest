<?php

namespace Qrest\Models;

use \MysqliDb;

class Db extends MysqliDb
{
    private $config;

    public function __construct()
    {
        $this->config = parse_ini_file(BASE_PATH . '/config/db.ini');
        if (!file_exists(BASE_PATH . '/config/db.ini')) {
            throw new \Error("db.ini not found");
        }
        try {
            parent::__Construct($this->config['host'], $this->config['username'], $this->config['password'], $this->config['database']);
            $this->connect();
        } catch (\Exception $e) {
            throw new \Exception('Unable to establish database connection: ' . $e->getCode() . ' | ' . $e->getMessage());
        }
        //Turn off excessive error reporting
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }

    public function updateBlob($query, $value)
    {
        // //Init PDO handler
        // $pdo = new PDO($this->config['host'], $this->config['username'], $this->config['password'], $this->config['database']);
        // $stmt = $pdo->prepare($query);

        // $stmt->bindParam($valueName, $value, PDO::PARAM_LOB);

        // return $stmt->execute();

        $query = "UPDATE items SET note_enc = ? WHERE id = 35";

        $stmt = parent::mysqli()->prepare($query);

        $stmt->bind_param('s', $value);

        // Execute the statement
        $stmt->execute();

        // Check for errors in the execution
        if ($stmt === false) {
            die("Error: " . $stmt);
        }
    }

    public function getBlob($value)
    {
        $query = "SELECT note_enc FROM items WHERE id = 35";

        $stmt = parent::mysqli()->prepare($query);

        // $stmt->bind_param('s', $value);

        // Execute the statement
        $stmt->execute();

        // Check for errors in the execution
        if ($stmt === false) {
            die("Error: " . $stmt);
        }

        $stmt->bind_result($column);
        while ($stmt->fetch()) {
            // Access the data of each row
            echo "Column 1: " . $column . "<br>";
        }
    }
}
