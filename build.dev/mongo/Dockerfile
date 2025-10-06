ARG MONGO_VERSION=7.0.25

FROM mongo:$MONGO_VERSION

ENV DEBIAN_FRONTEND=noninteractive

RUN set -eux ; \
    apt-get update ; \
    apt-get dist-upgrade -y ; \
    apt-get install -y netcat

COPY init_mongo.js /docker-entrypoint-initdb.d/
