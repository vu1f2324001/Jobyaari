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

# Set the correct permissions for Laravel's cache and storage
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose the port Render expects
EXPOSE 10000

# Start the Laravel application using artisan serve
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
