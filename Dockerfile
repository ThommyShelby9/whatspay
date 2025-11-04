FROM php:8.4.1-fpm AS base

WORKDIR /var/www/html

RUN rm /etc/apt/preferences.d/no-debian-php
RUN apt-get update && apt-get install -y build-essential nano mariadb-client zlib1g-dev  \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev libxpm-dev libvpx-dev locales libonig-dev libxml2-dev\
    libmagickwand-dev zip unzip libzip-dev libpq-dev php-soap vim netcat-traditional iputils-ping  \
    wget curl python-is-python3 2to3 cron postgresql-server-dev-all wget git supervisor sudo \
    && docker-php-ext-configure gd \
    && docker-php-ext-install pgsql pdo_pgsql pdo_mysql zip gd soap intl sockets bcmath exif\
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-enable opcache

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN chmod -R 777 /var/www/html/

# Expose port 9000 and start php-fpm server
EXPOSE 9000



