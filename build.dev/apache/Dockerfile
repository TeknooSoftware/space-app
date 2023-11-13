FROM ubuntu:mantic

ENV DEBIAN_FRONTEND noninteractive

RUN set -eux ; \
    apt-get update ; \
    apt-get dist-upgrade -y ; \
    apt-get install -y tzdata apache2 libapache2-mod-security2 ; \
    apt-get clean

ADD web.conf /etc/apache2/sites-available/web.conf

RUN set -eux ; \
    a2dissite 000-default ; \
    a2ensite web ; \
    a2enmod alias ; \
    a2enmod headers ; \
    a2enmod proxy ; \
    a2enmod proxy_http ; \
    a2enmod proxy_fcgi ; \
    a2enmod rewrite ; \
    a2enmod ssl ; \
    a2enmod security2 ; \
    usermod -u 1001 ubuntu; \
    usermod -u 1000 www-data

CMD ["apache2ctl", "-DFOREGROUND"]

EXPOSE 80
EXPOSE 443
