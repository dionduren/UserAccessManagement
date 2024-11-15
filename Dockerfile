FROM php:8.1-apache

# Install extensions or tools you need
# RUN docker-php-ext-install pdo pdo_pgsql

# Install dependencies for PostgreSQL
RUN apt-get update && apt-get install -y git \
    && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql

# Copy your application code (optional if already mounted via volumes)
# COPY . /var/www/html/
