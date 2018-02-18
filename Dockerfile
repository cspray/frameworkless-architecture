FROM php:7.2
ENV TERM vt100
RUN apt-get update -qq && apt-get install -yq apt-utils build-essential wget libpq-dev
RUN docker-php-ext-install pdo pdo_pgsql
RUN pecl install xdebug-2.6.0 \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini
COPY . /usr/src/myapp
WORKDIR /usr/src/myapp