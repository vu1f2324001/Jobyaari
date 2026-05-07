# Use official PHP 8.2 CLI image
FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    nodejs \
    npm \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (IMPORTANT FIX: pgsql added)
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Install frontend dependencies & build assets
RUN npm install && npm run build

# Create required Laravel folders
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# Fix permissions (safe version)
RUN chmod -R 775 storage bootstrap/cache

# Expose Render port
EXPOSE 10000

# Start Laravel (SAFE ORDER)
CMD php artisan migrate --force && \
    php artisan config:clear && \
    php artisan cache:clear && \
    php artisan serve --host=0.0.0.0 --port=10000
