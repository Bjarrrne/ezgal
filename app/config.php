<?php

/*

This is the config file for ezgal which sets up all necessary stuff

*/

// API Key for Google Maps usage. Howto: https://developers.google.com/maps/documentation/javascript/get-api-key
$gapikey = "";

// Debugging
// Show Errors
error_reporting(E_ALL);
ini_set('display_errors', true);
// Check if sqlite3 is activated
if (!extension_loaded('sqlite3')) { exit("ERROR! sqlite3 extension not loaded"); }
// Check if imagick is installed
if (!extension_loaded('imagick')) { exit("ERROR! imagick not loaded"); }


// Preparing database, connecting with PDO
$db = new PDO('sqlite:ezgalfiles.db');
$db->exec("CREATE TABLE IF NOT EXISTS files(
   id INTEGER PRIMARY KEY, 
   relativepath TEXT NOT NULL UNIQUE,
   absolutepath TEXT,
   processed INTEGER DEFAULT '0',
   mimetype TEXT,
   filetype TEXT,
   exif_exists INTEGER DEFAULT NULL,
   dirname TEXT,
   basename TEXT,
   filename TEXT,
   extension TEXT,
   file_size INTEGER,
   mod_date INTEGER,
   width INTEGER,
   height INTEGER,
   channel_red_mean REAL,
   channel_green_mean REAL,
   channel_blue_mean REAL,
   exif_datetime TEXT,
   exif_orientation INTEGER,
   exif_make TEXT,
   exif_model TEXT,
   exif_fnumber TEXT,
   exif_focallength TEXT,
   exif_exposuretime TEXT,
   exif_iso INTEGER,
   exif_latituderef TEXT,
   exif_latitude_deg TEXT,
   exif_latitude_min TEXT,
   exif_latitude_sec TEXT,
   exif_latitude_dd TEXT,
   exif_longituderef TEXT,
   exif_longitude_deg TEXT,
   exif_longitude_min TEXT,
   exif_longitude_sec TEXT,
   exif_longitude_dd TEXT,
   date_to_sort TEXT
   )");
   
// Creating table for settings
$db->exec("CREATE TABLE IF NOT EXISTS settings(
   setting_id INTEGER PRIMARY KEY, 
   setting_grouplength INTEGER DEFAULT 3,
   setting_sharpening INTEGER DEFAULT 1,
   setting_thumbsize INTEGER DEFAULT 500
   )");
// Filling with standard settings
$db->exec("INSERT OR IGNORE INTO settings ( setting_id ) VALUES ( 1 )");
// Fetch settings
$settingsarray = $db->query("SELECT * FROM settings LIMIT 1")->fetch();
$setting_grouplength = $settingsarray['setting_grouplength'];
$setting_sharpening = $settingsarray['setting_sharpening'];
$setting_thumbsize = $settingsarray['setting_thumbsize'];

   
if (!is_writable('ezgalfiles.db')) {
 chmod('ezgalfiles.db', 0775);
}

?>