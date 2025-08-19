# Debian 12 (bookworm) base
FROM php:8.1-apache

ENV DEBIAN_FRONTEND=noninteractive
WORKDIR /var/www/html

# System + build deps
RUN apt-get update && apt-get install -y \
    apt-transport-https ca-certificates curl gnupg \
    git nano unzip \
    build-essential autoconf \
    libpq-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libzip-dev libxml2-dev libonig-dev \
    unixodbc-dev \
    freetds-bin freetds-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd zip pdo_pgsql bcmath xml \
    && docker-php-ext-install pdo_dblib

# Apache: enable mod_rewrite and set DocumentRoot to /public
RUN a2enmod rewrite \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Laravel dirs & permissions
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache
