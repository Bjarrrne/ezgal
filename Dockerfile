FROM php:7.4-apache

WORKDIR /var/www/html
COPY ./app/ .

# Tools and PHP extensions

RUN apt-get update && apt-get install -y \
	ghostscript \
	ffmpeg \
	exiv2 \
	imagemagick \
	libmagickwand-dev --no-install-recommends \
	&& pecl install imagick \
	&& docker-php-ext-enable imagick \
	&& docker-php-ext-install exif
	
EXPOSE 80 443