FROM php:8.1-apache

# Install extensions or tools you need
# RUN docker-php-ext-install pdo pdo_pgsql

# Install dependencies for PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    pkg-config \
    libcurl4-openssl-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    libldap2-dev \
    # && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    gd \
    mbstring \
    curl \
    fileinfo \
    xml \
    zip \
    pdo_pgsql \
    pgsql \
    ldap

# Enable Apache mod_rewrite for Laravel
RUN a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Set working directory inside the container
WORKDIR /var/www/html

# Ensure Laravel storage and cache directories have correct permissions
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Debugging: Check installed PHP extensions (optional)
RUN php -m && php --ini && curl --version
