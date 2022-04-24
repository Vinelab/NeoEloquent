FROM php:8.1-alpine

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk add git \
    && apk del -f .build-deps

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json composer.loc[k] ./

RUN composer install

COPY Examples/ ./
COPY src/ ./
COPY tests/ ./
COPY phpunit.xml .travis.yml ./