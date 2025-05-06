# Use the official Vapor Docker base image for PHP 8.3 on ARM
FROM laravelphp/vapor:php83

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

# Clean up to reduce image size
RUN apk del .build-deps

# Optional: Copy PHP config overrides (uncomment if needed)
# COPY ./php.ini /usr/local/etc/php/conf.d/overrides.ini

# Copy application source code
COPY . /var/task

# Set working directory
WORKDIR /var/task

# Run Laravel-specific build steps (handled by vapor.yml too, this is fallback)
RUN COMPOSER_MIRROR_PATH_REPOS=1 composer install --no-dev --optimize-autoloader && \
    php artisan event:cache

# Ensure correct permissions for storage and bootstrap
RUN chmod -R 755 /var/task/storage /var/task/bootstrap/cache
