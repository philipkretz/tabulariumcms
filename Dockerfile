# Use the official PHP 8.4 image
FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    icu-dev \
    libzip-dev \
    jpeg-dev \
    freetype-dev \
    libwebp-dev \
    gmp-dev \
    imagemagick \
    imagemagick-dev \
    autoconf \
    g++ \
    make \
    postgresql-dev \
    mysql-client \
    linux-headers

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo_pgsql \
    mysqli \
    pgsql \
    gd \
    zip \
    intl \
    opcache \
    bcmath \
    gmp \
    exif \
    sockets

# Install imagick via PECL
RUN pecl install imagick \
    && docker-php-ext-enable imagick

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Set environment to production to prevent loading dev bundles
ENV APP_ENV=prod

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Copy PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Expose port
EXPOSE 9000

CMD ["php-fpm"]