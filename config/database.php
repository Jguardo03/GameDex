<?php

// * Database configuration settings
// * PDO statemenets for security and performance

class DataBase{

    private $host = "localhost";
    private $dbName = "gamdex";
    private $username = "root";
    private $password = "";
    private $charset = "utf8mb4";

    private $pdo;
    private $error;

    // Single Instance
    private static $instance = null;

    // Private Constructor

    private function __construct(){
        $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->dbName . ";charset=" . $this->charset;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true
        ];

        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Database Connection Error: " . $this->error);
            throw new Exception("Database connection error.Please try again later.");
        }
    }

    // Get the single instance of the Database class
    public static function getInstance(){
        if (self::$instance === null) {
            self::$instance = new DataBase();
        }
        return self::$instance;
    }
    // Get the PDO connection
    public function getConnection(){
        return $this->pdo;
    }

    //Prevent cloning of the instance
    private function __clone(){}

    //Prevent unserializing of the instance
    public function __wakeup(){
        throw new Exception("Cannot unserialize singleton");
    }
}
