FROM php:8.3.15-cli

WORKDIR /app

# ติดตั้ง dependency ที่ Laravel ต้องใช้
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev libpng-dev libonig-dev libxml2-dev libjpeg-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl gd

# ติดตั้ง Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# คัดลอกไฟล์โปรเจกต์
COPY . .

# ติดตั้ง package
RUN composer install --no-dev --optimize-autoloader

# ตั้ง permission
RUN chmod -R 777 storage bootstrap/cache

# Railway ใช้ PORT env variable
EXPOSE 8080

# รัน Laravel (ไม่ต้อง migrate ที่นี่)
CMD php artisan serve --host=0.0.0.0 --port=8080