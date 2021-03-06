<?php

// Scan those sweet little files, analyze them and write them into the DB
$filescan = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('images'));
foreach ($filescan as $foundfile) {

	// If file is a directory, continue in foreach
    if ($foundfile->isDir()){ continue; }
	
	// Minimum fileinfo we need
	$relativepath = $foundfile->getPathname();
	$absolutepath = realpath($relativepath);
	$file_size = filesize($relativepath);
	
	// Here's a check against the DB if an entry with relativepath and filesize already exists (that's my approach to identify images)
	// If yes, it's the same file, so no further processing should be done.
	// If no, it should reanalyze the file, because its another file with the same name or a new file
	$stmt = $db->prepare("SELECT * FROM files WHERE relativepath=:relativepath AND file_size=:file_size");
	$stmt->bindValue(":relativepath", $relativepath);
	$stmt->bindValue(":file_size", $file_size);
	$stmt->execute();
	$alreadyexisting = $stmt->fetchAll();
    if ($alreadyexisting) { continue; }
	
	// If file is not an image or a video, continue in foreach
	$mime = mime_content_type($absolutepath);
	$filetypearray = explode("/", $mime);
	$mimetype = $filetypearray[1];
	$filetype = $filetypearray[0];
	if (!($filetype == 'image' || $filetype == 'video')) {
		echo $absolutepath . " is neither video nor image<br />";
		echo $mimetype;
		print_r($filetypearray);
		echo $filetype;
		continue;
	}
	
	// Analyse files
	// Image files
	if($filetype == 'image') {
		$imagick = new Imagick($absolutepath);
		$exif_data = $imagick->getImageProperties("exif:*");
		//print_r ($exif_data); // Only for processor development
		$color_channel_data = $imagick->getImageChannelStatistics();
			if(empty($exif_data)) { $exif_exists = 'NULL'; } else { $exif_exists = '1'; }
			$dirname			= pathinfo($relativepath, PATHINFO_DIRNAME);
			$basename			= pathinfo($relativepath, PATHINFO_BASENAME);
			$filename			= pathinfo($relativepath, PATHINFO_FILENAME);
			$extension			= pathinfo($relativepath, PATHINFO_EXTENSION);
			// Saved as Unix-Timestamp to format later via date function (e.g.  date('d.m.Y H:i', filemtime($mod_time))
			$mod_date			= filemtime($relativepath);
			$width				= $imagick->getImageWidth();
			$height				= $imagick->getImageHeight();
			// Perhaps getColorValue is the better way?
			$channel_red_mean	= $color_channel_data[1]['mean'];
			$channel_green_mean	= $color_channel_data[2]['mean'];
			$channel_blue_mean	= $color_channel_data[4]['mean'];
			// If this exists I'll sort by that in Frontend
			if(strtotime($exif_data['exif:DateTimeOriginal'])) { $exif_datetime	= strtotime($exif_data['exif:DateTimeOriginal']); } else { $exif_datetime = 'NULL'; }
			// Creating a timestamp to sort by. This is here because I couldn't get the sql query to sort by exif_datetime or (if it doesn't exist) by mod_date
			if($exif_datetime == 'NULL') { $date_to_sort = $mod_date; } else  { $date_to_sort = $exif_datetime; }
			$exif_orientation	= $exif_data['exif:Orientation'];
			$exif_make			= $exif_data['exif:Make'];
			if(!empty($exif_data['exif:Model'])) { $exif_model = $exif_data['exif:Model']; } else { $exif_model = 'Unknown'; }
			$exif_fnumber		= $exif_data['exif:FNumber'];
			$exif_focallength	= $exif_data['exif:FocalLength'];
			$exif_exposuretime	= $exif_data['exif:ExposureTime'];
			$exif_iso			= $exif_data['exif:ISOSpeedRatings'];
			$exif_latitudedata	= $exif_data['exif:GPSLatitude'];
			$exif_latituderef	= $exif_data['exif:GPSLatitudeRef'];
			$exif_latitudearr	= preg_split('/[\s,\/]+/', $exif_latitudedata );
			$exif_latitude_deg	= $exif_latitudearr[0] / $exif_latitudearr[1];
			$exif_latitude_min	= $exif_latitudearr[2] / $exif_latitudearr[3];
			$exif_latitude_sec	= $exif_latitudearr[4] / $exif_latitudearr[5];
			$exif_latitude_dd	= $exif_latitude_deg + ($exif_latitude_min / 60) + ($exif_latitude_sec / 3600);
			if ($exif_latituderef == 'S') { $exif_latitude_dd *= -1; }
			$exif_longitudedata	= $exif_data['exif:GPSLongitude'];
			$exif_longituderef	= $exif_data['exif:GPSLongitudeRef'];
			$exif_longitudearr	= preg_split('/[\s,\/]+/', $exif_longitudedata );
			$exif_longitude_deg = $exif_longitudearr[0] / $exif_longitudearr[1];
			$exif_longitude_min = $exif_longitudearr[2] / $exif_longitudearr[3];
			$exif_longitude_sec = $exif_longitudearr[4] / $exif_longitudearr[5];
			$exif_longitude_dd	= $exif_longitude_deg + ($exif_longitude_min / 60) + ($exif_longitude_sec / 3600);
			if ($exif_longituderef == 'W') { $exif_longitude_dd *= -1; }
			$processed			= '0';
	// Video files	
	} elseif ($filetype == 'video') {
			$processed			= '0';
			$exif_exists 		= 'NULL';
			$exif_datetime		= 'NULL';
			$dirname			= pathinfo($relativepath, PATHINFO_DIRNAME);
			$basename			= pathinfo($relativepath, PATHINFO_BASENAME);
			$filename			= pathinfo($relativepath, PATHINFO_FILENAME);
			$extension			= pathinfo($relativepath, PATHINFO_EXTENSION);
			$file_size			= filesize($relativepath);
			$mod_date			= filemtime($relativepath);
			$date_to_sort		= $mod_date;
	} else {
			$processed			= '0';
			$exif_exists 		= 'NULL';
			$exif_datetime		= 'NULL';
			$dirname			= pathinfo($relativepath, PATHINFO_DIRNAME);
			$basename			= pathinfo($relativepath, PATHINFO_BASENAME);
			$filename			= pathinfo($relativepath, PATHINFO_FILENAME);
			$extension			= pathinfo($relativepath, PATHINFO_EXTENSION);
			$file_size			= filesize($relativepath);
			$mod_date			= filemtime($relativepath);
			$date_to_sort		= $mod_date;
	} // End exif_imagetype if

	// Write data into database
	// INSERT OR REPLACE ensure that a file with a filepath that already exists but a different filesize will get overwritten (= new file)	
	$db->exec("INSERT OR REPLACE INTO files (
		relativepath,
		absolutepath,
		processed,
		mimetype,
		filetype,
		exif_exists,
		dirname,
		basename,
		filename,
		extension,
		file_size,
		mod_date,
		width,
		height,
		channel_red_mean,
		channel_green_mean,
		channel_blue_mean,
		exif_datetime,
		exif_orientation,
		exif_make,
		exif_model,
		exif_fnumber,
		exif_focallength,
		exif_exposuretime,
		exif_iso,
		exif_latituderef,
		exif_latitude_deg,
		exif_latitude_min,
		exif_latitude_sec,
		exif_latitude_dd,
		exif_longituderef,
		exif_longitude_deg,
		exif_longitude_min,
		exif_longitude_sec,
		exif_longitude_dd,
		date_to_sort) VALUES (
	'$relativepath',
	'$absolutepath',
	'$processed',
	'$mimetype',
	'$filetype',
	$exif_exists,
	'$dirname',
	'$basename',
	'$filename',
	'$extension',
	'$file_size',
	'$mod_date',
	'$width',
	'$height',
	'$channel_red_mean',
	'$channel_green_mean',
	'$channel_blue_mean',
	$exif_datetime,
	'$exif_orientation',
	'$exif_make',
	'$exif_model',
	'$exif_fnumber',
	'$exif_focallength',
	'$exif_exposuretime',
	'$exif_iso',
	'$exif_latituderef',
	'$exif_latitude_deg',
	'$exif_latitude_min',
	'$exif_latitude_sec',
	'$exif_latitude_dd',
	'$exif_longituderef',
	'$exif_longitude_deg',
	'$exif_longitude_min',
	'$exif_longitude_sec',
	'$exif_longitude_dd',
	'$date_to_sort'
	)");

// End filescan foreach loop
}

