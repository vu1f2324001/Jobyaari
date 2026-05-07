# Use PHP 8.2 CLI
FROM php:8.2-cli

# System dependencies
RUN apt-get update && apt-get install -y \
    git curl unzip zip \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    nodejs npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy project
COPY . .

# Install PHP deps (optimize for production)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Install frontend deps
RUN npm install && npm run build

# Laravel required dirs
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# Permissions fix (Render-safe)
RUN chmod -R 775 storage bootstrap/cache

# IMPORTANT: clear cached config (prevents deploy issues)
RUN php artisan config:clear && php artisan cache:clear || true

EXPOSE 10000

# Start server
CMD php artisan serve --host=0.0.0.0 --port=10000
