#!/bin/sh

set -eu

if [ "$#" -ge 1 ] && [ "${1}" = "ext" ]; then
  EXTENSION=$(echo "${2}" | awk '{print toupper(substr($0,1,1)) substr($0,2)}')

  shift 2
  if [ -x "extensions/${EXTENSION}/space.sh" ]; then
    SCRIPT="extensions/${EXTENSION}/space.sh"
    "$SCRIPT" "${@}"

    exit 0
  fi

  if [ -e "extensions/${EXTENSION}/Makefile" ]; then
    make --no-print-directory -C "extensions/${EXTENSION}" "${@}"
    exit 0
  fi

  echo "Extension ${EXTENSION} has no Makefile or space.sh endpoint"

  exit 1
else
  make --no-print-directory -C . "$@"
fi
