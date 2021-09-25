FROM php:8.0

RUN pecl install xdebug && docker-php-ext-enable xdebug