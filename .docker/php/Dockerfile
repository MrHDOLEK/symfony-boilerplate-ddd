ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-fpm-bullseye as base

ARG COMPOSER_VERSION=2.8.4

RUN usermod -u 1001 www-data && groupmod -g 1001 www-data

RUN apt-get update && apt-get install -y nginx python3 supervisor \
     git zip bzip2 libpng-dev libpng-dev libicu-dev \
     vim libjpeg62-turbo-dev libfreetype6-dev libonig-dev \
     libpq-dev libxpm-dev libvpx-dev libbz2-dev zlib1g-dev libzip-dev gnumeric \
    && curl -sS https://getcomposer.org/installer | php -- --version="${COMPOSER_VERSION}" --install-dir=/usr/local/bin --filename=composer \
    && docker-php-ext-install intl bcmath mbstring bz2 gd zip pgsql pdo_pgsql opcache pcntl sockets \
    && pecl install apcu \
    && docker-php-ext-enable intl pcntl apcu \
    && apt-get remove -y git \
    && apt-get autoremove -y \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \

EXPOSE 9000

FROM base as dev

ARG INSTALL_XDEBUG=false

RUN apt-get update && apt-get install -y nginx

RUN if [ ${INSTALL_XDEBUG} = true ]; then \
    pecl install xdebug \
    && docker-php-ext-enable xdebug \
;fi

EXPOSE 8080

ENTRYPOINT php-fpm -D && nginx -g 'daemon off;'