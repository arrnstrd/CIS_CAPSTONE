FROM php:8.3-fpm

# Install system dependencies (added gosu at the bottom)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    pkg-config \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    imagemagick \
    libmagickwand-dev \
    gosu \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install \
    bcmath \
    exif \
    gd \
    intl \
    mbstring \
    pcntl \
    pdo \
    pdo_pgsql \
    pgsql \
    zip \
    xml \
    dom

# Install Imagick
RUN pecl install imagick \
    && docker-php-ext-enable imagick

# Enable Opcache
RUN docker-php-ext-enable opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]