#!/bin/bash
set -e
set -o pipefail

if [ -z "$FILENAME" ]; then
  echo "Error: FILENAME variable is not set."
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

echo "Starting backup..."
$DOCKER_CMD exec -T db mongodump --username admin_space --password space_pwd --authenticationDatabase admin --archive=/tmp/backup.gz --gzip
docker cp "${CONTAINER_ID}:/tmp/backup.gz" "$FILENAME"
docker exec "$CONTAINER_ID" rm /tmp/backup.gz

echo "Database backed up successfully to $FILENAME"
