FROM php:8.0-fpm

RUN #apt-get update && apt-get install -y libonig-dev curl git && pecl install xdebug
RUN apt-get update && apt-get install -y libonig-dev curl git zip unzip

RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis

RUN docker-php-ext-install iconv mbstring mysqli pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#ADD php.ini /usr/local/etc/php/conf.d/40-custom.ini

WORKDIR /var/www

CMD ["php-fpm"]
