FROM php:8.4-fpm

ENV LANG=en_US.UTF-8
ENV LANGUAGEen=_US:fr
ENV LC_ALL=en_US.UTF-8
ENV DEBIAN_FRONTEND=noninteractive

RUN set -eux ; \
    apt-get clean ; \
    apt-get update ; \
    apt-get dist-upgrade -y  ; \
    apt-get install -y  \
            apt-transport-https \
            ca-certificates \
            curl \
            gnupg-agent \
            libbz2-dev \
            libcurl4-openssl-dev \
            libgmp10-dev \
            libicu-dev \
            libonig-dev \
            libpng-dev \
            librabbitmq-dev \
            libsodium-dev \
            libsqlite3-dev \
            libssl-dev \
            libtidy-dev \
            libxml2-dev \
            libxslt-dev \
            libyaml-dev \
            libzip-dev \
            locales \
            musl-dev \
            nano  \
            sudo  \
            tzdata  \
            wget \
            zlib1g-dev \
    ; \
    locale-gen ; \
    pecl install apcu ; \
    pecl install mongodb ; \
    pecl install yaml ; \
    pecl install xdebug ; \
    pecl install -o -f amqp ; \
    pecl install -o -f redis ; \
    docker-php-ext-enable apcu  \
                          mongodb  \
                          yaml  \
                          amqp \
                          xdebug \
                          redis \
    ; \
    docker-php-ext-install bcmath \
                           bz2 \
                           calendar \
                           curl \
                           dom \
                           filter \
                           gd \
                           gettext \
                           gmp \
                           intl \
                           iconv \
                           mbstring \
                           pcntl \
                           pdo \
                           pdo_sqlite \
                           opcache \
                           sockets \
                           sodium \
                           simplexml \
                           soap \
                           tidy \
                           xml  \
                           xsl  \
                           zip \
    ; \
    apt-get clean ; \
    ln -s /usr/lib/x86_64-linux-musl/libc.so /lib/libc.musl-x86_64.so.1 ; \
    mkdir -p /run/php/ ; \
    cd /usr/local/bin ; \
    addgroup --gid 1000 spacegroup ; \
    adduser --no-create-home --disabled-password --disabled-login --uid 1000 --gid 1000 --system spaceuser ; \
    mkdir -p /var/www ; \
    chown spaceuser:spacegroup /var/www

ADD 99-space.ini /usr/local/etc/php/conf.d/
ADD space.pool.conf /usr/local/etc/php-fpm.d/www.conf

USER spaceuser:spacegroup
