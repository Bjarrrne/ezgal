<?php

/* Send ajax calls to DB */

$db = new PDO('sqlite:ezgalfiles.db');

$setting = $_GET['setting'];
$value = $_GET['value'];

$db->exec("REPLACE INTO settings ( setting_id, $setting ) VALUES ( 1, $value )");


?>