//
// DB is filled with new and changed image data. Now another loop to process the files (=create thumbnails).
//

// Create imagethumbs, videothumbs and intermediate folders
if (!is_writable('lowquals')) { mkdir('lowquals', 0755, true); }
if (!is_writable('thumbs')) { mkdir('thumbs', 0755, true); }
if (!is_writable('intermediates')) { mkdir('intermediates', 0755, true); }
if (!is_writable('videothumbs')) { mkdir('videothumbs', 0755, true); }
if (!is_writable('tmp')) { mkdir('tmp', 0755, true); }
$lowqualsdir = realpath("lowquals");
$thumbsdir = realpath("thumbs");
$intermediatesdir = realpath("intermediates");
$videothumbsdir = realpath("videothumbs");
$tmpdir = realpath("tmp");

// Defining the DB queries for different file formats
$imagickquery = "SELECT * FROM files WHERE mimetype IN ('jpeg', 'tiff', 'png', 'bmp', 'x-bmp', 'x-ms-bmp') AND filetype = 'image' AND processed = 0 ORDER BY id";
$ffmpegquery = "SELECT * FROM files WHERE mimetype IN ('mpeg', 'mp4', 'ogg', 'quicktime', 'webm', 'x-msvideo', 'x-sgi-movie', '3gpp') AND filetype = 'video' AND processed = 0 ORDER BY id";
$gifquery = "SELECT * FROM files WHERE mimetype IN ('gif') AND filetype = 'image' AND processed = 0 ORDER BY id";
$exiv2query = "SELECT * FROM files WHERE mimetype IN ('x-canon-cr2') AND filetype = 'image' AND processed = 0 ORDER BY id";

