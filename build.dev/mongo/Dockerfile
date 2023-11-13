FROM mongo:4.4.25

ENV DEBIAN_FRONTEND noninteractive

RUN set -eux ; \
    apt-get update ; \
    apt-get dist-upgrade -y

COPY init_mongo.js /docker-entrypoint-initdb.d/
