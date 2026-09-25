FROM php:8.5-cli

# This worker deploys jobs to `docker-compose` clusters with ansible-playbook over SSH
# (SPACE_DC_ANSIBLE_BINARY / SPACE_DC_TIMEOUT / SPACE_DC_DEPLOY_ROOT are read by execute_job),
# and Ansible refuses to start unless Python reports a UTF-8 preferred encoding. C.UTF-8 is
# always present, unlike a locale that must first be generated.
ENV LANG=C.UTF-8
ENV LC_ALL=C.UTF-8
ENV PYTHONIOENCODING=utf-8
ENV DEBIAN_FRONTEND=noninteractive
ENV NERDCTL_VERSION=1.7.2

RUN set -eux ; \
    apt-get clean ; \
    apt-get update ; \
    apt-get dist-upgrade -y  ; \
    apt-get install -y  \
            aardvark-dns \
            ansible-core \
            apt-transport-https \
            buildah \
            ca-certificates \
            containerd \
            containernetworking-plugins \
            curl \
            fuse-overlayfs \
            git \
            gnupg-agent \
            libbz2-dev \
            libcurl4-openssl-dev \
            libgmp10-dev \
            libicu-dev \
            libonig-dev \
            libpng-dev \
            librabbitmq-dev \
            libsodium-dev \
            libsqlite3-dev \
            libssl-dev \
            libtidy-dev \
            libxml2-dev \
            libxslt-dev \
            libyaml-dev \
            libzip-dev \
            musl-dev \
            nano  \
            netavark \
            openssh-client \
            passt \
            rootlesskit \
            slirp4netns \
            sudo  \
            tzdata  \
            uidmap \
            wget \
            zlib1g-dev \
    ; \
    docker-php-source extract ; \
    # fix bug in php 8.4.13 \
    if [ -f /usr/src/php/ext/dom/lexbor/lexbor/selectors-adapted/selectors.c ]; then \
        sed -i 's|#include "ext/dom/lexbor/lexbor/selectors-adapted/selectors.h"|#include "lexbor/selectors-adapted/selectors.h"|g' \
            /usr/src/php/ext/dom/lexbor/lexbor/selectors-adapted/selectors.c ; \
    fi ; \
    # fix lexbor include paths for php 8.5 \
    if [ -f /usr/src/php/ext/dom/parentnode/css_selectors.c ]; then \
        sed -i 's|#include "ext/lexbor/lexbor/css/parser.h"|#include "lexbor/css/parser.h"|g' \
            /usr/src/php/ext/dom/parentnode/css_selectors.c ; \
        sed -i 's|#include "ext/lexbor/lexbor/selectors/selectors.h"|#include "lexbor/selectors/selectors.h"|g' \
            /usr/src/php/ext/dom/parentnode/css_selectors.c ; \
    fi ; \
    export CFLAGS="${CFLAGS:-} -I/usr/src/php/ext/lexbor" ; \
    curl -L https://github.com/php/pie/releases/latest/download/pie.phar -o /usr/local/bin/pie ; \
    chmod +x /usr/local/bin/pie ; \
    docker-php-ext-install bcmath \
                           bz2 \
                           calendar \
                           curl \
                           dom \
                           filter \
                           gd \
                           gettext \
                           gmp \
                           intl \
                           iconv \
                           mbstring \
                           pcntl \
                           pdo \
                           pdo_sqlite \
                           sockets \
                           sodium \
                           simplexml \
                           soap \
                           tidy \
                           xml  \
                           xsl  \
                           zip \
    ; \
    pie install apcu/apcu ; \
    pie install mongodb/mongodb-extension ; \
    pie install pecl/yaml ; \
    pie install php-amqp/php-amqp ; \
    apt-get clean ; \
    ln -s /usr/lib/x86_64-linux-musl/libc.so /lib/libc.musl-x86_64.so.1 ; \
    mkdir -p /run/php/ ; \
    cd /usr/local/bin ; \
    wget -q "https://github.com/containerd/nerdctl/releases/download/v${NERDCTL_VERSION}/nerdctl-full-${NERDCTL_VERSION}-linux-amd64.tar.gz" -O /tmp/nerdctl.tar.gz ; \
    tar -C . -xzf /tmp/nerdctl.tar.gz --strip-components 1 bin/nerdctl ; \
    rm -f /tmp/nerdctl.tar.gz ; \
    addgroup --gid 1000 spacegroup ; \
    # The home must also be the one recorded in /etc/passwd, not only the HOME variable: buildah \
    # resolves it through the user database in several code paths. \
    adduser --home /home/spaceuser --disabled-password --disabled-login --uid 1000 --gid 1000 --system spaceuser ; \
    mkdir -p /var/www ; \
    chown spaceuser:spacegroup /var/www ; \
    mkdir -p /home/spaceuser/.config/containers \
             /home/spaceuser/.local/share/containers/storage \
    ; \
    chmod 0700 /home/spaceuser/.config/containers \
               /home/spaceuser/.local/share/containers/storage \
    ; \
    printf '%s\n' \
        '[storage]' \
        'driver = "overlay"' \
        'runroot = "/tmp/run-user-1000/containers/storage"' \
        'graphroot = "/home/spaceuser/.local/share/containers/storage"' \
        '' \
        '[storage.options.overlay]' \
        'mount_program = "/usr/bin/fuse-overlayfs"' \
        'mountopt = "nodev"' \
        > /home/spaceuser/.config/containers/storage.conf \
    ; \
    chmod 0600 /home/spaceuser/.config/containers/storage.conf ; \
    # `install -d` and `mkdir -p` only own the last component, and buildah refuses to start when any \
    # directory of its configuration path belongs to another user, so the whole home is given away. \
    chown -R spaceuser:spacegroup /home/spaceuser ; \
    # Alpine ships /etc/containers/storage.conf and the production image only patches it; Debian \
    # ships no such file, so it is written here with the same two settings: fuse-overlayfs as the \
    # mount program, and /var/lib/shared as an additional (read-only) image store. \
    install -d -m 0755 /etc/containers ; \
    printf '%s\n' \
        '[storage]' \
        'driver = "overlay"' \
        'runroot = "/run/containers/storage"' \
        'graphroot = "/var/lib/containers/storage"' \
        '' \
        '[storage.options]' \
        'additionalimagestores = [' \
        '"/var/lib/shared",' \
        ']' \
        '' \
        '[storage.options.overlay]' \
        'mount_program = "/usr/bin/fuse-overlayfs"' \
        'mountopt = "nodev"' \
        > /etc/containers/storage.conf \
    ; \
    mkdir -p /var/lib/shared/overlay-images \
             /var/lib/shared/overlay-layers \
             /var/lib/shared/vfs-images \
             /var/lib/shared/vfs-layers \
    ; \
    touch /var/lib/shared/overlay-images/images.lock \
          /var/lib/shared/overlay-layers/layers.lock \
          /var/lib/shared/vfs-images/images.lock \
          /var/lib/shared/vfs-layers/layers.lock \
    ; \
    echo "spaceuser ALL=(ALL:ALL) NOPASSWD: /usr/bin/containerd" > /etc/sudoers.d/space ; \
    echo "spaceuser ALL=(ALL:ALL) NOPASSWD: /usr/bin/ctr" >> /etc/sudoers.d/space ; \
    echo "spaceuser ALL=(ALL:ALL) NOPASSWD: /usr/local/bin/nerdctl" >> /etc/sudoers.d/space ; \
    chmod 0440 /etc/sudoers.d/space ; \
    echo spaceuser:100000:65536 >> /etc/subuid ; \
    echo spaceuser:100000:65536 >> /etc/subgid ; \
    echo spacegroup:100000:65536 >> /etc/subgid ; \
    # Debian ships a minimal config.toml with neither a [grpc] nor a snapshotter section, so the \
    # full default is generated before being patched the way the production image patches Alpine's. \
    containerd config default > /etc/containerd/config.toml ; \
    sed -i.copy '/^\[grpc]/,/^\[/{s/^  gid[[:space:]]*=.*/  gid = 1000/}' /etc/containerd/config.toml ; \
    sed -i.copy '/^\[grpc]/,/^\[/{s/^  uid[[:space:]]*=.*/  uid = 1000/}' /etc/containerd/config.toml ; \
    # Snapshotter: `overlayfs`, the built-in one. It only works because the compose file puts\
    # /var/lib/containerd on a named volume, i.e. a real filesystem: left on the overlay2 filesystem\
    # Docker gives this container it cannot stack an overlay on an overlay and every run dies with\
    # `failed to mount rootfs component: invalid argument`. The alternatives were measured and are\
    # worse: `fuse-overlayfs` cannot even extract a layer there\
    # (`setxattr ... user.overlay.impure: operation not permitted`), and `native` copies the whole\
    # rootfs on each container creation — 11s to run `composer --version` against 1s here.\
    sed -i.copy '/^\[plugins]/,/^\[/{s/^      snapshotter[[:space:]]*=.*/      snapshotter = "overlayfs"/}' /etc/containerd/config.toml ; \
    containerd config dump > /dev/null ; \
    # That key only configures the CRI plugin; nerdctl talks to containerd directly and reads this.\
    install -d -m 0755 /etc/nerdctl ; \
    printf '%s\n' 'snapshotter = "overlayfs"' > /etc/nerdctl/nerdctl.toml ; \
    ansible-playbook --version ; \
    command -v ssh ; \
    command -v ssh-keyscan ; \
    command -v sftp ; \
    python3 -c 'import locale, sys; sys.exit(0 if locale.getpreferredencoding() == "UTF-8" else 1)'

ADD 99-space.ini /usr/local/etc/php/conf.d/

COPY --chown=root:root builder-init.sh /builder-init.sh
COPY --chown=root:root space-run.sh /usr/local/bin/space-run

RUN set -eux ; \
    chmod +x /builder-init.sh ; \
    chmod +x /usr/local/bin/space-run ; \
    command -v space-run

ENV HOME=/home/spaceuser \
    XDG_RUNTIME_DIR=/tmp/run-user-1000 \
    CONTAINERS_STORAGE_CONF=/home/spaceuser/.config/containers/storage.conf

ENTRYPOINT ["/builder-init.sh"]

WORKDIR /var/www/space

USER spaceuser:spacegroup