// Loop for standard image files. No videos, audio or gifs allowed.
foreach ($db->query($imagickquery) as $imagedata) {
	// Starting processtime measurement
	$processstart = microtime(true);
	
	// Setting up data from DB needed
	$origpath = $imagedata['relativepath'];
	$dirname = $imagedata['dirname'];
	$filename = $imagedata['basename'];
	$orientation = $imagedata['exif_orientation'];
	
		// Create thumbnail directories
		$thumbdir = $thumbsdir . "/" . $dirname;
		$thumbfile = $thumbdir . "/" . $filename . ".jpg";
		if (!is_writable($thumbdir)) { mkdir($thumbdir, 0755, true); }
		// Create thumbnails
		$thumb = new Imagick($origpath);
		// Rotate image if exif data says so, with transparent background just in case
		if ($orientation == '3') { $thumb->rotateImage(new ImagickPixel('#00000000'), 180); } elseif ($orientation == '6') { $thumb->rotateImage(new ImagickPixel('#00000000'), 90); } elseif ($orientation == '6') { $thumb->rotateImage(new ImagickPixel('#00000000'), 270); }
		// Resize image using the lanczos resampling algorithm based on width
		$thumb->resizeImage(0,$setting_thumbsize,Imagick::FILTER_LANCZOS,1,FALSE);
		// Sharpen thumbnail
		if ($setting_sharpening == 1) { $thumb->sharpenImage(0, 0.7); }
		// Set to use jpeg compression
		$thumb->setImageCompression(Imagick::COMPRESSION_JPEG);
		// Set compression level (1 lowest quality, 100 highest quality)
		$thumb->setImageCompressionQuality(75);
		// Strip out unneeded meta data
		$thumb->stripImage();
		$thumb->writeImage($thumbfile);
		$thumb->destroy();
		
		// Create intermediate directory
		$intermediatedir = $intermediatesdir . "/" . $dirname;
		$intermediatefile = $intermediatedir . "/" . $filename . ".jpg";
		if (!is_writable($intermediatedir)) { mkdir($intermediatedir, 0755, true); }
		// Create intermediates
		$intermediate = new Imagick($origpath);
		// Rotate image if exif data says so, with transparent background just in case
		if ($orientation == '3') { $intermediate->rotateImage(new ImagickPixel('#00000000'), 180); } elseif ($orientation == '6') { $intermediate->rotateImage(new ImagickPixel('#00000000'), 90); } elseif ($orientation == '6') { $intermediate->rotateImage(new ImagickPixel('#00000000'), 270); }
		// Resize image using the lanczos resampling algorithm based on width
		if ($intermediate->getImageHeight() > 1920) { $intermediate->resizeImage(0,1920,Imagick::FILTER_LANCZOS,1,FALSE); }
		if ($intermediate->getImageWidth() > 1920) { $intermediate->resizeImage(1920,0,Imagick::FILTER_LANCZOS,1,FALSE); }
		/* elseif ($height > 1920) { $intermediate->resizeImage(0,200,Imagick::FILTER_LANCZOS,1,FALSE); } */
		// Sharpen intermediate
		if ($setting_sharpening == 1) { $intermediate->sharpenImage(0, 0.3); }
		// Set to use jpeg compression
		$intermediate->setImageCompression(Imagick::COMPRESSION_JPEG);
		// Set compression level (1 lowest quality, 100 highest quality)
		$intermediate->setImageCompressionQuality(75);
		// Strip out unneeded meta data
		$intermediate->stripImage();
		$intermediate->writeImage($intermediatefile);
		$intermediate->destroy();
		
		// Create lowquals directories
		$lowqualdir = $lowqualsdir . "/" . $dirname;
		$lowqualfile = $lowqualdir . "/" . $filename . ".jpg";
		if (!is_writable($lowqualdir)) { mkdir($lowqualdir, 0755, true); }
		// Create lowquals
		$lowqual = new Imagick($origpath);
		// Rotate image if exif data says so, with transparent background just in case
		if ($orientation == '3') { $lowqual->rotateImage(new ImagickPixel('#00000000'), 180); } elseif ($orientation == '6') { $lowqual->rotateImage(new ImagickPixel('#00000000'), 90); } elseif ($orientation == '6') { $lowqual->rotateImage(new ImagickPixel('#00000000'), 270); }
		// Resize image using the lanczos resampling algorithm based on width
		$lowqual->resizeImage(0,$setting_thumbsize,Imagick::FILTER_LANCZOS,1,FALSE);
		// Sharpen lowqual
		if ($setting_sharpening == 1) { $lowqual->sharpenImage(0, 0.7); }
		// Set to use jpeg compression
		$lowqual->setImageCompression(Imagick::COMPRESSION_JPEG);
		// Set compression level (1 lowest quality, 100 highest quality)
		$lowqual->setImageCompressionQuality(10);
		// Strip out unneeded meta data
		$lowqual->stripImage();
		$lowqual->writeImage($lowqualfile);
		$lowqual->destroy();
		
	// Ending processtime measurement
	$processend = microtime(true);
	$processtime = $processend - $processstart;
	// Write success into database
	$db->exec("UPDATE files SET processed = 1, processtime = '$processtime' WHERE relativepath = '$origpath'");
	
// End standard images processing loop
}

