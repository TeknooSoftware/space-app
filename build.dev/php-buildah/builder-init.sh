#!/bin/bash

# Entrypoint of the development `cli_execute` worker, the local counterpart of the production
# `php-builder` image's own `builder-init.sh`. It prepares the rootless OCI runtime the job
# execution needs (containerd socket, registry credentials, hook images), then hands over to the
# command the compose file provides, so `command:` stays the single source of truth and
# `docker compose run cli_execute bash` keeps working.

set -e

install -d -m 0700 \
    "$XDG_RUNTIME_DIR" \
    "$XDG_RUNTIME_DIR/containers" \
    "$XDG_RUNTIME_DIR/containers/storage"

sudo /usr/bin/containerd &

# Production starts containerd and immediately logs in; here the worker must keep consuming the
# queue even when the nested runtime is unavailable, so the socket is waited for and its absence
# only downgrades the features that need it.
containerdReady=0
for _ in $(seq 1 30); do
    if [ -S /run/containerd/containerd.sock ]; then
        containerdReady=1
        break
    fi
    sleep 1
done

if [ "$containerdReady" = "0" ]; then
    echo "space: containerd socket not available after 30s, nerdctl commands will fail" >&2
fi

registryReady=0
if [ -n "${SPACE_OCI_GLOBAL_REGISTRY_URL}" ] \
    && [ -n "${SPACE_OCI_GLOBAL_REGISTRY_USERNAME}" ] \
    && [ -n "${SPACE_OCI_GLOBAL_REGISTRY_PWD}" ]; then
    registryReady=1
else
    echo "space: SPACE_OCI_GLOBAL_REGISTRY_URL/_USERNAME/_PWD not set, skipping registry login"
fi

if [ "$registryReady" = "1" ]; then
    buildah login \
        --username="${SPACE_OCI_GLOBAL_REGISTRY_USERNAME}" \
        --password="${SPACE_OCI_GLOBAL_REGISTRY_PWD}" \
        "${SPACE_OCI_GLOBAL_REGISTRY_URL}" \
        || echo "space: buildah login failed" >&2

    if [ "$containerdReady" = "1" ]; then
        sudo nerdctl login \
            --username="${SPACE_OCI_GLOBAL_REGISTRY_USERNAME}" \
            --password="${SPACE_OCI_GLOBAL_REGISTRY_PWD}" \
            "${SPACE_OCI_GLOBAL_REGISTRY_URL}" \
            || echo "space: nerdctl login failed" >&2
    fi
fi

# The hook images must be local before a job runs one: `nerdctl run` has no quiet mode, so a missing
# image is pulled inside the hook's own timeout and its progress frames end up in the job history.
# SPACE_BUILDER_HOOKS lists them as `name:tag` pairs, separated by spaces or commas, each resolved
# against ${SPACE_OCI_GLOBAL_REGISTRY_URL}/space/hook-<name>:<tag>. An empty value pulls nothing.
# It runs in the background: pulling the collection takes minutes, and the worker must never wait for
# it. Until it completes a hook still pulls its own image, which is only slow and noisy, whereas a
# blocking pre-pull leaves the `execute_job` queue unconsumed for as long as it lasts.
if [ -z "${SPACE_BUILDER_HOOKS}" ]; then
    echo "space: SPACE_BUILDER_HOOKS is empty, no hook image pre-pulled"
elif [ "$registryReady" = "1" ] && [ "$containerdReady" = "1" ]; then
    (
        # Deliberately unquoted: the value is a list and must be split on its separators.
        for i in $(echo "${SPACE_BUILDER_HOOKS}" | tr ',' ' ')
        do
            sudo nerdctl pull --quiet "${SPACE_OCI_GLOBAL_REGISTRY_URL}/space/hook-${i}" \
                > /dev/null \
                || echo "space: unable to pull hook-${i}" >&2
        done
        echo "space: hook images ready"
    ) &
fi

exec docker-php-entrypoint "$@"
