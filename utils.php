<?php

// Wrapper around the default php session interface
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

    public function getVar($name) {
        if (isset($_SESSION[$name])) {
            return Maybe::build($_SESSION[$name]);
        } else {
            return new Nothing;
        }
    }
    public function unsafeGetVar($name) {
        return $_SESSION[$name];
    }
    public function setVar($name, $value) {
        $_SESSION[$name] = $value;
    }
    public function unsetVar($name) {
        unset($_SESSION[$name]);
    }

    public function resetAll() {
        session_unset();
    }
}

// Namespace for maybe based post values
class Post {
    public static function getVar($name) {
        if (isset($_POST[$name])) {
            return Maybe::build($_POST[$name]);
        } else {
            return new Nothing;
        }
    }
}


// Turns out the PDO class that was added as part of the standard does what I was using this class for
// So I just turned this into a namespace
class DatabaseConnection {
    
    public static function makeConnection($username, $password) {
        $servername = "localhost";
        $port = "3306";
        $dbname = "mydb";
        $dsn = "mysql:host=$servername;dbname=$dbname;port=$port";

        $eConn = Either::tryFunc( function () use ($dsn, $username, $password) {
            $conn = new PDO($dsn, $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        });
        return $eConn;
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
    abstract public function isNothing();
    abstract public function isJust();

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
    public function isNothing() {return false;}
    public function isJust() {return true;}
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
    public function isNothing() {return true;}
    public function isJust() {return false;}
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

    public function leftFlatMap($func) {
        return leftMap($func)->leftFlatten();
    }
    public function rightFlatMap($func) {
        return rightMap($func)->rightFlatten();
    }

    public static function tryFunc($func) {
        try {
            return new Right($func());
        } catch (Exception $e) {
            return new Left($e);
        }
    }

    public abstract function isLeft();
    public abstract function isRight();
    public abstract function leftMap($func);
    public abstract function leftFlatten();
    public abstract function rightMap($func);
    public abstract function rightFlatten();
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
    public function leftFlatten() {
        return $value;
    }
    public function rightMap($func) {
        return $this;
    }
    public function rightFlatten() {
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
    public function leftFlatten() {
        return $this;
    }
    public function rightMap($func) {
        return new Right($func($this->value));
    }
    public function rightFlatten() {
        return $value;
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
    public static function loadFromFile($filePath) {
        $parsed = parse_ini_file("..\\policy_config.ini");
        return new PolicyOptions($parsed);
    }
    public function saveToFile($filePath) {
        $handle = fopen($filePath, 'w') or die('Cannot open config file ' . $filepath . ' for output.');
        $config_text = "";
        foreach($mapping as $key => $value) {
            $config_text .= ("" . $key . "=" . $value);
        }
        fwrite($handle, $config_text);
    }
    // ToDo
    public static function loadFromPost($session) {
        throw new \Exception("Not Implemented");
    }

    private $mapping;
    private function __construct($mapping) {
        $this->mapping = $mapping;
    }
}



?>