// Loop for videos
foreach ($db->query($ffmpegquery) as $videodata) {
	// Starting processtime measurement
	$processstart = microtime(true);
	
	// Setting up data from DB needed
	$origpath = $videodata['relativepath'];
	$dirname = $videodata['dirname'];
	$filename = $videodata['basename'];
	
	// Create videothumbnail directories
	$videothumbdir = $videothumbsdir . "/" . $dirname;
	$videothumbfile = $videothumbdir . "/" . $filename . ".png";
	if (!is_writable($videothumbdir)) { mkdir($videothumbdir, 0755, true); }
	
	// Create thumbnail with ffmpeg
	exec('ffmpeg -i '.$origpath.' -ss 00:00:01.000 -vframes 1 -vf scale=-1:'.$setting_thumbsize.' '.$videothumbfile.'');
	
	// This can be used to generate gifs from videos, but atm the gifs are way to big so its not used
	// exec('ffmpeg -ss 0.1 -t 2.5 -i '.$origpath.' -filter_complex "[0:v] fps=12,scale='.$setting_thumbsize.':-1,split [a][b];[a] palettegen [p];[b][p] paletteuse" '.$videothumbdir.'/'.$filename.'.gif');
	
	// Ending processtime measurement
	$processend = microtime(true);
	$processtime = $processend - $processstart;
	// Write success into database
	$db->exec("UPDATE files SET processed = 1, processtime = '$processtime' WHERE relativepath = '$origpath'");
	
// End video processing loop
}

