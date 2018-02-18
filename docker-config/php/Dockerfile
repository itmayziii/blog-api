FROM php:7.2-fpm
RUN docker-php-ext-install pdo_mysql \
  && pecl install xdebug \
  && docker-php-ext-enable xdebug