FROM php:7.2-fpm

LABEL maintainer="tommymay37@gmail.com"

RUN apt-get update && apt-get install -y libcurl4-openssl-dev pkg-config libssl-dev supervisor
COPY laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf

RUN docker-php-ext-install pdo_mysql \
  && pecl install redis-3.1.6 \
  && pecl install xdebug \
  && pecl install mongodb \
  && docker-php-ext-enable xdebug redis mongodb

# docker build -t itmayziii/fullheapdeveloper-php:1 .
