FROM php:8.2-fpm-alpine
RUN apk add --no-cache git curl zip unzip libzip-dev libpng-dev \
    libjpeg-turbo-dev freetype-dev oniguruma-dev icu-dev \
    linux-headers bash \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) pdo_mysql mbstring zip exif pcntl bcmath gd intl opcache sockets
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www
COPY backend/ .
RUN composer install --no-dev --optimize-autoloader --no-interaction \
 && chown -R www-data:www-data storage bootstrap/cache
EXPOSE 9000
CMD ["php-fpm"]
