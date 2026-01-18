FROM httpd:2.4.66-alpine3.23

USER root:root

RUN set -eux ; \
    apk update ; \
    apk upgrade --available ;  \
    apk add --no-cache \
                openssl ; \
    rm -rf /var/cache/apk/* ; \
    rm -rf /usr/local/apache2/conf/original ; \
    rm -f /usr/local/apache2/conf/extra/httpd-vhosts.conf ; \
    rm -f /usr/local/apache2/conf/extra/httpd-ssl.conf ; \
    rm -f /usr/local/apache2/conf/vhosts/www.conf ; \
    mkdir -p /usr/local/apache2/ssl ; \
    mkdir -p /usr/local/apache2/conf/vhosts ; \
    mkdir -p /usr/local/apache2/logs

COPY --chown=root:root ./httpd.conf /usr/local/apache2/conf/httpd.conf
COPY --chown=root:root vhost.conf /usr/local/apache2/conf/vhosts/www.conf

RUN addgroup -g 1000 spacegroup ; \
    adduser -u 1000 -G spacegroup -D -s /sbin/nologin spaceuser  ; \
    cd /usr/local/apache2/ssl ; \
    openssl genrsa -des3 -passout pass:x -out server.pass.key 2048 ; \
    openssl rsa -passin pass:x -in server.pass.key -out server.key ; \
    openssl req -new -key server.key -out server.csr \
            -subj "/C=Fr/ST=Normandie/L=Caen/O=Teknoo Software/OU=Space/CN=localhost" ; \
    openssl x509 -req -days 365 -in server.csr -signkey server.key -out server.crt ; \
    chown -R spaceuser:spacegroup /usr/local/apache2/ssl ; \
    chown -R spaceuser:spacegroup /usr/local/apache2/logs ; \
    chown -R spaceuser:spacegroup /usr/local/apache2/conf/vhosts

USER spaceuser:spacegroup

EXPOSE 80
EXPOSE 443

