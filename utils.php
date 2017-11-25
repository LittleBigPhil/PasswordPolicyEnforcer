<?php

//Allows stateful editing of the HTML
//Automatically echos on destruction
//Example use :
//
class HTMLDocument {
    private $bodyMarkup = array();
    private $title = "";

    public function addBodyHTML($tags) {
        $this->bodyMarkup[] = $tags;
    }
    public function setTitle($title) {
        $this->title = $title;
    }

    public function __destruct() {
        echo
        '<!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="utf-8" />
                <title>'

        . $this->title

        . '</title>
            </head>
            <body>
            <caption><h1 style="text-align:center">'
        . $this->title
        . '</h1></caption>';
        
        foreach ($this->bodyMarkup as $bodyString) {
            echo $bodyString;
        }

        echo   '</body>
        </html>';
    }
}


//Wrapper around the default php session interface
//
class Session {
    private $lifetime = 600;

    public function __construct() {
        if(session_id() == '') {
            session_set_cookie_params($this->lifetime);
            session_start();
        } else {
            setcookie(session_name(), session_id(), time() + $this->lifetime);
        }
    }

    //ToDo: make this use the maybe type
    public function tryGetVar($name, $func, $elseFunc = NULL) {
        if (isset($_SESSION[$name])) {
            $func($_SESSION[$name]);
            return TRUE;
        } else {
            if ($elseFunc != NULL) {
                $elseFunc();
            }
            return FALSE;
        }
    }

    public function unsafeGetVar($name) {
        return $_SESSION[$name];
    }
    public function setVar($name, $value) {
        $_SESSION[$name] = $value;
    }
}


//Wrapper around the default php database connection
//Uses RAII, so the connection automatically closes when object goes out of scope
class DatabaseConnection {
    
    public static function makeConnection($username, $password) {
        $servername = 
            "localhost:3306";
        $dbname = "mydb";
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            echo "Connection failed: " . $conn->connect_error;
            return NULL;
        } else {
            return new DatabaseConnection($conn);
        }
    }

    private $connection;

    private function __construct($connection) {
        $this->connection = $connection;
    }

    public function __destruct() {
        $this->connection->close();
    }

    public function query($sql) {
        return $this->connection->query($sql);
    }

    //ToDo: add prepared statement method

    public function insertID() {
        return $this->connection->insert_id;
    }
}


>
