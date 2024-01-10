#!/bin/sh

set -eu

PHP="/usr/bin/env php"
APP_ENV="prod"

if [ "$#" = "3" ]; then
  PHP="${1} ${2}"
  APP_ENV="${3}"
else
  if [ "$#" = "2" ]; then
    PHP="${1}"
    APP_ENV="${2}"
  fi
fi

ENV_LOCAL_FILE='.env.local'
DOCKER_COMPOSE_OVERRIDE_FILE='../docker-compose.override.yml'

###########
# Functions

readForYesOrNo() {
  returnVal=""

  while [ "$returnVal" != "y" ] && [ "$returnVal" != "n" ]; do
    read -r -p "$1 : " returnVal
  done

  echo "$returnVal"
}

readAMandatoryResponse() {
  returnVal=""

  while [ -z "$returnVal" ]; do
    read -r -p "$1 : " returnVal
  done

  echo "$returnVal"
}

updateSecret() {
  echo "Set $1 into Symfony Secret"
  echo -n "$2" | APP_ENV="$APP_ENV" ${PHP} bin/console secrets:set $1 -
}

updateFile() {
  echo "Set $2 in $1"
  pattern=" "
  case "$3" in
    *\ * ) sed -i "s~^\([- ]*\)$2=.*$~\\1$2=\"$3\"~g" "$1" ;;
    * ) sed -i "s~^\([- ]*\)$2=.*$~\\1$2=$3~g" "$1" ;;
  esac
}

########
#Prompts

useSfSeret=$(readForYesOrNo "Use Symfony Secrets [y/n]")
useDockerCompose=$(readForYesOrNo "Configure to use with a local Docker Compose [y/n]")

if [ "$useDockerCompose" = "y" ]; then
  mongoDbDSN="mongodb://space_user:space_pwd@db/space"
  amqpDSN="amqp://space:space_pwd@amqp:5672/"
  mercurePublishUrl="http://mercure:8181/"
  mercureSubscribeUrl="https://localhost/hub/"
else
  mongoDbDSN=$(readAMandatoryResponse "MongoDB DSN")
  amqpDSN=$(readAMandatoryResponse "AMQP DSN")
  mercurePublishUrl=$(readAMandatoryResponse "Mercure Server URL")
  mercureSubscribeUrl="$mercurePublishUrl"
fi

mercureJwtToken=$(readAMandatoryResponse "Mercure JWT Token")
kubernetesApi=$(readAMandatoryResponse "Kubernetes API Url")
kubernetesToken=$(readAMandatoryResponse "Kubernetes API Service Token to create new namespace and roles")
kubernetesDashboard=$(readAMandatoryResponse "Kubernetes Dashboard URL")
dockerGlobalRegistryApi=$(readAMandatoryResponse "Docker Registry API Url")
dockerGlobalRegistryUser=$(readAMandatoryResponse "Docker Registry User")
dockerGlobalRegistryPassword=$(readAMandatoryResponse "Docker Registry Password")
dockerPrivateRegistryUrl=$(readAMandatoryResponse "Docker Private Registry Url")

###############
# Configuration

if [ "$useSfSeret" = "y" ]; then
    echo "Generate Symfony Secrets"
    APP_ENV="$APP_ENV" ${PHP} bin/console secrets:generate-keys -r
fi

if [ "$useDockerCompose" = "y" ]; then
  if [ -f "$DOCKER_COMPOSE_OVERRIDE_FILE" ]; then
    echo "Backup existant $DOCKER_COMPOSE_OVERRIDE_FILE to $DOCKER_COMPOSE_OVERRIDE_FILE.bkp"
    cp "$DOCKER_COMPOSE_OVERRIDE_FILE" "$DOCKER_COMPOSE_OVERRIDE_FILE.bck"
  else
    echo "Creating new $DOCKER_COMPOSE_OVERRIDE_FILE file"
    cp "$DOCKER_COMPOSE_OVERRIDE_FILE.dist" "$DOCKER_COMPOSE_OVERRIDE_FILE"
  fi
fi

if [ -f "$ENV_LOCAL_FILE" ]; then
  echo "Backup of existing $ENV_LOCAL_FILE to $ENV_LOCAL_FILE.bkp"
  cp "$ENV_LOCAL_FILE" "$ENV_LOCAL_FILE.bck"
