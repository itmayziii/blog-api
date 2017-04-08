FROM ubuntu:16.04
MAINTAINER itmazyiii <tommymay37@gmail.com>
CMD service php7.0-fpm start && \
  service nginx start && \
  /bin/bash

RUN apt-get update

RUN echo "deb http://nginx.org/packages/mainline/ubuntu/ xenial nginx" >> /etc/apt/sources.list
RUN echo "deb-src http://nginx.org/packages/mainline/ubuntu/ xenial nginx" >> /etc/apt/sources.list

# Nginx
RUN apt-get -y install wget
RUN wget http://nginx.org/keys/nginx_signing.key
RUN apt-key add nginx_signing.key
RUN apt-get update
RUN apt-get -y install nginx
COPY ./nginx.conf /etc/nginx/conf.d/default.conf
COPY ./php.ini /etc/php/7.0/fpm/php.ini

# PHP 7
RUN apt-get -y install software-properties-common
RUN add-apt-repository -y ppa:ondrej/php
RUN apt-get update
RUN apt-get -y --allow-unauthenticated install php7.0-fpm php7.0-xml php7.0-mcrypt php7.0-mbstring php7.0-zip php7.0-mysql
COPY ./www.conf /etc/php/7.0/fpm/pool.d/www.conf

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/bin --filename=composer
RUN php -r "unlink('composer-setup.php');"

# Node
RUN apt-get -y --allow-unauthenticated install nodejs
RUN apt-get -y --allow-unauthenticated install npm
RUN ln -s /usr/bin/nodejs /usr/bin/node

# Tools
RUN apt-get -y install vim



# things to note
# change conf.d/default.conf file 
# change user to www-data in nginx.conf file
# change the fast cgi loading to 0
# bounce php-fpm - service php7.0-fpm start / service php7.0-fpm restart
# bounce nginx - php7.0-fpm start / service php7.0-fpm restart
# install composer dependencies
# install node modules


# docker run --name laravelfromscratch -v /Users/txm241/Sites/LaravelFromScratch2016/project/:/var/www/html/project/ --link mysqlcontainer:mysql  -p 8080:80 -dit laravelfromscratch:v1
# docker run --name mysqlcontainer -v /Users/txm241/Sites/LaravelFromScratch2016/project/data/:/var/lib/mysql/ -e MYSQL_ROOT_PASSWORD=C12H22o11 -p3306:3306 -dit mysql:latest
# This does not actually expose port 8080 on the host machine, unfortunately this does not seem possible except for when running the container, this is more of a suggestion.
EXPOSE 8080:80