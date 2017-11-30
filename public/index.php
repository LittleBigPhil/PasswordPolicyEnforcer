<?php

require_once("..\\utils.php");
require_once("..\\html_integration.php");

$html = new HTMLDocument;

$html->setTitle("title");
$html->addBodyHTML("<p>dkljfdl</p>");

$maybe = new Just(3);
$maybe->map(function($x) use($html) {
    $html->addBodyHTML("<p>The number is: " . strval($x) . "</p>");
});


$parsed = parse_ini_file("..\\policy_config.ini");
var_dump($parsed);
?>
