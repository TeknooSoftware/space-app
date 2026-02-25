# FrankenPHP web image replacing httpd + php-fpm for development
FROM dunglas/frankenphp:1.11.3-php8.5

ENV LANG=en_US.UTF-8
ENV LANGUAGE=en_US:fr
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
            git \
            gnupg-agent \
            libbz2-dev \
            libcurl4-openssl-dev \
            libgmp10-dev \
            libicu-dev \
            libnss3-tools \
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
    locale-gen ; \
    curl -L https://github.com/php/pie/releases/latest/download/pie.phar -o /usr/local/bin/pie ; \
    chmod +x /usr/local/bin/pie ; \
    pie install apcu/apcu ; \
    pie install mongodb/mongodb-extension ; \
    pie install pecl/yaml ; \
    pie install xdebug/xdebug ; \
    pie install php-amqp/php-amqp ; \
    pie install phpredis/phpredis ; \
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
    ln -s /usr/lib/x86_64-linux-musl/libc.so /lib/libc.musl-x86_64.so.1 || true ; \
    addgroup --gid 1000 spacegroup ; \
    adduser --no-create-home --disabled-password --disabled-login --uid 1000 --gid 1000 --system spaceuser ; \
    mkdir -p /var/www ; \
    mkdir -p /data/caddy/ ; \
    mkdir -p /etc/caddy ; \
    mkdir -p /var/www/.local/share/caddy/pki /var/www/.config/caddy ; \
    chown -R spaceuser:spacegroup /var/www /data/caddy/

# PHP configuration
ADD 99-space.ini /usr/local/etc/php/conf.d/
COPY --chown=spaceuser:spacegroup Caddyfile /etc/frankenphp/Caddyfile

USER spaceuser:spacegroup

WORKDIR /var/www/space

EXPOSE 80
EXPOSE 443
