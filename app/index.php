<?php
// More time for execution because resizing the images can take some time
set_time_limit(1000);
require_once('config.php');
require_once('functions.php');
require_once('processor.php');

?>

<!DOCTYPE html>
<html>
<head>
<meta content="width=device-width, initial-scale=1, user-scalable=no" name="viewport">
<link rel="stylesheet" href="css/normalize.css">
<link rel="stylesheet" href="css/styles.css">
<link rel="stylesheet" href="css/fa-all.css">
<script src="scripts/scripts.js"></script>
<script src="scripts/lazyload.min.js"></script>
</head>
<body>

<div id="footer-container">
<div id="footer-navbar">
<i class="fas fa-cog" onclick="overlay('openoverlaysettings')"></i>
<i class="fas fa-info-circle" onclick="overlay('openoverlaygeneralinfo')"></i>
<i class="fas fa-filter" onclick="overlay('openoverlayfilters')"></i>
<i class="fas fa-angle-double-up" onclick="window.scrollTo({top: 0, left: 0, behavior: 'smooth'})"></i>
</div>
</div>

<?php

// Here comes the Frontend!
// Preparing frontend query with filtering methods
if (isset($_GET['filterby'])) { $filterby = $_GET['filterby']; }
if (isset($_GET['filtervalue'])) { $filtervalue = $_GET['filtervalue']; }
if (isset($_GET['filterby']) && isset($_GET['filtervalue']) && $_GET['filterby'] == 'date_to_sort') {
	$frontendquery = "SELECT * FROM files WHERE strftime('%Y-%m', datetime(date_to_sort, 'unixepoch')) = strftime('%Y-%m', datetime($filtervalue, 'unixepoch')) ORDER BY date_to_sort DESC";
} else if (isset($_GET['filterby']) && isset($_GET['filtervalue'])) {
	$frontendquery = "SELECT * FROM files WHERE $filterby = '$filtervalue' ORDER BY date_to_sort DESC";
} else {
	// Show all
	$frontendquery = "SELECT * FROM files ORDER BY date_to_sort DESC";
}

echo "<section id=\"gallery\">";

// Setting up some variables and counters to control foreach loop
$filedateold = "0";
$groupcounter = 1;
$groupcounterstartgroup = 1;
$mediacounter = 1;

