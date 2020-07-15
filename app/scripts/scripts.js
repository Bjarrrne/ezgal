// Initializing GMaps
var map;
var marker;
function initMap() {
	var uluru = {lat: -25.344, lng: 131.036};
	map = new google.maps.Map(
		document.getElementById('gmap'), {zoom: 17, center: uluru});
	marker = new google.maps.Marker({position: uluru, map: map});
}



// Overlay Script

function overlay(querytype, media_id, filetype, mediapath, basename, date_to_sort, mimetype, file_size, width, height, exif_exists, exif_make, exif_model, exif_fnumber, exif_focallength, exif_exposuretime, exif_iso, exif_latituderef, exif_latitude_deg, exif_latitude_min, exif_latitude_sec, exif_latitude_dd, exif_longituderef, exif_longitude_deg, exif_longitude_min, exif_longitude_sec, exif_longitude_dd, nextmediapath, prevmediapath) {
	// Set overflow of body to hidden so scrolling gets deactivated behinde the overlay
	document.body.style.overflow = "hidden";

	// Until correct DB/Ajax implementation, all querydata will be stored via javascript localstorage, which is not very safe, but works.
	// Set localstorage for metadata only if the query comes from openoverlay, so the data does not get overwritten when openoverlayinfo.
	// This is a bit stupid and once I want to make the metadata accessible directly from the frontend future me will have to change that...
	localStorage.setItem("querytype", querytype);
	
	if (localStorage.getItem("querytype") == 'openoverlay') {
		localStorage.setItem("media_id", media_id);
		localStorage.setItem("filetype", filetype);
		localStorage.setItem("mediapath", mediapath);
		localStorage.setItem("basename", basename);
		localStorage.setItem("date_to_sort", date_to_sort);
		localStorage.setItem("mimetype", mimetype);
		localStorage.setItem("file_size", file_size);
		localStorage.setItem("width", width);
		localStorage.setItem("height", height);
		localStorage.setItem("exif_exists", exif_exists);
		localStorage.setItem("exif_make", exif_make);
		localStorage.setItem("exif_model", exif_model);
		localStorage.setItem("exif_fnumber", exif_fnumber);
		localStorage.setItem("exif_focallength", exif_focallength);
		localStorage.setItem("exif_exposuretime", exif_exposuretime);
		localStorage.setItem("exif_iso", exif_iso);
		localStorage.setItem("exif_latituderef", exif_latituderef);
		localStorage.setItem("exif_latitude_deg", exif_latitude_deg);
		localStorage.setItem("exif_latitude_min", exif_latitude_min);
		localStorage.setItem("exif_latitude_sec", exif_latitude_sec);
		localStorage.setItem("exif_latitude_dd", exif_latitude_dd);
		localStorage.setItem("exif_longituderef", exif_longituderef);
		localStorage.setItem("exif_longitude_deg", exif_longitude_deg);
		localStorage.setItem("exif_longitude_min", exif_longitude_min);
		localStorage.setItem("exif_longitude_sec", exif_longitude_sec);
		localStorage.setItem("exif_longitude_dd", exif_longitude_dd);
		localStorage.setItem("nextmediapath", nextmediapath);
		localStorage.setItem("prevmediapath", prevmediapath);
	}
	
	function bytestomb(bytes) {
		var marker = 1024; // Change to 1000 if required
		var decimal = 1; // Change as required
		var kiloBytes = marker; // One Kilobyte is 1024 bytes
		var megaBytes = marker * marker; // One MB is 1024 KB
		var gigaBytes = marker * marker * marker; // One GB is 1024 MB
		var teraBytes = marker * marker * marker * marker; // One TB is 1024 GB
		// return bytes if less than a KB
		if(bytes < kiloBytes) return bytes + " Bytes";
		// return KB if less than a MB
		else if(bytes < megaBytes) return(bytes / kiloBytes).toFixed(decimal) + " KB";
		// return MB if less than a GB
		else if(bytes < gigaBytes) return(bytes / megaBytes).toFixed(decimal) + " MB";
		// return GB if less than a TB
		else return(bytes / gigaBytes).toFixed(decimal) + " GB";
	}
	
	// Loading video player html
	var overlay_videoplayer = document.getElementById('overlay_videoplayer');
	
	// Opening the media overlay
	if (localStorage.getItem("querytype") == 'openoverlay') {	
		if (localStorage.getItem("filetype") == 'image') {
			document.getElementById("overlay_type_image").style.display = "block";
			document.getElementById("overlay_mediapath_image").src = "intermediates/" + localStorage.getItem("mediapath");
			// Setting up media download button
			document.getElementById("overlaydownloadbutton-image").href = localStorage.getItem("mediapath");
			document.getElementById("overlaydownloadbutton-image").download = localStorage.getItem("basename");
			// Preloading next and prev image
			preloadoverlayimages(localStorage.getItem("nextmediapath"), localStorage.getItem("prevmediapath"));

		} else if (localStorage.getItem("filetype") == 'gif') {
			document.getElementById("overlay_type_image").style.display = "block";
			document.getElementById("overlay_mediapath_image").src = localStorage.getItem("mediapath");
			// Setting up media download button
			document.getElementById("overlaydownloadbutton-image").href = localStorage.getItem("mediapath");
			document.getElementById("overlaydownloadbutton-image").download = localStorage.getItem("basename");
			
		} else if (localStorage.getItem("filetype") == 'video') {
			document.getElementById("overlay_type_video").style.display = "block";
			document.getElementById("overlay_mediapath_video").src = localStorage.getItem("mediapath");
			// Setting up media download button
			document.getElementById("overlaydownloadbutton-video").href = localStorage.getItem("mediapath");
			document.getElementById("overlaydownloadbutton-image").download = localStorage.getItem("basename");
			// Loading and playing video
			overlay_videoplayer.load();
			overlay_videoplayer.play();
		}
		
	// Opening the media info overlay
	} else if (localStorage.getItem("querytype") == 'openoverlayinfo') {
		if (localStorage.getItem("filetype") == 'image') {
			document.getElementById("overlay_info_type_image").style.height = "100%";
			document.getElementById("overlay_button_container_image").style.height = "51px";
			// Convert the unix timestamp to the desired format (see https://coderrocketfuel.com/article/convert-a-unix-timestamp-to-a-date-in-vanilla-javascript)
			var unixTimestamp = Number(localStorage.getItem("date_to_sort"));
			var unixmillisec = unixTimestamp * 1000;
			var unixdate = new Date(unixmillisec);
			var realdate = unixdate.toLocaleString();
			document.getElementById("overlay_info_date").innerHTML = realdate;
			document.getElementById("overlay_info_directory").innerHTML = localStorage.getItem("mediapath");
			var mpix = localStorage.getItem("width") * localStorage.getItem("height") / 1000000;
			document.getElementById("overlay_info_fileinfo").innerHTML = parseFloat(mpix).toFixed(1) + " MP&nbsp;&nbsp;&nbsp;" + localStorage.getItem("width") + " x " + localStorage.getItem("height") + "&nbsp;&nbsp;&nbsp;" + bytestomb(localStorage.getItem("file_size"));
			document.getElementById("overlay_info_imageinfo").innerHTML = localStorage.getItem("exif_make") + " " + localStorage.getItem("exif_model");
			var fnum_arr = localStorage.getItem("exif_fnumber").split('/');
			var focalnum_arr = localStorage.getItem("exif_focallength").split('/');
			document.getElementById("overlay_info_imageinfo_details").innerHTML = "f/" + fnum_arr[0] / fnum_arr[1] + "&nbsp;&nbsp;&nbsp;" + localStorage.getItem("exif_exposuretime") + " s&nbsp;&nbsp;&nbsp;" + focalnum_arr[0] / focalnum_arr[1] + " mm&nbsp;&nbsp;&nbsp;ISO " + localStorage.getItem("exif_iso");
			// Setting the GMap center & marker
			map.setCenter({
				lat : Number(localStorage.getItem("exif_latitude_dd")),
				lng : Number(localStorage.getItem("exif_longitude_dd"))
				});
			marker.setPosition({
				lat : Number(localStorage.getItem("exif_latitude_dd")),
				lng : Number(localStorage.getItem("exif_longitude_dd"))
				});
		} else if (localStorage.getItem("filetype") == 'gif') {
			document.getElementById("overlay_info_type_image").style.height = "100%";
			document.getElementById("overlay_button_container_image").style.height = "51px";
			document.getElementById("overlay_info_directory").innerHTML = localStorage.getItem("mediapath");
			document.getElementById("overlay_info_fileinfo").innerHTML = localStorage.getItem("width") + " x " + localStorage.getItem("height");	
		} else if (localStorage.getItem("filetype") == 'video') {
			document.getElementById("overlay_info_type_video").style.height = "100%";
			document.getElementById("overlay_button_container_video").style.height = "51px";
			overlay_videoplayer.pause();	
		}
		
	// Opening filtering Overlay
	} else if (localStorage.getItem("querytype") == 'openoverlayfilters') {
		document.getElementById("overlay_type_filters").style.height = "100%";
		document.getElementById("overlay_button_container_filters").style.height = "51px";
		
	// Opening general info Overlay
	} else if (localStorage.getItem("querytype") == 'openoverlaygeneralinfo') {
		document.getElementById("overlay_general_info").style.height = "100%";
		document.getElementById("overlay_button_container_generalinfo").style.height = "51px";
		
	// Opening general settings Overlay
	} else if (localStorage.getItem("querytype") == 'openoverlaysettings') {
		document.getElementById("overlay_type_settings").style.height = "100%";
		document.getElementById("overlay_button_container_settings").style.height = "51px";
	
	// Closing overlays
	} else if (localStorage.getItem("querytype") == 'closeoverlay') {
		overlay_videoplayer.pause();
		document.getElementById("overlay_type_image").style.display = "none";
		document.getElementById("overlay_type_video").style.display = "none";
		document.body.style.overflow = "auto";
	} else if (localStorage.getItem("querytype") == 'closeoverlayinfo') {
		document.getElementById("overlay_info_type_image").style.height = "0%";
		document.getElementById("overlay_info_type_video").style.height = "0%";
		document.getElementById("overlay_button_container_image").style.height = "0%";
		document.getElementById("overlay_button_container_video").style.height = "0%";
	} else if (localStorage.getItem("querytype") == 'closeoverlayfilters') {
		document.getElementById("overlay_type_filters").style.height = "0%";
		document.getElementById("overlay_button_container_filters").style.height = "0%";
		document.body.style.overflow = "auto";
	} else if (localStorage.getItem("querytype") == 'closeoverlaygeneralinfo') {
		document.getElementById("overlay_general_info").style.height = "0%";
		document.getElementById("overlay_button_container_generalinfo").style.height = "0%";
		document.body.style.overflow = "auto";
	} else if (localStorage.getItem("querytype") == 'closeoverlaysettings') {
		document.getElementById("overlay_type_settings").style.height = "0%";
		document.getElementById("overlay_button_container_settings").style.height = "0%";
		document.body.style.overflow = "auto";
	}
}


