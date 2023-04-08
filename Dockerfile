FROM php:8.1-alpine

RUN apk add --no-cache $PHPIZE_DEPS git linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json composer.loc[k] ./

RUN composer install

COPY src/ ./
COPY tests/ ./
COPY phpunit.xml ./