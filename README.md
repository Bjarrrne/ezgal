# ezgal
An easy to setup, non-destructive image gallery with a nice mobile-first frontend.

## A bit more info
The idea is to create a super simple web-frontend for your self-hosted images, that kind of resembles the design of Google Photos, and that can be set up super easily with docker. 

## Features
* Does not change anything about your original files or folders. Nothing gets removed or added
* Simply mount your original files folder(s) into it
* Create thumbnails periodically and automatically
* Offer simple search and sort functions (date, file type, metadata, etc.)
* No need for a specific way of adding files. Simply add them in any way you want. FTP, PhotSync App, whatever

## System
* SQLite as a database. Might be too slow for huge images collections, but we'll see how it goes. Pro: Doesn't need an extra MYSQL container to be set up
* PHP on NGINX
* Imagick for thumbnail creation
* CSS (Javascript if needed, let's see...)
* A Cronjob for periodical thumbnail creation, I guess
* Docker image that combines everything to a ready to go solution

## What this doesn't do
* This doesn't sync your files
* No login, user management or sharing functions planned as of yet
* Login and HTTPS should be done via the reverse proxy of your choice, e.g. Traefik
