FROM php:8.2.12-fpm

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
    ; \
    pecl install apcu ; \
    pecl install mongodb ; \
    pecl install yaml ; \
    pecl install xdebug ; \
    pecl install -o -f amqp ; \
    docker-php-ext-enable apcu  \
                          mongodb  \
                          yaml  \
                          amqp \
                          xdebug \
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
                           mysqli \
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
    wget -O - https://packagecloud.io/gpg.key | apt-key add - ; \
    wget -q -O - https://packages.blackfire.io/gpg.key | sudo dd of=/usr/share/keyrings/blackfire-archive-keyring.asc ; \
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/blackfire-archive-keyring.asc] http://packages.blackfire.io/debian any main" | sudo tee /etc/apt/sources.list.d/blackfire.list ; \
    apt-get update ; \
    apt-get install -y blackfire-php \
    ; \
    apt-get clean ; \
    ln -s /usr/lib/x86_64-linux-musl/libc.so /lib/libc.musl-x86_64.so.1 ; \
    locale-gen ; \
    printf "blackfire.agent_socket=tcp://\${PHP_BLACKFIRE_SERVER}:8307\n" > /usr/local/etc/php/conf.d/90-blackfire.ini ; \
    mkdir -p /run/php/ ; \
    mkdir -p /var/log/blackfire/ ; \
    cd /usr/local/bin ; \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" ; \
    php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" ; \
    php composer-setup.php ; \
    php -r "unlink('composer-setup.php');" ; \
    mv composer.phar composer ; \
    chmod +x composer ; \
    usermod -u 1000 www-data ; \
    mkdir -p /var/www ; \
    chown www-data:www-data /var/www

ADD 99-space.ini /usr/local/etc/php/conf.d/
ADD space.pool.conf /usr/local/etc/php-fpm.d/www.conf
