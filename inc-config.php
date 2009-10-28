<?php
session_start();

require('/var/sql-connect/hilma.php');
require('inc-functions.php');
require('inc-cache.php');


define('GMAPS_APIKEY','');

header('Content-type: text/html; charset=UTF-8');

?>