// Function to click through gallery. This is probably a really stupid way to do this, but this way I don't have
// to load all the data into javascript, and I don't have to do this via ajax
function nextmedia() {
	var currentid = parseInt(localStorage.getItem("media_id"));
	var idnext = currentid + 1;
	overlay('closeoverlay');
	document.querySelector("#mediaid" + idnext).click(); 
}

function prevmedia() {
	var currentid = parseInt(localStorage.getItem("media_id"));
	var idprev = currentid - 1;
	overlay('closeoverlay');
	document.querySelector("#mediaid" + idprev).click(); 
}



// Simple preloading function for overlay view
function preloadoverlayimages(nextimage, previousimage) {
	var nxtimg = new Image();
	nxtimg.src = nextimage;
	var prvimg = new Image();
	prvimg.src = previousimage;
}
	
	
	
// Footer-menu Script

var startScrollpos = window.pageYOffset;
var prevScrollpos = window.pageYOffset;
window.onscroll = function() {
var currentScrollPos = window.pageYOffset;
if (currentScrollPos == startScrollpos) {
	document.getElementById("footer-container").style.bottom = "0px";
	document.getElementById("footer-navbar").style.boxShadow = "0 0 0px #5a5a5a";
} else if (prevScrollpos > currentScrollPos) {
	document.getElementById("footer-container").style.bottom = "0px";
	document.getElementById("footer-navbar").style.boxShadow = "0 0 0px #5a5a5a";
} else {
	document.getElementById("footer-container").style.bottom = "-80px";
	document.getElementById("footer-navbar").style.boxShadow = "0 0 0px #5a5a5a";
}
prevScrollpos = currentScrollPos;
}



// Ajax settings calls
function submitsetting(setting, value) {
	xmlhttp = new XMLHttpRequest();
	xmlhttp.open("POST","ajax_settings.php?setting="+setting+"&value="+value,true);
	xmlhttp.send();
	//ajax doesn't work with direct reload afterwards, don't know why...    window.location.reload(true);
}