FROM php:7.2-fpm

LABEL maintainer="tommymay37@gmail.com"

RUN docker-php-ext-install pdo_mysql \
  && pecl install xdebug \
  && docker-php-ext-enable xdebug

# docker build -t itmayziii/fullheapdeveloper-php:v1 .