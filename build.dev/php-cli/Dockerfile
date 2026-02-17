FROM php:8.5-cli

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
            git \
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
    # fix lexbor include paths for php 8.5 \
    if [ -f /usr/src/php/ext/dom/parentnode/css_selectors.c ]; then \
        sed -i 's|#include "ext/lexbor/lexbor/css/parser.h"|#include "lexbor/css/parser.h"|g' \
            /usr/src/php/ext/dom/parentnode/css_selectors.c ; \
        sed -i 's|#include "ext/lexbor/lexbor/selectors/selectors.h"|#include "lexbor/selectors/selectors.h"|g' \
            /usr/src/php/ext/dom/parentnode/css_selectors.c ; \
    fi ; \
    export CFLAGS="${CFLAGS:-} -I/usr/src/php/ext/lexbor" ; \
    curl -L https://github.com/php/pie/releases/latest/download/pie.phar -o /usr/local/bin/pie ; \
    chmod +x /usr/local/bin/pie ; \
    pie install apcu/apcu ; \
    pie install mongodb/mongodb-extension ; \
    pie install pecl/yaml ; \
    pie install php-amqp/php-amqp ; \
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