else
  echo "Creating new $ENV_LOCAL_FILE file"

  APP_REMEMBER_SECRET=$(cat /proc/sys/kernel/random/uuid | sed 's/[-]//g' | sha256sum | head -c 48; echo)
  APP_SECRET=$(cat /proc/sys/kernel/random/uuid | sed 's/[-]//g' | sha256sum | head -c 48; echo)
  SPACE_CODE_GENERATOR_SALT=$(cat /proc/sys/kernel/random/uuid | sed 's/[-]//g' | sha256sum | head -c 48; echo)
  TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE=$(cat /proc/sys/kernel/random/uuid | sed 's/[-]//g' | sha256sum | head -c 48; echo)
  SPACE_JWT_PASSPHRASE=$(cat /proc/sys/kernel/random/uuid | sed 's/[-]//g' | sha256sum | head -c 48; echo)

  if [ "$useSfSeret" = "y" ]; then
      cp .env.local.dist "$ENV_LOCAL_FILE"

      echo "Set random secrets: APP_REMEMBER_SECRET, APP_SECRET and SPACE_CODE_GENERATOR_SALT, TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE, SPACE_JWT_PASSPHRASE"
      updateSecret "APP_REMEMBER_SECRET" "$APP_REMEMBER_SECRET"
      updateSecret "APP_SECRET" "$APP_SECRET"
      updateSecret "SPACE_CODE_GENERATOR_SALT" "$SPACE_CODE_GENERATOR_SALT"
      updateSecret "TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE" "$TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE"
      updateSecret "SPACE_JWT_PASSPHRASE" "$SPACE_JWT_PASSPHRASE"
  else
      cp .env.local.unsecure.dist "$ENV_LOCAL_FILE"

      echo "Set random secrets in $ENV_LOCAL_FILE: APP_REMEMBER_SECRET, APP_SECRET and SPACE_CODE_GENERATOR_SALT"
      updateFile "$ENV_LOCAL_FILE" "APP_REMEMBER_SECRET" "$APP_REMEMBER_SECRET"
      updateFile "$ENV_LOCAL_FILE" "APP_SECRET" "$APP_SECRET"
      updateFile "$ENV_LOCAL_FILE" "SPACE_CODE_GENERATOR_SALT" "$SPACE_CODE_GENERATOR_SALT"
      updateFile "$ENV_LOCAL_FILE" "TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE" "$TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE"
      updateFile "$ENV_LOCAL_FILE" "SPACE_JWT_PASSPHRASE" "$SPACE_JWT_PASSPHRASE"
  fi

  if [ "$useDockerCompose" = "y" ]; then
      updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "APP_REMEMBER_SECRET" "$APP_REMEMBER_SECRET"
      updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "APP_SECRET" "$APP_SECRET"
      updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_CODE_GENERATOR_SALT" "$SPACE_CODE_GENERATOR_SALT"
      updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE" "$TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE"
      updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_JWT_PASSPHRASE" "$SPACE_JWT_PASSPHRASE"
  fi
fi

MESSENGER_EXECUTE_JOB_DSN="$amqpDSN%2f/execute_job"
MESSENGER_HISTORY_SENT_DSN="$amqpDSN%2f/history_sent"
MESSENGER_JOB_DONE_DSN="$amqpDSN%2f/job_done"
MESSENGER_NEW_JOB_DSN="$amqpDSN%2f/new_job"

if [ "$useSfSeret" = "y" ]; then
  updateSecret "MONGODB_SERVER" "$mongoDbDSN"
  updateSecret "MESSENGER_EXECUTE_JOB_DSN" "$MESSENGER_EXECUTE_JOB_DSN"
  updateSecret "MESSENGER_HISTORY_SENT_DSN" "$MESSENGER_HISTORY_SENT_DSN"
  updateSecret "MESSENGER_JOB_DONE_DSN" "$MESSENGER_JOB_DONE_DSN"
  updateSecret "MESSENGER_NEW_JOB_DSN" "$MESSENGER_NEW_JOB_DSN"
  updateSecret "SPACE_KUBERNETES_CREATE_TOKEN" "$kubernetesToken"
  updateSecret "SPACE_OCI_GLOBAL_REGISTRY_PWD" "$dockerGlobalRegistryPassword"
  updateSecret "MERCURE_JWT_TOKEN" "$mercureJwtToken"
