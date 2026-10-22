ARG MONGO_VERSION=9

FROM mongo:$MONGO_VERSION

ENV DEBIAN_FRONTEND=noninteractive

RUN set -ux ; \
    apt-get update ; \
    apt-get dist-upgrade -y ; \
    apt-get install -y netcat-openbsd

COPY init_mongo.js /docker-entrypoint-initdb.d/
