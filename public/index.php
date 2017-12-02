<?php

require_once("..\\utils.php");
require_once("..\\html_integration.php");

$html = new HTMLDocument; $session = new Session;
$database = DatabaseConnection::makeConnection("serverAsAdmin","WhatIsThisGarbage987");
$root = Component::rootComponent($html, $session, $database);
$root->present();


?>
