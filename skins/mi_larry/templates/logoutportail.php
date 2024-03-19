<?php
\Program\Lib\Request\Session::destroy();
$portail = \Config\IHM::$PORTAIL_UTILISATEUR_URL . '#in/logout';
header('Location: ' . $portail);
die();
