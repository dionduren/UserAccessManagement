FROM php:8.1-apache

# Install dependencies for PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    nano \
    unzip \
    curl \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    && docker-php-ext-install \ 
    gd \
    zip \
    pdo_pgsql \
    pgsql \
    bcmath \
    xml \
    curl \
    fileinfo \
    openssl
# xml \
# mbstring

# Enable Apache mod_rewrite for Laravel
RUN a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Ensure Laravel storage and cache directories have correct permissions
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy custom PHP configuration
# COPY php-config/php.ini /usr/local/etc/php/conf.d/custom.ini

# Define work directory
WORKDIR /var/www/html
