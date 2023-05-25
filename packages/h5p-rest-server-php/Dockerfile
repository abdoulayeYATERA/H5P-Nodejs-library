#This is a sample image
FROM trafex/php-nginx:3.0.0

MAINTAINER meet@milliweb.fr, abdoulaye@milliweb.fr

SHELL ["/bin/sh", "-c"]
WORKDIR /var/www/html
COPY ./exportapi.php .
user root
RUN apk add php-zip
user nobody
