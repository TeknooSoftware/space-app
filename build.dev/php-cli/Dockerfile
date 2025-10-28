FROM php:8.4.13-cli

ENV LANG=en_US.UTF-8
ENV LANGUAGE=en_US:fr
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
    docker-php-source extract ; \
    # fix bug in php 8.4.13 \
    if [ -f /usr/src/php/ext/dom/lexbor/lexbor/selectors-adapted/selectors.c ]; then \
        sed -i 's|#include "ext/dom/lexbor/lexbor/selectors-adapted/selectors.h"|#include "lexbor/selectors-adapted/selectors.h"|g' \
            /usr/src/php/ext/dom/lexbor/lexbor/selectors-adapted/selectors.c ; \
    fi ; \
    pecl install apcu ; \
    pecl install mongodb ; \
    pecl install yaml ; \
    pecl install -o -f amqp ; \
    docker-php-ext-enable apcu  \
                          mongodb  \
                          yaml  \
                          amqp ; \
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
    locale-gen ; \
    mkdir -p /run/php/ ; \
    addgroup --gid 1000 spacegroup ; \
    adduser --no-create-home --disabled-password --disabled-login --uid 1000 --gid 1000 --system spaceuser ; \
    mkdir -p /var/www ; \
    chown spaceuser:spacegroup /var/www

ADD 99-space.ini /usr/local/etc/php/conf.d/

USER spaceuser:spacegroup