// Loop for gifs (have to be resized for frontend to work properly)
foreach ($db->query($gifquery) as $gifdata) {
	// Starting processtime measurement
	$processstart = microtime(true);
	
	// Setting up data from DB needed
	$origpath = $gifdata['relativepath'];
	$dirname = $gifdata['dirname'];
	$filename = $gifdata['basename'];
	
	// Create gifthumbnail directories
	$thumbdir = $thumbsdir . "/" . $dirname;
	$thumbfile = $thumbdir . "/" . $filename . ".gif";
	if (!is_writable($thumbdir)) { mkdir($thumbdir, 0755, true); }
	
	// Create thumbnail with ffmpeg (seems to be kind of complicated with imagemagick...)
	exec('ffmpeg -hide_banner -v warning -i '.$origpath.' -filter_complex "[0:v] scale=-1:'.$setting_thumbsize.':flags=lanczos,split [a][b]; [a] palettegen=reserve_transparent=on:transparency_color=ffffff [p]; [b][p] paletteuse" '.$thumbfile.'');
	
	// Ending processtime measurement
	$processend = microtime(true);
	$processtime = $processend - $processstart;
	// Write success into database
	$db->exec("UPDATE files SET processed = 1, processtime = '$processtime' WHERE relativepath = '$origpath'");
	
// End gifs processing loop
}