foreach($db->query($frontendquery) as $filedata) {
	$relativepath = $filedata['relativepath'];
	$filedatecurrentunix = $filedata['date_to_sort'];
	$filedatecurrent = date("F Y", $filedata['date_to_sort']);
	
	// Here we create a new SQL query to get the next and previous mediapath, to give those to the javascript function
	// to preload them if the user clicks/swipes through the media in the overlay. There might be a better way
	// but I haven't thought of one yet. This will be problematic with multiple entries with the same date_to_sort value...
	// Oh yes and this doesn't really work when the view is filtered, because it preloads the wrong images...
	// Gotta think of something better...
	$prevmediapath = "";
	$nextmediaquery = "SELECT * FROM files WHERE date_to_sort < $filedatecurrentunix ORDER BY date_to_sort DESC LIMIT 1";
	foreach($db->query($nextmediaquery) as $nextmediaarray) {
		if ($nextmediaarray['mimetype'] == 'gif') {
			$nextmediapath = $nextmediaarray['relativepath'];
		} elseif ($nextmediaarray['filetype'] == 'image') {
			$nextmediapath = 'intermediates/'.$nextmediaarray['dirname'].'/'.$nextmediaarray['basename'].'.jpg';
		} else {
			$nextmediapath = $nextmediaarray['relativepath'];
		}
	}
	$prevmediaquery = "SELECT * FROM files WHERE date_to_sort > $filedatecurrentunix ORDER BY date_to_sort ASC LIMIT 1";
	foreach($db->query($prevmediaquery) as $prevmediaarray) { 
		if ($prevmediaarray['mimetype'] == 'gif') {
			$prevmediapath = $prevmediaarray['relativepath'];
		} elseif ($prevmediaarray['filetype'] == 'image') {
			$prevmediapath = 'intermediates/'.$prevmediaarray['dirname'].'/'.$prevmediaarray['basename'].'.jpg';
		} else {
			$prevmediapath = $prevmediaarray['relativepath'];
		}
	}
	
	// Monthly dividers
	if ($filedatecurrent != $filedateold) {
		echo "</div></section><div class=\"monthsdivider\">" . $filedatecurrent . "</div><section id=\"gallery\"><div class=\"gallerygroup3\">";
		$groupcounterstartgroup = 0;
		$filedateold = $filedatecurrent;
	}
	
	// Groupcounter to group 3 files to a div that can be styled - Starting div
	if ($groupcounterstartgroup == 1) { echo "<div class=\"gallerygroup3\">"; $groupcounterstartgroup = 0; }
	
	// Echoing Files
	echo "<div class=\"mediawrapper\">";
	// Videos
	if ($filedata['filetype'] == 'video') {
		echo "<img id=\"mediaid".$mediacounter."\" class=\"lazy\" data-src=\"videothumbs/".$filedata['dirname']."/".$filedata['basename'].".png\"  onclick=\"overlay('openoverlay', '".$mediacounter."', 'video', '".$relativepath."', '".$filedata['basename']."')\"><i class=\"playbtn fa fa-play\" onclick=\"overlay('openoverlay', '".$mediacounter."', 'video', '".$relativepath."', '".$filedata['basename']."')\"></i>";
	// Gifs
	} elseif ($filedata['mimetype'] == 'gif') {
		// Gifs are extra here because thumbs are created but no intermediates are used
		echo "<img id=\"mediaid".$mediacounter."\" class=\"lazy\" data-src=\"thumbs/".$filedata['dirname']."/".$filedata['basename'].".gif\" onclick=\"overlay('openoverlay', '".$mediacounter."', 'gif', '".$relativepath."', '".$filedata['basename']."')\">";
	// Images (standard & raw)
	} elseif ($filedata['filetype'] == 'image') {
		echo "<img id=\"mediaid".$mediacounter."\" class=\"lazy\" src=\"lowquals/".$filedata['dirname']."/".$filedata['basename'].".jpg\" data-src=\"thumbs/".$filedata['dirname']."/".$filedata['basename'].".jpg\" onclick=\"overlay(
			'openoverlay',
			'".$mediacounter."',
			'image',
			'".$filedata['relativepath']."',
			'".$filedata['basename']."',
			'".$filedata['date_to_sort']."',
			'".$filedata['mimetype']."',
			'".$filedata['file_size']."',
			'".$filedata['width']."',
			'".$filedata['height']."',
			'".$filedata['exif_exists']."',
			'".$filedata['exif_make']."',
			'".$filedata['exif_model']."',
			'".$filedata['exif_fnumber']."',
			'".$filedata['exif_focallength']."',
			'".$filedata['exif_exposuretime']."',
			'".$filedata['exif_iso']."',
			'".$filedata['exif_latituderef']."',
			'".$filedata['exif_latitude_deg']."',
			'".$filedata['exif_latitude_min']."',
			'".$filedata['exif_latitude_sec']."',
			'".$filedata['exif_latitude_dd']."',
			'".$filedata['exif_longituderef']."',
			'".$filedata['exif_longitude_deg']."',
			'".$filedata['exif_longitude_min']."',
			'".$filedata['exif_longitude_sec']."',
			'".$filedata['exif_longitude_dd']."',
			'".$nextmediapath."',
			'".$prevmediapath."',
			)\">";
	}
	echo "</div>";
	
	// Groupcounter to group $setting_grouplength files to a div that can be styled - Ending div
	if ($groupcounter % $setting_grouplength == 0) { echo "</div>"; $groupcounterstartgroup = 1; }
	
	$groupcounter++;
	$mediacounter++;
	
