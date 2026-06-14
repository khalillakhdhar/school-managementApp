FROM php:8.2-fpm-alpine AS builder
WORKDIR /app
RUN apk add --no-cache build-base linux-headers autoconf git curl libpng-dev libjpeg-turbo-dev libwebp-dev zlib-dev libzip-dev mysql-client
RUN docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd bcmath ctype fileinfo mbstring pdo pdo_mysql tokenizer xml zip opcache
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts
COPY . .
RUN composer run-script post-autoload-dump --no-interaction 2>/dev/null || true

FROM php:8.2-fpm-alpine
WORKDIR /app
RUN apk add --no-cache libpng libjpeg-turbo libwebp zlib libzip mysql-client bash curl
RUN docker-php-ext-install -j$(nproc) gd bcmath ctype fileinfo mbstring pdo pdo_mysql tokenizer xml zip opcache
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY --from=builder /app /app
RUN mkdir -p storage/logs storage/app/public bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
HEALTHCHECK --interval=30s --timeout=10s --start-period=10s --retries=3 \
    CMD php-fpm-healthcheck || exit 1
EXPOSE 9000
CMD ["php-fpm"]
