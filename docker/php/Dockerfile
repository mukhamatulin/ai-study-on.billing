FROM php:8.4-fpm-alpine

RUN apk add --no-cache git icu-dev libzip-dev postgresql-dev \
    && docker-php-ext-install intl opcache pdo_pgsql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
