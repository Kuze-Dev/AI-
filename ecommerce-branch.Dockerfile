# Use the official Vapor Docker base image for PHP 8.3
FROM laravelphp/vapor:php83

#Upgrade critical system libraries and apk-tools
RUN apk upgrade -Ua 

# Install system dependencies as needed (e.g., for image processing, queues, etc.)
RUN apk --no-cache add \
    git \
    unzip \
    ffmpeg \
    mysql-client \
    gmp \
    libpng \
    libjpeg-turbo \
    libwebp \
    libxpm \
    freetype \
    zlib \
    libzip \
    oniguruma \
    && apk --no-cache add --virtual .build-deps \
    gmp-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    libxpm-dev \
    freetype-dev \
    zlib-dev \
    libzip-dev \
    autoconf \
    build-base

# Install PHP extensions
RUN docker-php-ext-install \
    gmp \
    zip \
    pdo_mysql \
    bcmath \
    exif

# Install Imagick PHP extension
RUN pecl install imagick && \
    docker-php-ext-enable imagick
    
# Clean up to reduce image size
RUN apk del .build-deps

# Optional: Copy PHP config overrides (uncomment if needed)
# COPY ./php.ini /usr/local/etc/php/conf.d/overrides.ini

# Copy application source code
COPY . /var/task

# Set working directory
WORKDIR /var/task

# Ensure necessary directories exist and set correct permissions
RUN mkdir -p /var/task/storage /var/task/bootstrap/cache && \
    chmod -R 755 /var/task/storage /var/task/bootstrap/cache

# Optional Laravel build steps (if not handled by vapor.yml)
# RUN COMPOSER_MIRROR_PATH_REPOS=1 composer install --no-dev --optimize-autoloader && \
#     php artisan event:cache
