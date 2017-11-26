<?php

require_once("..\\utils.php");

$html = new HTMLDocument;

$html->setTitle("title");
$html->addBodyHTML("<p>dkljfdl</p>");

$maybe = new Just(3);
$maybe->map(function($x) use($html) {
    $html->addBodyHTML("<p>The number is: " . strval($x) . "</p>");
});

?>
