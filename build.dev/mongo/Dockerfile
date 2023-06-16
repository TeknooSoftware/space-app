FROM mongo:4.4

MAINTAINER Richard Déloge <richard@teknoo.software>

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update ; \
    apt-get dist-upgrade -y

COPY init_mongo.js /docker-entrypoint-initdb.d/
