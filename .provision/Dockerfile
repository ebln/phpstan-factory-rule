FROM php:8.0-cli

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install -y git nano zip unzip zlib1g-dev libzip-dev \
    && pecl install -o xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* /tmp/pear/* \
    && touch /var/log/xdebug.log && chgrp 1000 /var/log/xdebug.log && chmod g+w /var/log/xdebug.log \
    && sed -i -e '/alias l\|export\|eval/s/#//' /root/.bashrc \
    && mkdir --parents /var/www/src /var/www/tests \
    && true
COPY 30-xdebug-config.ini /usr/local/etc/php/conf.d/

ENV PATH "$PATH:/root/.composer/vendor/bin"

RUN composer global require friendsofphp/php-cs-fixer \
    && sed -i -e '/alias l\|export\|eval/s/#//' /root/.bashrc

WORKDIR /var/www/
