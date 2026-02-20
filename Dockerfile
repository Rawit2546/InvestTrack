FROM php:8.3.15-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev libpng-dev libonig-dev libxml2-dev libjpeg-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Fix permissions
RUN chmod -R 777 storage bootstrap/cache

# Clear config cache (สำคัญมาก)
RUN php artisan config:clear
RUN php artisan cache:clear

# Railway ใช้ PORT env
EXPOSE 8080

# 🔥 รัน migrate ก่อน start server
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8080