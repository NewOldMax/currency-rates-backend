FROM php:7.1-fpm

# PHP

RUN apt-get update && \
    apt-get install -y libmcrypt-dev libpq-dev netcat libxml2-dev libc-client-dev libkrb5-dev && \
    rm -r /var/lib/apt/lists/*

RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl && \
    docker-php-ext-install \
        mcrypt \
        bcmath \
        mbstring \
        zip \
        opcache \
        soap \
        pdo pdo_pgsql \
        sockets

RUN yes | pecl install apcu xdebug-beta \
        && echo "extension=$(find /usr/local/lib/php/extensions/ -name apcu.so)" > /usr/local/etc/php/conf.d/apcu.ini \
        && echo "apc.enable_cli=1" > /usr/local/etc/php/conf.d/apcu.ini \
        && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
        && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
        && echo "xdebug.remote_autostart=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
        && echo "xdebug.remote_connect_back=on" >> /usr/local/etc/php/conf.d/xdebug.ini

RUN apt-get update
RUN apt-get upgrade -y

WORKDIR /srv
CMD ["bash", "boot.sh"]

RUN yes | echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/memory_limit.ini

COPY devops/config/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY . /srv/
