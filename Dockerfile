FROM php:8.2-fpm-alpine

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create directories and set permissions
RUN mkdir -p /var/www/storage/app \
    && mkdir -p /var/www/storage/framework/cache \
    && mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/testing \
    && mkdir -p /var/www/storage/framework/views \
    && mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/bootstrap/cache

# Copy application files
COPY . /var/www

# Set proper permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Copy .env.example to .env for build process
RUN cp .env.example .env

# Install dependencies without running scripts that need .env
RUN composer install --optimize-autoloader --no-dev --no-interaction --no-scripts

# Build frontend assets
RUN npm install && npm run build

# Expose port
EXPOSE 8000

# Copy startup script
COPY start.sh /var/www/start.sh
RUN chmod +x /var/www/start.sh

# Start command
CMD ["bash", "/var/www/start.sh"]