<?php

require('config.php');

class DatabaseHelper
{
    public $db_host;
    public $db_name;
    public $dbuser;
    public $dbpass;

    public function __construct($db_host, $db_name, $dbuser, $dbpass)
    {
        $this->db_host = $db_host;
        $this->db_name = $db_name;
        $this->dbuser = $dbuser;
        $this->dbpass = $dbpass;
    }

    //This functions returns a connection to the database
    public function getConnection()
    {
        try {
            $dbConnection = new PDO(
                "mysql:host=$this->db_host;dbname=$this->db_name",
                $this->dbuser,
                $this->dbpass
            );

            $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo $e->getMessage();
            return null;
        }
        return $dbConnection;
    }

    // This function accepts a table, and an associative array of key, value pairs and
    // returns true if it exists in the table or false if it does not
    public function exists($table, $row)
    {
        $sql = "select * from $table where ";
        foreach ($row as $key => $value) {
            $sql = $sql . $key . "=" . "'$value'";

            if ($value != end($row)) {
                $sql = $sql . " AND ";
            }
        }
        $sql = $sql . ";";
        try {
            $dbHandle = $this->getConnection();
            $stmt = $dbHandle->prepare($sql);
            $stmt->execute();
//            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $stmt->rowCount();
            return $count > 0 ? true : false;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    //Should return 1 since we insert one row
    public function insert($table, $row)
    {
        $sql = "insert into $table (";
        foreach ($row as $key => $value) {
            $sql = $sql . $key . ",";
        }
        $sql = trim($sql, ",") . ")";
        $sql = $sql . " values (";
        foreach ($row as $key => $value) {
            if (is_int($value)) {
                $sql = $sql . "$value" . ",";//no single quotes
            } else {
                $sql = $sql . "'$value'" . ","; //need single quotes if string
            }
        }
        $sql = trim($sql, ",") . ");";
        try {
            $dbHandle = $this->getConnection();
            $count = $dbHandle->exec($sql);
            return $count;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    //Update a single column value.
    //TODO: allow multiple column values to be updated
    public function update($table, $column, $row)
    {
        $sql = "update $table set $column = $column + 1 where ";
        foreach ($row as $key => $value) {
            $sql = $sql . $key . "=" . "'$value'";
            if ($value != end($row)) {
                $sql = $sql . " AND ";
            }
        }
        $sql = $sql . ";";
        try {
            $dbHandle = $this->getConnection();
            $stmt = $dbHandle->prepare($sql);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function executeQuery($sql) {
        try {
            $dbHandle = $this->getConnection();
            $stmt = $dbHandle->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
