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
    public $getInfo;
    public $presenter;
    public $components;
    public $enabled;

    public function __construct() {
        $this->getInfo = function(){ return Null; };
        $this->presenter = function($info, $components){};
        $this->components = [];
        $this->enabled = true;
    }

    public function present() {
        if ($this->enabled) {
            $info = $this->getInfo();
            $this->presenter($info, $components);
        } 
    }

    public static function fillerComponent() {
        $comp = new Component;
        $comp->enabled = false;
        return $comp;
    }
    public static function rootComponent($html, $session, $database) {
        $comp = new Component;

        $comp->getInfo = InfoGetters::emptyGetter;
        $comp->presenter = Presenters::presentAll;

        // ToDo
        // Add the pages with actual components
        $comp->components = [
            "loginPage" => Presenter::htmlTitledPresenter($html, Presenter::loginFormPresenter, "Login"),
            "landingPage" => fillerComponent(),
            "adminPage" => fillerComponent(),
            "changePasswordPage" => fillerComponent()
        ];

        $enablePage = function ($name) use ($comp) {
            array_map($comp->components, function ($innerComp) { $innerComp->enabled = false; });
            $comp->components[$name]->enabled = true;
        };

        $eventHandlers = [
            "loginSubmit" => function () use ($database, $session, $enablePage) {
                // ToDo
                // check database for credentials
                // set session var for password expiration


                $credentialsAreValid = false;
                if ($credentialsAreValid) {
                    $session->setVar("isLoggedOn", true);
                    $session->setVar("userName", $username);
                    if ($isAdmin) {
                        $session->setVar("isAdmin", true);
                    }
                    if ($passwordExpire) {
                        $session->setVar("userName", true);
                    }
                    $enablePage("landingPage");
                } else {
                    $enablePage("loginPage");
                }
            },
            "logoutSubmit" => function () use ($session) {
                $session->resetAll();
            },
            "goToChangePassword" => function () use ($session, $enablePage) {
                $enablePage("changePasswordPage");
            },
            "goToAdmin" => function () use ($session, $enablePage) {
                $maybeAdmin = $session->getVar("isAdmin");

                $maybeAdmin->map(function ($isAdmin) use ($enablePage) {
                    $enablePage("adminPage");                    
                });
            },
            "changePassword" => function () use ($session, $enablePage) {
                // ToDo
                // check if previous password was correct and concordant
                // update if so
                $enablePage("landingPage");
            },
            "updateAdminOptions" => function () use ($session, $enablePage) {
                // ToDo
                // Check if admin
                // update ini accordingly
                $enablePage("landingPage");
            },
            "goToLanding" => function () use ($session, $enablePage) {
                $enablePage("landingPage");
            }
        ];

        $maybeRootEvent = $session->getVar("rootEvent");
        $maybeRootEvent = $maybeRootEvent->map(function ($rootEvent) use ($eventHandlers) {
            return $eventHandlers[$rootEvent];
        });
        $componentEnabler = $maybeRootEvent->ifOrElse(eventHandlers["goToLanding"]);
        $componentEnabler();

        //these have higher precedence, and relies on some of above, so it goes last
        $maybeLoggedIn = $session->getVar("isLoggedOn");
        if ($maybeLoggedIn.isNothing()) {
            array_map($comp->components, function ($comp) { $comp->enable = false; });
            $comp->components["loginPage"]->enabled = true;
        }

        $maybeExpired = $session->getVar("passwordExpired");
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
        return function ($info, $components) use ($html, $getTags) {
            $html->addBodyHTML($getTags($info, $components));      
        };
    }
    public static function htmlTitledPresenter($html, $getTags, $title) {
        return function ($info, $components) use ($html, $getTags, $title) {
            $html->setTitle($title);
            $html->addBodyHTML($getTags($info, $components));
        };
    }
    public static function loginFormPresenter($info, $components) {
        return '<form action="index.php" method="post">
        User Name: <input type="text" name="username"><br>
        Password: <input type="password" name="pass"><br>
        <input type="submit" name="rootEvent" value="loginSubmit">
        </form>';
    }
}


class InfoGetters {
    public static function emptyGetter() { return null; }
}


?>
