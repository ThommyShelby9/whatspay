FROM php:8.4.1-fpm AS base

WORKDIR /var/www/html

RUN rm /etc/apt/preferences.d/no-debian-php
RUN apt-get update && apt-get install -y build-essential nano mariadb-client zlib1g-dev \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev libxpm-dev libvpx-dev locales libonig-dev libxml2-dev\
    libmagickwand-dev zip unzip libzip-dev libpq-dev php-soap vim netcat-traditional iputils-ping \
    wget curl python-is-python3 2to3 cron postgresql-server-dev-all wget git supervisor sudo nginx \
    && docker-php-ext-configure gd \
    && docker-php-ext-install pgsql pdo_pgsql pdo_mysql zip gd soap intl sockets bcmath exif\
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-enable opcache

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration files
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy application
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage

# Create startup script
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Expose ports
EXPOSE 9000 80

# Start services
CMD ["/start.sh"]