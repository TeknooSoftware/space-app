ARG MONGO_VERSION=7

FROM mongo:$MONGO_VERSION

ENV DEBIAN_FRONTEND noninteractive

RUN set -eux ; \
    apt-get update ; \
    apt-get dist-upgrade -y

COPY init_mongo.js /docker-entrypoint-initdb.d/
