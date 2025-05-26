# Use the official Vapor Docker base image for PHP 8.3
FROM laravelphp/vapor:php83

# Upgrade system libraries
RUN apk upgrade -Ua

# Install runtime dependencies and image optimizers
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
    ca-certificates \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    nodejs \
    npm \
    libwebp-tools && \
    apk --no-cache add --virtual .build-deps \
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

# Configure GD with WebP support
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp

# Install Imagick PHP extension
RUN pecl install imagick && \
    docker-php-ext-enable imagick

# Install PHP extensions
RUN docker-php-ext-install \
    gmp \
    zip \
    pdo_mysql \
    bcmath \
    exif \
    gd

# Install SVG optimizer
RUN npm install -g svgo

# Clean up build dependencies
RUN apk del .build-deps

# Copy application source code
COPY . /var/task

# Set working directory
WORKDIR /var/task

# Combine RDS and system CA certs
RUN cat /var/task/rds-combined-ca-bundle.pem /etc/ssl/certs/ca-certificates.crt > /etc/ssl/certs/combined-mysql-ca.pem

# Ensure correct permissions
RUN mkdir -p /var/task/storage /var/task/bootstrap/cache && \
    chmod -R 755 /var/task/storage /var/task/bootstrap/cache
