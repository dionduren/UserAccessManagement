FROM php:8.1-apache

# Install extensions or tools you need
# RUN docker-php-ext-install pdo pdo_pgsql

# Install dependencies for PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    nano \
    git \
    unzip \
    curl \
    && apt-get install -y \ 
    libpq-dev \
    && docker-php-ext-install \ 
    fileinfo \
    gd \
    ext-gd \
    zip \
    ext-zip\
    pdo_pgsql \
    pgsql

# Enable Apache mod_rewrite for Laravel
RUN a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Ensure Laravel storage and cache directories have correct permissions
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
