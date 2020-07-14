# ezgal
An easy to setup, non-destructive image (media?) gallery with a nice mobile-first frontend.

## A bit more info
The idea is to create a super simple web-frontend for your self-hosted images, that kind of resembles the design of Google Photos, and that can be set up super easily with docker. 

## Features
* Does **not** change anything about your original files or folders.
* Simply mount your original media folder into it
* Creates thumbnails automatically (may take a while on first startup)
* No need for a specific way of adding files. Simply add them in any way you want. FTP, PhotoSync App, WebDav, etc.
* Everything is stored locally! No need for an internet connection because this doesn't use CDNs for external assets. This might be stupid but at the same time I wanted to create something that can work even when your server isn't connected to the internet (except GMaps, which is only activated when adding an API-Key).
* Compatible file formats: 
  * Standard web-compliant image files (jpg, png, bmp have been tested)
  * Raw files
  * Standard HTML5 compatible videos
  * Standard HTML5 compatible audio
  * The rest is trial and error I guess.

## What this doesn't do

* This doesn't sync your files
* No login, user management or sharing functions planned as of yet. Login and HTTPS should be done via the reverse proxy of your choice, e.g. Traefik

## Installation

### With docker

- Use docker run and mount the correct media folder into it, e.g. like
  `docker run -d -p 80:80 -v /your/media/folder:/var/www/html/images barrrne/ezgal:latest`
- Or use docker compose

### Without docker

Without docker you can simply upload all files under `app` to your host. Then, inside `app`, create the folder `images`. This is where you will have to store all your media files.

Keep in mind that you will have to install the following tools/extensions for this to work properly:

* Tools
  * imagemagick
  * exiv2
  * ffmpeg (not yet implemented)
  * ghostscript (not yet implemented)
* PHP extensions (installed and enabled)
  * SQLite3 / pdo_sqlite
  * imagick
  * exif (keep in mind to set the --enable-exif flag)

 ## Is this safe to use for public galleries?
 * NO!
 * Right now all the metadata can be accessed quite easily. Until I took some measures for safety (hide the data, secure data queries, etc.) you should only use this in public if... actually, no, don't use this in public.

## System

* SQLite. Might be too slow for huge images collections, but we'll see how it goes. Pro: Doesn't need MYSQL to be set up as an extra service
* PHP on Apache
* Image processing:
  * Imagemagick/Imagick for standard file formats
  * ufraw/exiv2 for raw files
  * ffmpeg for video files
* Frontend
  * LazyLoad (v. 15.1.1, https://github.com/verlok/lazyload)
  * Normalize.cc for normalization (v. 8.0.1, https://github.com/necolas/normalize.css/)
  * FontAwesome for fancy icons

## What I'd like to accomplish (kind of a Roadmap)

- [x] Show Gifs/Videos (still having frontend problems)
  - [ ] Thumbnails for videos? (ffmpeg etc.)
  - [ ] "Thumbnails" for audio
- [x] Show Metadata/Exif data
- [x] Get Lazyload to work
- [x] Fullscreen slideshow when image is opened (with preloader)
- [ ] Filtering options according to metadata (date, file type, metadata, etc.)
- [x] Create Docker image that combines everything to a ready to go solution
