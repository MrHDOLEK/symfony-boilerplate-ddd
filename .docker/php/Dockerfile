ARG PHP_VERSION=8.5.9
FROM php:${PHP_VERSION}-fpm-trixie AS base

ARG COMPOSER_VERSION=2.10.2
ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN usermod -u 1001 www-data && groupmod -g 1001 www-data

RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        git \
        unzip \
        zip \
        bzip2 \
        libpng-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libfreetype-dev \
        libonig-dev \
        libpq-dev \
        libbz2-dev \
        libzip-dev \
        zlib1g-dev \
    && curl -sS https://getcomposer.org/installer \
        | php -- --version="${COMPOSER_VERSION}" --install-dir=/usr/local/bin --filename=composer \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        bcmath \
        mbstring \
        bz2 \
        gd \
        zip \
        pgsql \
        pdo_pgsql \
        pcntl \
        sockets \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && rm -f /etc/nginx/sites-enabled/default \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY .docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY .docker/php/php.ini /usr/local/etc/php/conf.d/php.ini
COPY .docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/zz-www.conf

WORKDIR /var/www

EXPOSE 8080

FROM base AS dev

ARG INSTALL_XDEBUG=false

COPY .docker/php/xdebug.ini /tmp/xdebug.ini
RUN if [ "${INSTALL_XDEBUG}" = "true" ]; then \
        pecl install xdebug \
        && docker-php-ext-enable xdebug \
        && cp /tmp/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini \
    ;fi; rm -f /tmp/xdebug.ini

CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]

FROM base AS prod

ENV APP_ENV=prod
ENV APP_DEBUG=0

COPY --chown=www-data:www-data . /var/www

COPY .docker/php/opcache-prod.ini /usr/local/etc/php/conf.d/zz-opcache-prod.ini

RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --classmap-authoritative --no-scripts

RUN composer dump-env prod --empty

RUN APP_SECRET=build-time DB_HOST=build-time DB_PORT=5432 DB_NAME=build-time \
    DB_USER=build-time DB_PASSWORD=build-time \
    sh -c 'php bin/console cache:clear --env=prod --no-debug \
        && php bin/console cache:warmup --env=prod --no-debug' \
    && chown -R www-data:www-data /var/www/var

CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