// End Frontend Loop
}

echo "</section>";

?>

<div id="overlay_type_filters" class="overlay-general" style="height: 0%;">
	<div id="overlay_button_container_filters" class="overlay-buttons" style="height: 0%;">
		<a href="javascript:void(0)" class="overlaybtn" onclick="overlay('closeoverlayfilters')"><i class="fas fa-times"></i></a>
	</div>
	<div class="overlay-content" id="overlay-filters-content">
		<h1>Filters</h1>
		<a href="?"><button class="gallery-filter">Show all</button></a>
		<table class="overlay-table">
		<tr><td>Type</td>
		<td>
		<a href="?filterby=filetype&filtervalue=image"><button class="gallery-filter">Image</button></a>
		<a href="?filterby=filetype&filtervalue=video"><button class="gallery-filter">Video</button></a>
		</td></tr>
		<tr><td>Month:</td>
		<td>
			<form>
				<select onChange="location.href=this.options[this.selectedIndex].value">
					<?php /* Query for Month */
					$filterdateold = "0";
					$filterdatequery = "SELECT DISTINCT date_to_sort FROM files ORDER BY date_to_sort DESC";
					foreach($db->query($filterdatequery) as $filterbydate) {
						$filterdatecurrent = date("F Y", $filterbydate['date_to_sort']);
							if ($filterdatecurrent != $filterdateold) {
								echo "<option value=\"?filterby=date_to_sort&filtervalue=" . $filterbydate['date_to_sort'] . "\">" . date("Y F", $filterbydate['date_to_sort']) . "";
							}
						$filterdateold = $filterdatecurrent;
					} ?>
				</select>
			</form>
		</td></tr>
		<tr><td>Camera:</td>
		<td>
			<?php	/* Query for Camera Model */
				$filtermodelquery = "SELECT DISTINCT exif_model FROM files WHERE exif_model IS NOT NULL ORDER BY exif_model";
				foreach($db->query($filtermodelquery) as $filterbymodel) {
					 echo "<a href=\"?filterby=exif_model&filtervalue=" . $filterbymodel['exif_model'] . "\"><button class=\"gallery-filter\">" . $filterbymodel['exif_model'] . "</button></a>";
				} ?>
		</td></tr>
		</table>
	</div>
</div>



<div id="overlay_type_image" class="overlay">
	<div class="overlay-buttons">
		<a href="javascript:void(0)" class="overlaybtn" onclick="prevmedia()"><i class="fas fa-angle-left"></i></a>
		<a id="overlaydownloadbutton-image" class="overlaybtn"><i class="fas fa-save"></i></a>
		<a href="javascript:void(0)" class="overlaybtn" onclick="overlay('openoverlayinfo','image')"><i class="fas fa-info-circle"></i></i></a>
		<a href="javascript:void(0)" class="overlaybtn" onclick="overlay('closeoverlay')"><i class="fas fa-times"></i></a>
		<a href="javascript:void(0)" class="overlaybtn" onclick="nextmedia()"><i class="fas fa-angle-right"></i></a>
	</div>
	<div class="overlay-content">
		<img id="overlay_mediapath_image" src="">
	</div>
</div>

<div id="overlay_type_video" class="overlay">
	<div class="overlay-buttons">
		<a href="javascript:void(0)" class="overlaybtn" onclick="prevmedia()"><i class="fas fa-angle-left"></i></a>
		<a id="overlaydownloadbutton-video" class="overlaybtn"><i class="fas fa-save"></i></a>
		<a href="javascript:void(0)" class="overlaybtn" onclick="overlay('openoverlayinfo','video')"><i class="fas fa-info-circle"></i></i></a>
		<a href="javascript:void(0)" class="overlaybtn" onclick="overlay('closeoverlay')"><i class="fas fa-times"></i></a>
		<a href="javascript:void(0)" class="overlaybtn" onclick="nextmedia()"><i class="fas fa-angle-right"></i></a>
	</div>
	<div class="overlay-content">
		<video autoplay controls id="overlay_videoplayer"><source id="overlay_mediapath_video" src=""></video>
	</div>
