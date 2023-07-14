FROM ubuntu:kinetic

MAINTAINER Richard Déloge <richard@teknoo.software>

ENV LANG en_US.UTF-8
ENV LANGUAGE en_US:fr
ENV LC_ALL en_US.UTF-8
ENV DEBIAN_FRONTEND noninteractive

RUN apt-get clean ; \
    apt-get update ; \
    apt-get dist-upgrade -y  ; \
    apt-get install -y  \
        software-properties-common  \
        sudo  \
        apt-transport-https \
        ca-certificates \
        curl \
        gnupg-agent \
        wget \
        locales \
    ; \
    locale-gen ; \
    LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php ; \
    wget -O - https://packagecloud.io/gpg.key | apt-key add - ; \
    wget -q -O - https://packages.blackfire.io/gpg.key | apt-key add - ; \
    echo "deb http://packages.blackfire.io/debian any main" | tee /etc/apt/sources.list.d/blackfire.list  ; \
    apt-get update ; \
    apt-get install -y  \
            tzdata  \
            wget \
            nano  \
            php8.2-common \
            php8.2-cli  \
            php8.2-fpm  \
            php8.2-mysql \
            php8.2-apcu \
            php8.2-gd  \
            php8.2-curl \
            php8.2-intl \
            php8.2-phpdbg \
            php8.2-sqlite3  \
            php8.2-xdebug \
            git  \
            php8.2-xml \
            php8.2-mbstring \
            php8.2-bcmath \
            php8.2-mongodb \
            php8.2-soap \
            php8.2-amqp \
            blackfire-agent \
    ; \
    locale-gen ; \
    mkdir -p /run/php/ ; \
    mkdir -p /var/log/blackfire/

ADD 99-space.ini /etc/php/8.2/fpm/conf.d/
ADD 99-space.ini /etc/php/8.2/cli/conf.d/
ADD space.pool.conf /etc/php/8.2/fpm/pool.d/

RUN usermod -u 1000 www-data ; \
    mkdir -p /var/www ; \
    chown www-data:www-data /var/www ; \
    echo '#!/bin/bash' > /opt/startup.sh ; \
    echo 'php-fpm8.2 -F --fpm-config /etc/php/8.2/fpm/php-fpm.conf' >> /opt/startup.sh ; \
    chmod +x /opt/startup.sh

CMD ["/opt/startup.sh"]

EXPOSE 9000