else
  updateFile "$ENV_LOCAL_FILE" "MONGODB_SERVER" "$mongoDbDSN"
  updateFile "$ENV_LOCAL_FILE" "MESSENGER_EXECUTE_JOB_DSN" "$MESSENGER_EXECUTE_JOB_DSN"
  updateFile "$ENV_LOCAL_FILE" "MESSENGER_HISTORY_SENT_DSN" "$MESSENGER_HISTORY_SENT_DSN"
  updateFile "$ENV_LOCAL_FILE" "MESSENGER_JOB_DONE_DSN" "$MESSENGER_JOB_DONE_DSN"
  updateFile "$ENV_LOCAL_FILE" "MESSENGER_NEW_JOB_DSN" "$MESSENGER_NEW_JOB_DSN"
  updateFile "$ENV_LOCAL_FILE" "SPACE_KUBERNETES_CREATE_TOKEN" "$kubernetesToken"
  updateFile "$ENV_LOCAL_FILE" "SPACE_OCI_GLOBAL_REGISTRY_PWD" "$dockerGlobalRegistryPassword"
  updateFile "$ENV_LOCAL_FILE" "MERCURE_JWT_TOKEN" "$mercureJwtToken"
fi

if [ "$useDockerCompose" = "y" ]; then
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MONGODB_SERVER" "$mongoDbDSN"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MESSENGER_EXECUTE_JOB_DSN" "$MESSENGER_EXECUTE_JOB_DSN"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MESSENGER_HISTORY_SENT_DSN" "$MESSENGER_HISTORY_SENT_DSN"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MESSENGER_JOB_DONE_DSN" "$MESSENGER_JOB_DONE_DSN"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MESSENGER_NEW_JOB_DSN" "$MESSENGER_NEW_JOB_DSN"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_KUBERNETES_CREATE_TOKEN" "$kubernetesToken"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_OCI_GLOBAL_REGISTRY_PWD" "$dockerGlobalRegistryPassword"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MERCURE_JWT_TOKEN" "$mercureJwtToken"
fi

updateFile "$ENV_LOCAL_FILE" "APP_ENV" "$APP_ENV"
updateFile "$ENV_LOCAL_FILE" "SPACE_KUBERNETES_MASTER" "$kubernetesApi"
updateFile "$ENV_LOCAL_FILE" "SPACE_KUBERNETES_DASHBOARD" "$kubernetesDashboard"
updateFile "$ENV_LOCAL_FILE" "SPACE_OCI_GLOBAL_REGISTRY_URL" "$dockerGlobalRegistryApi"
updateFile "$ENV_LOCAL_FILE" "SPACE_OCI_GLOBAL_REGISTRY_USERNAME" "$dockerGlobalRegistryUser"
updateFile "$ENV_LOCAL_FILE" "SPACE_OCI_REGISTRY_URL" "$dockerPrivateRegistryUrl"
updateFile "$ENV_LOCAL_FILE" "MERCURE_PUBLISH_URL" "$mercurePublishUrl"
updateFile "$ENV_LOCAL_FILE" "MERCURE_SUBSCRIBER_URL" "$mercureSubscribeUrl"
updateFile "$ENV_LOCAL_FILE" "TEKNOO_PAAS_SECURITY_ALGORITHM" "rsa"
updateFile "$ENV_LOCAL_FILE" "TEKNOO_PAAS_SECURITY_PRIVATE_KEY" "var/keys/private.pem"
updateFile "$ENV_LOCAL_FILE" "TEKNOO_PAAS_SECURITY_PUBLIC_KEY" "var/keys/public.pem"

if [ "$useDockerCompose" = "y" ]; then
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "APP_ENV" "$APP_ENV"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_KUBERNETES_MASTER" "$kubernetesApi"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_KUBERNETES_DASHBOARD" "$kubernetesDashboard"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_OCI_GLOBAL_REGISTRY_URL" "$dockerGlobalRegistryApi"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_OCI_GLOBAL_REGISTRY_USERNAME" "$dockerGlobalRegistryUser"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_OCI_REGISTRY_URL" "$dockerPrivateRegistryUrl"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MERCURE_PUBLISH_URL" "$mercurePublishUrl"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MERCURE_SUBSCRIBER_URL" "$mercureSubscribeUrl"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "TEKNOO_PAAS_SECURITY_ALGORITHM" "rsa"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "TEKNOO_PAAS_SECURITY_PRIVATE_KEY" "var/keys/private.pem"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "TEKNOO_PAAS_SECURITY_PUBLIC_KEY" "var/keys/public.pem"
fi

echo "Space is configured"