</div>



<div id="overlay_info_type_image" class="overlay-general" style="height: 0%;">
	<div id="overlay_button_container_image" class="overlay-buttons" style="height: 0%;">
		<a href="javascript:void(0)" class="overlaybtn" onclick="overlay('closeoverlayinfo')"><i class="fas fa-times"></i></a>
	</div>
	<div class="overlay-content">
		<h1>Details</h1>
		<table class="overlay-table">
		<tr><td><i class="far fa-folder"></i></td><td><span id="overlay_info_directory"></span></td></tr>
		<tr><td><i class="far fa-calendar-alt"></i></td><td><span id="overlay_info_date"></span></td></tr>
		<tr><td><i class="far fa-image"></i></td><td><span id="overlay_info_fileinfo"></span></td></tr>
		<tr><td><i class="fas fa-camera"></i></td><td></i><b><span id="overlay_info_imageinfo"></span></b><br /><span id="overlay_info_imageinfo_details"></span></td></tr>
		</table>
		<?php if ($gapikey != "") { ?><div id="gmap" class="map"></div><?php } ?>
	</div>
</div>

<div id="overlay_info_type_video" class="overlay-general" style="height: 0%;">
	<div id="overlay_button_container_video" class="overlay-buttons" style="height: 0%;">
		<a href="javascript:void(0)" class="overlaybtn" onclick="overlay('closeoverlayinfo')"><i class="fas fa-times"></i></a>
	</div>
	<div class="overlay-content">
		<h1>Details</h1>
		No metadata for videos yet. Sorry.
	</div>
</div>


<div id="overlay_general_info" class="overlay-general" style="height: 0%;">
	<div id="overlay_button_container_generalinfo" class="overlay-buttons" style="height: 0%;">
		<a href="javascript:void(0)" class="overlaybtn" onclick="overlay('closeoverlaygeneralinfo')"><i class="fas fa-times"></i></a>
	</div>
	<div class="overlay-content">
		<h1>Details</h1>
		<table class="overlay-table">
		<tr><td>Number of media</td><td><?php $generalinfocountentries = $db->query("SELECT count(1) FROM files")->fetch(); echo $generalinfocountentries[0]; ?></td></tr>
		<tr><td>Size of media</td><td><?php $generalinfosize = $db->query("SELECT SUM(file_size) FROM files")->fetch(); echo formatBytes($generalinfosize[0]); ?></td></tr>
		<tr><td>ezgal Version</td><td>0.3</td></tr>
		</table>
	</div>
</div>


<div id="overlay_type_settings" class="overlay-general" style="height: 0%;">
	<div id="overlay_button_container_settings" class="overlay-buttons" style="height: 0%;">
		<a href="javascript:void(0)" class="overlaybtn" onclick="overlay('closeoverlaysettings')"><i class="fas fa-times"></i></a>
	</div>
	<div class="overlay-content">
		<h1>Details</h1>
		<table class="overlay-table">
		<tr><td>Max no. of images in a row</td><td>
			<button class="gallery-filter" onclick="submitsetting('setting_grouplength','6')">6 lol</button>
			<button class="gallery-filter" onclick="submitsetting('setting_grouplength','3')">3 (standard)</button>
			<button class="gallery-filter" onclick="submitsetting('setting_grouplength','4')">4?</button>
			</td></tr>
		</table>
		<button class="gallery-filter" onclick="window.location.reload(true)">Reload</button>
	</div>
</div>

<script src="scripts/post-scripts.js">></script>
<?php if ($gapikey != "") { ?>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $gapikey; ?>&callback=initMap"></script>
<?php } ?>
</body>
</html>