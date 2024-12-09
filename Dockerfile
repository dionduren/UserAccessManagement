FROM php:8.1-apache

# Install extensions or tools you need
# RUN docker-php-ext-install pdo pdo_pgsql

# Install dependencies for PostgreSQL
# RUN apt-get update && apt-get install -y git \
#     git \
#     unzip \
#     curl \
#     && apt-get install -y libpq-dev \
#     libfreetype6-dev \
#     libjpeg62-turbo-dev \
#     libpng-dev \
#     libonig-dev \
#     libxml2-dev \
#     libzip-dev \
#     libldap2-dev \
#     && docker-php-ext-configure gd --with-freetype --with-jpeg \
#     && docker-php-ext-install gd mbstring curl fileinfo xml zip pdo_pgsql pgsql ldap
RUN apt-get update && apt-get install -y git \
    && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql

# Copy your application code (optional if already mounted via volumes)
# COPY . /var/www/html/
# COPY /var/www/UserAccessManagement/php.ini /usr/local/etc/php/conf.d/

# Enable Apache mod_rewrite
# RUN a2enmod rewrite

# Install Composer
# RUN curl -sS https://getcomposer.org/installer | php \
#     && mv composer.phar /usr/local/bin/composer

# Set working directory
# WORKDIR /var/www/html

# Optional: Copy application code (if not using volumes)
# COPY . .

# Set permissions for Laravel
# RUN mkdir -p storage bootstrap/cache \
#     && chown -R www-data:www-data /var/www/html \
#     && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
