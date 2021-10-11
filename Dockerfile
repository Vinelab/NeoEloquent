FROM php:8.0-alpine

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del -f .build-deps

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json composer.loc[k] ./

RUN composer install --ignore-platform-reqs #temporary workaround as the bolt library incorrectly enforces the sockets extension

COPY Examples/ ./
COPY src/ ./
COPY tests/ ./
COPY phpunit.xml .travis.yml ./