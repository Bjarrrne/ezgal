# Changelog

### V0.3

* Added raw image support
* Added processing time measurement for future optimization
  Right now extracting and processing preview images from big raw files (e.g. 18MP) takes the longest, while all of the other processing is quite quick (<0.5s).
* Reworked some naming conventions.
  Thumbnail and intermediate files will now keep their original extension in their filename (e.g. "thumb1.png.jpg"). This is needed because theoretically 2 files with the same filename but different extension could exist in the same folder, which would result in the second thumbnail (thumb1.jpg) would overwrite the first thumbnail. This is prevented by keeping the original extension in the filename.

### V0.2

* Added support for video files
* Added gif support
  This is still lacking because most of the time the processed gif will be bigger than the original gif. At the same time gif thumbnails need to have a specific resolution for the frontend to work correctly. Possible workaround would be to A. generate still image as thumbnail or B. generate mp4s as thumbnails. Or to optimize gif processing.

### V0.1

* Added Dockerfile with necessary tools and extensions