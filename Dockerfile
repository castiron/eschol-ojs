FROM php:7.2-fpm-alpine

WORKDIR /var/www/html

ENV SERVERNAME="localhost"      \
    HTTPS="on"                  \
    OJS_VERSION=ojs-2_3_6-0 \
    OJS_CLI_INSTALL="0"         \
    OJS_DB_HOST="mysql"     \
    OJS_DB_USER="mysql"           \
    OJS_DB_PASSWORD="mysql"       \
    OJS_DB_NAME="mysql"           \
    OJS_CONF="/var/www/html/config.inc.php"

# PHP extensions
ENV PHP_EXTENSIONS="php-bcmath php-bz2 php-calendar php-ctype php-curl php-dom php-exif php-ftp php-gd php-gettext \
	php-iconv php-json php-opcache php-openssl php-pdo_mysql php-phar php-posix php-shmop php-sockets php-sysvmsg \
	php-sysvsem php-sysvshm php-xml php-xmlreader php-zip php-zlib php-mysqli"

# Required to build OJS:
ENV BUILDERS 		\
	git 			\
	nodejs 			\
	npm

RUN set -xe \
	&& apk add --no-cache --virtual .build-deps $BUILDERS \
	&& apk add --no-cache $PHP_EXTENSIONS

RUN docker-php-ext-install mysqli

# Prepare crontab
#RUN  echo "0 * * * *   ojs-run-scheduled" | crontab - \

CMD [ "docker-php-entrypoint", "php-fpm"]