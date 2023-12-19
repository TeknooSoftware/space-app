FROM rabbitmq:management-alpine

USER root:root

# Define environment variables.
ENV RABBITMQ_USERNAME space_usr
ENV RABBITMQ_PASSWORD space_pwd
ENV RABBITMQ_PID_FILE /var/lib/rabbitmq/space/rabbitmq

ADD init.sh /init.sh

RUN set -eux ; \
    apk update ; \
    apk upgrade --available ; \
    rm -rf /var/cache/apk/* ; \
    chmod +x /init.sh

CMD ["/init.sh"]

USER 1001
