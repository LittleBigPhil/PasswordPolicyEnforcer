<?php

//Wrapper around the default php session interface
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

abstract class Maybe {
    public static function build($value) {
        if ($value === NULL) {
            return new Nothing;
        } else {
            return new Just($value);
        }
    }

    abstract public function ifOrElse($else_val);
    abstract public function map($func);
    abstract public function flatten();

    public function flatMap($func) {
        return map($func)->flatten();
    }
}

class Just extends Maybe {
    public $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public function ifOrElse($else_val) {
        return $this->value;
    }

    public function map($func) {
        return new Just($func($this->value));
    }

    public function flatten() {
        return $this->value;
    }
}

class Nothing {
    public function ifOrElse($else_val) {
        return $else_value;
    }

    public function map($func) {
        return $this;
    }

    public function flatten() {
        return $this;
    }
}

class Lazy {
    private $value;
    private $generator;
    
    public function __construct($func) {
        $this->generator = $func;
        $this->value = new Nothing;
    }
    
    public function get() {
        $generated = $this->value->ifOrElse($this->generator());
        $this->value = new Just($generated);
        return $generated;
    }
}

abstract class Either {
    public abstract function isLeft();
    public abstract function isRight();
    public abstract function leftMap($func);
    public abstract function rightMap($func);
    public abstract function getLeft();
    public abstract function getRight();
}

class Left extends Either{
    private $value;
    public function __construct($value) {
        $this->value = $value;
    }

    public function isLeft() {
        return true;
    }
    public function isRight() {
        return false;
    }
    public function leftMap($func) {
        return new Left($func($this->value));
    }
    public function rightMap($func) {
        return $this;
    }
    public function getLeft() {
        return new Just($this->value);
    }
    public function getRight() { 
        return new Nothing;
    }
}

class Right extends Either{
    private $value;
    public function __construct($value) {
        $this->value = $value;
    }

    public function isLeft() {
        return false;
    }
    public function isRight() {
        return true;
    }
    public function leftMap($func) {
        return $this;
    }
    public function rightMap($func) {
        return new Right($func($this->value));
    }
    public function getLeft() {
        return new Nothing;
    }
    public function getRight() {
        return new Just($this->value);
    }
}

class Trampoline {
    // This allows tail recursion to be optimized not to fill the stack
    // A trampolinable function is one that returns an either
    //    if the return value is left, computation should continue
    //    if the return value is right, computation is finished
    public static function execute($tFunc) {
        $thunk = new Left($tFunc());
        while ($thunk->isLeft()) {
            $nextFunc = $thunk->getLeft()->ifOrElse(function(){ throw new Exception; } );
            $thunk = $nextFunc();
        }
        $resolved = $thunk->getRight()->ifOrElse(Null);
        assert($resolved !== Null);
        return $resolved;
    }
}



class StringUtils {
    // ToDo
    public static function distanceBetween(string $str1, string $str2) {
        throw new \Exception("Not Implemented");
    }
}


class PolicyOptions {
    // ToDO
    public static function loadFromFile($filePath) {
        throw new \Exception("Not Implemented");
    }
    // ToDo
    public function saveToFile($filePath) {
        throw new \Exception("Not Implemented");
    }
    // ToDo
    public static function loadFromPost($session) {
        throw new \Exception("Not Implemented");
    }

    private $mapping;
    private __construct($mapping) {
        $this->mapping = $mapping;
    }
}



?>
