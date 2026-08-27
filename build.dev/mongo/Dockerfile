ARG MONGO_VERSION=8.3

FROM mongo:$MONGO_VERSION

ENV DEBIAN_FRONTEND=noninteractive

RUN set -ux ; \
    apt-get update ; \
    apt-get dist-upgrade -y

COPY init_mongo.js /docker-entrypoint-initdb.d/
