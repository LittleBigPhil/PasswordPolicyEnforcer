<?php



//Allows stateful editing of the HTML
//Automatically echos on destruction
//Example use :
//
//$html = new HTMLDocument;
//$html->setTitle("title example");
//$html->addBodyHTML("<p>paragraph example</p><br />");
//unset($html);
//
//the unset shouldn't be necessary
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


class Component {
    public $getInfo = function(){return Null;};
    public $presenter = function($info, $components){};
    public $components = [];

    public function present() {
        if ($this->enabled) {
            $info = $this->getInfo();
            $this->presenter($info, $components);
        } 
    }

    public $enabled = true;


    public static function rootComponent($html, $session, $database) {
        $comp = new Component;

        $comp->getInfo = InfoGetters::emptyGetter;
        $comp->presenter = Presenters::presentAll;
        // ToDo
        // Add the pages
        $comp->components = [
            "loginPage" => null
            "landingPage" => null
            "adminPage" => null
            "changePasswordPage" => null
        ];

        $enablePage function ($name) use ($comp->components) {
            array_map($comp->components, function ($comp) { $comp->enable = false; });
            $comp->components[$name]->enabled = true;
        }

        // ToDo
        // Handle the events;
        $eventHandlers = [
            "loginSubmit" => function () use ($database, $session, $enablePage) {
                // check database on credentials
                // update session variables for logged in
                // update session variable for user type
                // update session variables for expired password


                $credentialsAreValid = false;
                if ($credentialsAreValid) {

                    // set session vars to logged in
                    $enablePage("landingPage");
                } else {
                    $enablePage("loginPage");
                }
            },
            "logoutSubmit" => function () use ($session) {
                $session->resetAll();
            },
            "goToChangePassword" => function () use ($session, $enablePage) {

            },
            "goToAdmin" => function () use ($session, $enablePage) {
                
            },
            "changePassword" => function () use ($session, $enablePage) {
                
            },
            "updateAdminOptions" => function () use ($session, $enablePage) {

            },
            "goToLanding" => function () use ($session, $enablePage) {
                
            }
        ];
        $maybeRootEvent = $session->getVar("rootEvent");
        $maybeRootEvent = $maybeRootEvent->map(function ($rootEvent) use ($eventHandlers) {
            return $eventHandlers[$rootEvent];
        });
        $componentEnabler = $maybeRootEvent->ifOrElse(function () use ($eventHandlers) {
            eventHandlers["goToLanding"];
        });
        $componentEnabler($comp->components);

        //these have higher precedence, and relies on some of above, so it goes last
        $maybeLoggedIn = $session->getVar("isLoggedIn");
        if ($maybeLoggedIn.isNothing()) {
            array_map($comp->components, function ($comp) { $comp->enable = false; });
            $comp->components["loginPage"]->enabled = true;
        }

        $maybeExpired = $session->getVar("passwordExpired")
        $maybeExpired->map(function ($passwordExpired) use ($comp) {
            array_map($comp->components, function ($comp) { $comp->enable = false; });
            $comp->components["changePasswordPage"]->enabled = true;
        });


        return $comp;
    }
    public static function loginComponent($html) {
        $comp = new Component;

        $comp->getInfo = InfoGetters::emptyGetter;
        $comp->presenter = Presenters::htmlPresenter($html, Presenters::loginFormPresenter);
        $comp->components = [];
    }
}


class Presenters {
    public static function presentAll($info, $components) {
        array_map($components, function ($comp) { $comp->present(); });
    }
    public static function htmlPresenter($html, $getTags) {
        return function ($info, $components) {
            $html->addBodyHTML($getTags($info, $components));      
        }
    }
    public static function loginFormPresenter($info, $components) {
        return "<form action="index.php" method="post">
        User Name: <input type="text" name="username"><br>
        Password: <input type="password" name="pass"><br>
        <input type="submit" name="rootEvent" value="loginSubmit">
        </form>"
    }
}


class InfoGetters {
    public static function emptyGetter() { return null; }
}


?>
