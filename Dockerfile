FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress \
    --no-scripts

COPY . .

RUN composer dump-autoload --optimize --classmap-authoritative

FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json ./
RUN npm install

COPY . .
RUN npm run build

FROM php:8.4-apache AS app

WORKDIR /var/www/html

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    libpq-dev \
    libsqlite3-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install -j"$(nproc)" \
    bcmath \
    mbstring \
    opcache \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    xml \
    && a2enmod rewrite headers \
    && sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf \
    && rm -rf /var/lib/apt/lists/*

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.save_comments=1'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

COPY --from=vendor /app /var/www/html
COPY --from=frontend /app/public/build /var/www/html/public/build

RUN cp .env.example .env \
    && php artisan key:generate --force \
    && php artisan migrate --force

RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
        database \
    && touch database/database.sqlite \
    && chown -R www-data:www-data \
        storage \
        bootstrap/cache \
        database \
    && chmod -R ug+rwx storage bootstrap/cache \
    && chmod 775 database \
    && chmod 664 database/database.sqlite

HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD curl -fsS http://localhost/up || exit 1

EXPOSE 80

CMD ["apache2-foreground"]
