FROM composer:latest as vendor

WORKDIR /app/

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --no-dev \
    --optimize-autoloader

COPY . /app/

RUN composer dump-autoload --no-scripts --no-dev --optimize

FROM node:latest as assets

WORKDIR /app/

COPY ./package.json ./webpack.config.js ./package-lock.json /app/
COPY ./assets /app/assets/

RUN npm install
RUN npm run build

FROM php:7.2-fpm-alpine

RUN set -xe \
    && apk add --update \
        icu \
    && apk add --no-cache --virtual .php-deps \
        make \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        zlib-dev \
        icu-dev \
        g++ \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        intl \
    && docker-php-ext-enable intl \
    && { find /usr/local/lib -type f -print0 | xargs -0r strip --strip-all -p 2>/dev/null || true; } \
    && apk del .build-deps \
    && rm -rf /tmp/* /usr/local/lib/php/doc/* /var/cache/apk/*

RUN docker-php-ext-install -j$(nproc) bcmath \
    && docker-php-ext-install -j$(nproc) pdo_mysql \
    && docker-php-ext-install -j$(nproc) pcntl

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY . /app/
COPY --from=vendor /app/vendor/ /app/vendor/
COPY --from=assets /app/public/build/manifest.json /app/public/build/manifest.json
COPY --from=assets /app/public/build/entrypoints.json /app/public/build/entrypoints.json

RUN mkdir -p /app/var/cache /app/var/log \
    && chown -R www-data.www-data /app/
