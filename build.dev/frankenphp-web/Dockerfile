# FrankenPHP web image replacing httpd + php-fpm for development
FROM dunglas/frankenphp:1.9-php8.4.14

ENV LANG=en_US.UTF-8
ENV LANGUAGE=en_US:fr
ENV LC_ALL=en_US.UTF-8
ENV DEBIAN_FRONTEND=noninteractive

ENV XDG_DATA_HOME=/var/www/.local/share
ENV XDG_CONFIG_HOME=/var/www/.config
ENV CADDY_HOME=/var/www/.local/share/caddy

# Install required libs and PHP extensions similar to php-fpm image
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
    ln -s /usr/lib/x86_64-linux-musl/libc.so /lib/libc.musl-x86_64.so.1 || true ; \
    mkdir -p /run/php/ ; \
    addgroup --gid 1000 spacegroup ; \
    adduser --no-create-home --disabled-password --disabled-login --uid 1000 --gid 1000 --system spaceuser ; \
    mkdir -p /var/www ; \
    chown -R spaceuser:spacegroup /var/www ; \
    mkdir -p /etc/caddy ; \
    mkdir -p /var/www/.local/share/caddy /var/www/.config/caddy ; \
    chown -R spaceuser:spacegroup /var/www/.local /var/www/.config

# PHP configuration
ADD 99-space.ini /usr/local/etc/php/conf.d/
COPY --chown=spaceuser:spacegroup Caddyfile /etc/caddy/Caddyfile

USER spaceuser:spacegroup

WORKDIR /var/www/space

EXPOSE 80
EXPOSE 443
