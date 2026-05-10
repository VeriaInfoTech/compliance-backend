# PHP 8.3 FPM on Debian Bookworm (Ubuntu-compatible)
FROM php:8.3-fpm-bookworm

LABEL maintainer="shahr-bank" \
    description="Laminas backend with PHP 8.3 FPM"

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    zlib1g-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    intl \
    zip \
    pdo_mysql \
    gd \
    soap

# PECL extensions (Redis, MongoDB 1.x for mongodb/mongodb library compatibility)
RUN pecl install redis mongodb-1.21.2 \
    && docker-php-ext-enable redis mongodb

# Ensure PHP-FPM listens on all interfaces (for nginx in another container)
RUN echo "listen = 0.0.0.0:9000" >> /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /var/www
