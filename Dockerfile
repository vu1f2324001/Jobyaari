# Use the official PHP 8.2 CLI image
FROM php:8.2-cli

# Install system dependencies for Laravel and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    nodejs \
    npm \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install and enable PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Copy Composer from the official composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory inside the container
WORKDIR /var/www

# Copy the entire project into the container
COPY . /var/www

# Install project dependencies safely for production
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Install Node dependencies and build frontend assets
RUN npm install && npm run build

# Ensure required directories exist
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# FIXED permissions (NO chown)
RUN chmod -R 775 storage bootstrap/cache

# Expose the port Render expects
EXPOSE 10000

# Start the Laravel application using artisan serve
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
