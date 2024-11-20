#!/bin/sh

set -eu

if [ "${1}" = "ext" ]; then
  EXTENSION=$(echo "${2}" | awk '{print toupper(substr($0,1,1)) substr($0,2)}')

  shift 2
  if [ -x "appliance/extensions/${EXTENSION}/space.sh" ]; then
    SCRIPT="appliance/extensions/${EXTENSION}/space.sh"
    "$SCRIPT" "${@}"

    exit 0
  fi

  if [ -e "appliance/extensions/${EXTENSION}/Makefile" ]; then
    make --no-print-directory -C "appliance/extensions/${EXTENSION}" "${@}"
    exit 0
  fi

  echo "Extension ${EXTENSION} has no Makefile or space.sh endpoint"

  exit 1
else
  make --no-print-directory -C appliance "$@"
fi