// Loop for raw image files
foreach ($db->query($exiv2query) as $rawdata) {
	// Starting processtime measurement
	$processstart = microtime(true);
	
	// Setting up data from DB needed
	$origpath = $rawdata['relativepath'];
	$dirname = $rawdata['dirname'];
	$filename = $rawdata['filename'];
	$basename = $rawdata['basename'];
	
	// Extract largest preview to /tmp/
	$previews = array();
	exec('exiv2 -pp "'.$origpath.'"', $previews);
	$previewnumber = count($previews);
	if ($previewnumber == 0) { continue; }
	exec('exiv2 -ep'.$previewnumber.' -l '.$tmpdir.' '.$origpath.'');
	$origpath = ''.$tmpdir.'/'.$filename.'-preview'.$previewnumber.'.jpg';
	
	// Create raw thumbnail directories
	$thumbdir = $thumbsdir . "/" . $dirname;
	$thumbfile = $thumbdir . "/" . $basename . ".jpg";
	if (!is_writable($thumbdir)) { mkdir($thumbdir, 0755, true); }
	
	// Create thumbnails
	$thumb = new Imagick($origpath);
	// Rotate image if exif data says so, with transparent background just in case
	if ($orientation == '3') { $thumb->rotateImage(new ImagickPixel('#00000000'), 180); } elseif ($orientation == '6') { $thumb->rotateImage(new ImagickPixel('#00000000'), 90); } elseif ($orientation == '6') { $thumb->rotateImage(new ImagickPixel('#00000000'), 270); }
	// Resize image using the lanczos resampling algorithm based on width
	$thumb->resizeImage(0,$setting_thumbsize,Imagick::FILTER_LANCZOS,1,FALSE);
	// Sharpen thumbnail
	if ($setting_sharpening == 1) { $thumb->sharpenImage(0, 0.7); }
	// Set to use jpeg compression
	$thumb->setImageCompression(Imagick::COMPRESSION_JPEG);
	// Set compression level (1 lowest quality, 100 highest quality)
	$thumb->setImageCompressionQuality(75);
	// Strip out unneeded meta data
	$thumb->stripImage();
	$thumb->writeImage($thumbfile);
	$thumb->destroy();
	
	// Create intermediate directory
	$intermediatedir = $intermediatesdir . "/" . $dirname;
	$intermediatefile = $intermediatedir . "/" . $basename . ".jpg";
	if (!is_writable($intermediatedir)) { mkdir($intermediatedir, 0755, true); }
	
	// Create intermediates
	$intermediate = new Imagick($origpath);
	// Rotate image if exif data says so, with transparent background just in case
	if ($orientation == '3') { $intermediate->rotateImage(new ImagickPixel('#00000000'), 180); } elseif ($orientation == '6') { $intermediate->rotateImage(new ImagickPixel('#00000000'), 90); } elseif ($orientation == '6') { $intermediate->rotateImage(new ImagickPixel('#00000000'), 270); }
	// Resize image using the lanczos resampling algorithm based on width
	if ($intermediate->getImageHeight() > 1920) { $intermediate->resizeImage(0,1920,Imagick::FILTER_LANCZOS,1,FALSE); }
	if ($intermediate->getImageWidth() > 1920) { $intermediate->resizeImage(1920,0,Imagick::FILTER_LANCZOS,1,FALSE); }
	// Sharpen intermediate
	if ($setting_sharpening == 1) { $intermediate->sharpenImage(0, 0.3); }
	// Set to use jpeg compression
	$intermediate->setImageCompression(Imagick::COMPRESSION_JPEG);
	// Set compression level (1 lowest quality, 100 highest quality)
	$intermediate->setImageCompressionQuality(75);
	// Strip out unneeded meta data
	$intermediate->stripImage();
	$intermediate->writeImage($intermediatefile);
	$intermediate->destroy();
	
	// Delete tmp file
	unlink($origpath);
	$origpath = $rawdata['relativepath'];

	// Ending processtime measurement
	$processend = microtime(true);
	$processtime = $processend - $processstart;
	// Write success into database
	$db->exec("UPDATE files SET processed = 1, processtime = '$processtime' WHERE relativepath = '$origpath'");

// End raw processing loop
}


// Remove files from database that do not exist anymore, and remove their intermediate and thumb files
$removequery = "SELECT * FROM files";
foreach ($db->query($removequery) as $removefile) {
	if (file_exists($removefile['relativepath'])) {
		continue;
	} else {
		$origpath = $removefile['relativepath'];
		$dirname = $removefile['dirname'];
		$filename = $removefile['basename'];
		$thumbdir = $thumbsdir . "/" . $dirname;
		$thumbfile = $thumbdir . "/" . $filename;
		$intermediatedir = $intermediatesdir . "/" . $dirname;
		$intermediatefile = $intermediatedir . "/" . $filename;
		unlink($thumbfile);
		unlink($intermediatefile);
		$db->exec("DELETE FROM files WHERE relativepath = '$origpath'");
	}
// End removal loop
}

?>