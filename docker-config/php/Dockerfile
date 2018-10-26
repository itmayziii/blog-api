FROM php:7.2-fpm

LABEL maintainer="tommymay37@gmail.com"

RUN apt-get update && apt-get install -y libcurl4-openssl-dev pkg-config libssl-dev supervisor
COPY laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf

RUN docker-php-ext-install pdo_mysql \
  && pecl install redis-3.1.6 \
  && pecl install mongodb \
  && docker-php-ext-enable redis mongodb

# Xdebug - not appropriate for a production build
RUN yes | pecl install xdebug \
  && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
  && echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
  && echo "xdebug.scream=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
  && echo "xdebug.profiler_enable=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
  && echo "xdebug.idekey=IDEA_DEBUG" >> /usr/local/etc/php/conf.d/xdebug.ini \
  && echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
  && echo "xdebug.remote_autostart=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
  && echo "xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/xdebug.ini \
  && echo "xdebug.remote_connect_back=0" >> /usr/local/etc/php/conf.d/xdebug.ini \
  && echo "xdebug.remote_host=docker.for.mac.localhost" >> /usr/local/etc/php/conf.d/xdebug.ini \
  && echo "xdebug.remote_port=9001" >> /usr/local/etc/php/conf.d/xdebug.ini \
  && echo "xdebug.profiler_output_dir=/var/www/html/tmp" >> /usr/local/etc/php/conf.d/xdebug.ini

# docker build -t itmayziii/fullheapdeveloper-php:1 .
