FROM php:8.3.15-cli

WORKDIR /app

# Install system packages + PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev libpng-dev libonig-dev libxml2-dev libjpeg-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Fix permissions
RUN chmod -R 777 storage bootstrap/cache

# Clear caches
RUN php artisan config:clear
RUN php artisan cache:clear

EXPOSE 8000

# Run migrate at runtime (ไม่ให้ container crash ถ้า DB ยังไม่พร้อม)
CMD sh -c "php artisan migrate --force || true && php artisan serve --host=0.0.0.0 --port=$PORT"