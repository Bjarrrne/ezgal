# ezgal
An easy to setup, non-destructive image gallery with a nice mobile-first frontend.

## A bit more info
The idea is to create a super simple web-frontend for your self-hosted images, that kind of resembles the design of Google Photos, and that can be set up super easily with docker. 

## Features
* Does **not** change anything about your original files or folders.
* Simply mount your original files folder(s) into it
* Creates thumbnails automatically (may take a while on first startup)
* No need for a specific way of adding files. Simply add them in any way you want. FTP, PhotSync App, WebDav, etc.
* Everything is stored locally! No need for an internet connection because this doesn't use CDNs for external assets. This might be stupid but at the same time I wanted to create something that can work even when your server isn't connected to the internet (except GMaps, which is only activated when adding an API-Key).
* Compatible file formats: 
  * Everything Imagemagick has delegates installed for
  * Raw files
  * Standard HTML5 compatible videos
  * The rest is trial and error I guess.

## What I'd like to accomplish (kind of a Roadmap)
- [x] Show Gifs/Videos (still having frontend problems)
  - [ ] Thumbnails for videos? (ffmpeg etc.)
- [x] Show Metadata/Exif data
- [x] Get Lazyload to work
- [x] Fullscreen slideshow when image is opened (with preloader)
- [ ] Filtering options according to metadata (date, file type, metadata, etc.)
- [ ] Ability to create a Gif from monthly/daily picturesets (because why not?)
- [ ] Create Docker image that combines everything to a ready to go solution

## System
* SQLite. Might be too slow for huge images collections, but we'll see how it goes. Pro: Doesn't need MYSQL to be set up as an extra service
* PHP on NGINX
* Image processing:
  * Imagemagick/Imagick for standard file formats
  * ufraw/exiv2 for raw files
* Frontend
  * LazyLoad (v. 15.1.1, https://github.com/verlok/lazyload)
  * Normalize.cc for normalization (v. 8.0.1, https://github.com/necolas/normalize.css/)
  * FontAwesome for fancy icons

## What this doesn't do
* This doesn't sync your files
* No login, user management or sharing functions planned as of yet
* Login and HTTPS should be done via the reverse proxy of your choice, e.g. Traefik

## Prerequisites (for non-docker setups)
* Server with
  * imagemagick and the necessary delegates installed
  * ufraw
  * exiv2
* PHP with
  * SQLite3 installed
  * imagick extension installed
  * --enable-exif flag set

 ## Is this safe to use for public galleries?
 * NO!
 * Right now all the metadata can be accessed quite easily. Until I took some measures for safety (hide the data, secure data queries, etc.) you should only use this in public if... actually, no, don't use this in public.

## 2dos
* [ ] Check installed extensions (sqlite, imagick, ufraw etc.) and inform user if something is missing

* Flesh out processing:
  * [x] Deletion of deleted images from DB and thumbnails/intermediates
  * Ensure that only compatible files are being used
  * Optimize large archive processing
* Map
  * [x] Finalize long/lat usage in file details (works, I think?)