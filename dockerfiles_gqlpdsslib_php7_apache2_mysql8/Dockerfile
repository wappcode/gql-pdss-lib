FROM php:7.3-apache

RUN apt-get update \
        && apt-get install -y \
        vim \
        libzip-dev zip \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        && docker-php-ext-configure zip --with-libzip \
        && docker-php-ext-install mysqli pdo pdo_mysql zip \    
        && curl -sS https://getcomposer.org/installer | php \
        && mv composer.phar /usr/local/bin/ \
        && ln -s /usr/local/bin/composer.phar /usr/local/bin/composer \
        && apt-get install -y git \
        && pecl install xdebug-3.1.2 \
        && docker-php-ext-enable xdebug \
        && docker-php-ext-install gd


RUN cp /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/  \
        && cp /etc/apache2/mods-available/headers.load /etc/apache2/mods-enabled/       

COPY ./app-apache.conf /etc/apache2/sites-enabled/


WORKDIR /var/www/html
