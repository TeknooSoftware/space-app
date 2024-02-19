FROM php:8.3-cli

ENV LANG en_US.UTF-8
ENV LANGUAGE en_US:fr
ENV LC_ALL en_US.UTF-8
ENV DEBIAN_FRONTEND noninteractive

RUN set -eux ; \
    apt-get clean ; \
    apt-get update ; \
    apt-get dist-upgrade -y  ; \
    apt-get install -y  \
            software-properties-common  \
            sudo  \
            apt-transport-https \
            ca-certificates \
            curl \
            gnupg-agent \
            musl-dev \
            tzdata  \
            wget \
            locales \
            nano  \
            libyaml-dev \
            librabbitmq-dev \
            libbz2-dev \
            libcurl4-openssl-dev \
            libxml2-dev \
            zlib1g-dev \
            libpng-dev \
            libonig-dev \
            libsqlite3-dev \
            libsodium-dev \
            libtidy-dev \
            libxslt-dev \
            libzip-dev \
            libssl-dev \
    ; \
    pecl install apcu ; \
    pecl install mongodb ; \
    pecl install yaml ; \
    pecl install -o -f amqp ; \
    docker-php-ext-enable apcu  \
                          mongodb  \
                          yaml  \
                          amqp \
    ; \
    docker-php-ext-install bcmath \
                           bz2 \
                           calendar \
                           curl \
                           dom \
                           filter \
                           gd \
                           gettext \
                           intl \
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
    locale-gen ; \
    mkdir -p /run/php/ ; \
    cd /usr/local/bin ; \
    addgroup --gid 1000 spacegroup ; \
    adduser --no-create-home --disabled-password --disabled-login --uid 1000 --gid 1000 --system spaceuser ; \
    mkdir -p /var/www ; \
    chown spaceuser:spacegroup /var/www

ADD 99-space.ini /usr/local/etc/php/conf.d/

USER spaceuser:spacegroup
