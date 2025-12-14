# Stage 1: Composer dependencies
FROM composer:2 AS composer
WORKDIR /app

# Copy composer files first for caching
COPY composer.json composer.lock ./
COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Stage 2: PHP runtime
FROM php:8.2-cli

# Install system dependencies + PHP extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        pkg-config \
        libxml2-dev \
        libpq-dev \
        libzip-dev \
        zip \
        unzip \
        git \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_pgsql mbstring xml zip bcmath \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . .

# Copy vendor from composer stage
COPY --from=composer /app/vendor ./vendor

# Fix permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Environment variables
ENV APP_ENV=production
ENV FIREBASE_STORAGE_BUCKET=photoapp-71ba5.firebasestorage.app

# Expose port
EXPOSE 8000

# Run migrations then start server
CMD sh -c "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000"
