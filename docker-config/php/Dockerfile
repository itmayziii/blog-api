FROM php:7.2-fpm

LABEL maintainer="tommymay37@gmail.com"

RUN docker-php-ext-install pdo_mysql \
  && pecl install redis-3.1.6 \
  && pecl install xdebug \
  && docker-php-ext-enable xdebug redis

# docker build -t itmayziii/fullheapdeveloper-php:v1 .