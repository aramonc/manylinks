FROM php:7.1-fpm
RUN apt-get update \
    && apt-get install -y libssl-dev \
    && pecl install mongodb \
    && pecl install xdebug \
    && docker-php-ext-enable mongodb xdebug \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/zz-xdebug.ini \
    && echo "xdebug.remote_connect_back=on" >> /usr/local/etc/php/conf.d/zz-xdebug.ini \
    && echo "xdebug.remote_autostart=on" >> /usr/local/etc/php/conf.d/zz-xdebug.ini \
    && echo "xdebug.idekey=\"PHPSTORM\"" >> /usr/local/etc/php/conf.d/zz-xdebug.ini
