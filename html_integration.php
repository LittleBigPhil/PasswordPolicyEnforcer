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


    public static function rootComponent($root_event) {
        $comp = new Component;

        $comp->getInfo = InfoGetters::emptyGetter;
        $comp->presenter = Presenters::presentAll;
        $comp->components = [
            
        ];
        array_map($comp->components, function ($comp) { $comp->enable = false; });

        // ToDo
        // Handle the event;

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
}


class InfoGetters {
    public static function emptyGetter() { return null; }
}


?>
