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
            $info = call_user_func($this->getInfo);
            call_user_func($this->presenter, $info, $this->components);
        } 
    }

    public static function fillerComponent() {
        $comp = new Component;
        $comp->enabled = false;
        return $comp;
    }
    public static function rootComponent($html, $session, $database) {
        $comp = new Component;

        $comp->getInfo = InfoGetter::emptyGetter();
        $comp->presenter = Presenter::presentAll();

        // ToDo
        // Add the pages with actual components
        $comp->components = [
            "loginPage" => self::loginComponent($html),
            "landingPage" => self::landingComponent($html),
            "adminPage" => self::fillerComponent(),
            "changePasswordPage" => self::fillerComponent()
        ];

        $enablePage = function ($name) use ($comp) {
            array_map(function ($innerComp) { $innerComp->enabled = false; }, $comp->components);
            $comp->components[$name]->enabled = true;
        };

        $eventHandlers = [
            "loginSubmit" => function () use ($database, $session, $enablePage) {
                // ToDo
                // set session var for password expiration

                $credentialsAreValid = false;
                $isAdmin = false;
                $username = "";
                $passwordExpire = false;

                $maybeUsername = Post::getVar("username");
                $maybePassword = Post::getVar("pass");
                $maybeUsername->map(function ($username) use (&$credentialsAreValid, $maybePassword, $database) {
                    $maybePassword->map(function ($password) use ($username, &$credentialsAreValid, $database) {
                        $sql = "select * from users where username='" . $username . "'";
                        $result = $database->query($sql);
                        if ($result && $result->num_rows > 0) {
                            $fetched = $result->fetch_assoc();
                            $passhash = $fetched["passhash"];
                            if (password_verify($password, $passhash)) {
                                $credentialsAreValid = true;
                                if ($fetched["usertype"] == "admin") {
                                    $isAdmin = true;
                                }
                            }
                            $result->close();
                        }
                        return null;
                    });
                    return null;
                });

                if ($credentialsAreValid) {
                    $session->setVar("isLoggedOn", true);
                    $session->setVar("username", $username);
                    if ($isAdmin) {
                        $session->setVar("isAdmin", true);
                    }
                    if ($passwordExpire) {
                        $session->setVar("username", true);
                    }
                    call_user_func($enablePage, "landingPage");
                } else {
                    call_user_func($enablePage,"loginPage");
                }
            },
            "logoutSubmit" => function () use ($session) {
                $session->resetAll();
            },
            "goToChangePassword" => function () use ($session, $enablePage) {
                call_user_func($enablePage,"changePasswordPage");
            },
            "goToAdmin" => function () use ($session, $enablePage) {
                $maybeAdmin = $session->getVar("isAdmin");

                $maybeAdmin->map(function ($isAdmin) use ($enablePage) {
                    call_user_func($enablePage,"adminPage");                    
                });
            },
            "changePassword" => function () use ($session, $enablePage) {
                // ToDo
                // check if previous password was correct and concordant
                // update if so
                call_user_func($enablePage,"landingPage");
            },
            "updateAdminOptions" => function () use ($session, $enablePage) {
                // ToDo
                // Check if admin
                // update ini accordingly
                call_user_func($enablePage,"landingPage");
            },
            "goToLanding" => function () use ($session, $enablePage) {
                call_user_func($enablePage,"landingPage");
            }
        ];

        $maybeRootEvent = Post::getVar("rootEvent");
        $maybeRootEvent = $maybeRootEvent->map(function ($rootEvent) use ($eventHandlers) {
            return $eventHandlers[$rootEvent];
        });
        $componentEnabler = $maybeRootEvent->ifOrElse($eventHandlers["goToLanding"]);
        call_user_func($componentEnabler);

        //these have higher precedence, and relies on some of above, so it goes last
        $maybeLoggedIn = $session->getVar("isLoggedOn");
        if ($maybeLoggedIn->isNothing()) {
            call_user_func($enablePage, "loginPage");
        }

        $maybeExpired = $session->getVar("passwordExpired");
        $maybeExpired->map(function ($passwordExpired) use ($comp) {
            call_user_func($enablePage, "changePasswordPage");
        });


        return $comp;
    }
    public static function loginComponent($html) {
        $comp = new Component;

        $comp->getInfo = InfoGetter::emptyGetter();
        $comp->presenter = Presenter::htmlTitledPresenter($html, Presenter::loginFormPresenter(), "Login");
        $comp->components = [];

        return $comp;
    }
    public static function landingComponent($html) {
        $comp = new Component;

        $comp->getInfo = InfoGetter::emptyGetter();
        $comp->presenter = Presenter::htmlTitledPresenter($html, Presenter::landingFormPresenter(), "Landing");
        $comp->components = [];

        return $comp;
    }
}


class Presenter {
    public static function presentAll() {
        return function($info, $components) {
            array_map(function ($comp) { $comp->present(); }, $components);
        };
    }
    public static function htmlPresenter($html, $getTags) {
        return function ($info, $components) use ($html, $getTags) {
            $html->addBodyHTML(call_user_func($getTags, $info, $components));      
        };
    }
    public static function htmlTitledPresenter($html, $getTags, $title) {
        return function ($info, $components) use ($html, $getTags, $title) {
            $html->setTitle($title);
            $html->addBodyHTML(call_user_func($getTags, $info, $components));
        };
    }
    public static function loginFormPresenter() {
        return function($info, $components) {
            return '<form action="index.php" method="post">
            User Name: <input type="text" name="username"><br>
            Password: <input type="password" name="pass"><br>
            <button type="submit" name="rootEvent" value="loginSubmit">Submit</button>
            </form>';
        };
    }
    public static function landingFormPresenter() {
        // ToDo
        // Disabled the admin options when user type isn't an admin
        return function($info, $components) {
            return '<form action="index.php" method="post">
              <button type="submit" name="rootEvent" value="goToChangePassword">Change your password</option>
              <button type="submit" name="rootEvent" value="goToAdmin">Admin Options</option>
              <button type="submit" name="rootEvent" value="logoutSubmit">Logout</option>
            </form>';
        };
    }
}


class InfoGetter {
    public static function emptyGetter() { 
        return function() {return null;}; 
    }
}


?>
