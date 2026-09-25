#!/bin/sh

set -e

if [ $# -lt 1 ]; then
    echo "space-run Need some arguments"
    sudo nerdctl run --help

    exit 1
fi

sudo nerdctl run "$@"
