#!/bin/bash
set -e
set -o pipefail

if [ -z "$FILENAME" ]; then
  echo "Error: FILENAME variable is not set."
  exit 1
fi

if [ ! -f "$FILENAME" ]; then
  echo "Error: File $FILENAME does not exist."
  exit 1
fi

if ! docker compose version &> /dev/null && ! docker-compose version &> /dev/null; then
  echo "Error: docker compose or docker-compose is not installed or not in PATH."
  exit 1
fi

# Detect which command to use
if docker compose version &> /dev/null; then
  DOCKER_CMD="docker compose"
else
  DOCKER_CMD="docker-compose"
fi

CONTAINER_ID=$($DOCKER_CMD ps -q db)

if [ -z "$CONTAINER_ID" ]; then
  echo "Error: No running container found for 'db' service. Is docker-compose up running?"
  exit 1
fi

echo "Starting restore..."
docker cp "$FILENAME" "${CONTAINER_ID}:/tmp/restore.gz"
$DOCKER_CMD exec -T db mongorestore --username admin_space --password space_pwd --authenticationDatabase admin --drop --archive=/tmp/restore.gz --gzip
docker exec "$CONTAINER_ID" rm /tmp/restore.gz

echo "Database restored successfully from $FILENAME"