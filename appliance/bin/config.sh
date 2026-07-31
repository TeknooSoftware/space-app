#!/bin/sh

set -eu

PHP="/usr/bin/env php"
APP_ENV="prod"

# Dry-run mode: run every prompt as usual but touch nothing on disk. Enabled with a leading
# --dry-run/-n flag or the DRY_RUN=1 environment variable (so `DRY_RUN=1 make config` works too).
DRY_RUN="${DRY_RUN:-0}"
if [ "$#" -ge 1 ] && { [ "${1}" = "--dry-run" ] || [ "${1}" = "-n" ]; }; then
  DRY_RUN="1"
  shift
fi

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
DOCKER_COMPOSE_FPM_OVERRIDE_FILE='../compose.fpm.override.yml'
DOCKER_COMPOSE_FPM_FILE='../compose.fpm.yml'
DOCKER_COMPOSE_FRANKENPHP_OVERRIDE_FILE='../compose.frankenphp.override.yml'
DOCKER_COMPOSE_FRANKENPHP_FILE='../compose.frankenphp.yml'
DOCKER_COMPOSE_OVERRIDE_FILE='../compose.override.yml'
DOCKER_COMPOSE_FILE='../compose.yml'
SESSION_FILE='config/packages/framework.session.backend.yaml'
FILE_SESSION_FILE='config/packages/framework.session.backend.file.yaml.dist'
REDIS_SESSION_FILE='config/packages/framework.session.backend.redis.yaml.dist'
RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m'

# In dry-run mode redirect every write target into a throwaway sandbox and remember the real
# destinations for the final display. The source/template files (*.dist, compose.*.yml templates)
# stay real: they are only ever read. The sandbox is SEEDED with a copy of each real target that
# already exists, so the "backup existing vs. create new" branches behave exactly as a real run
# (a dev who already has compose.override.yml/.env.local previews edits on top of the real files
# and never touches a template). No real file is ever written.
if [ "$DRY_RUN" = "1" ]; then
  echo "*** ${GREEN}DRY RUN${NC} — no file will be modified; the resulting files are printed at the end ***"
  echo ""

  DRY_RUN_DIR=$(mktemp -d)
  trap 'rm -rf "$DRY_RUN_DIR"' EXIT

  REAL_ENV_LOCAL_FILE="$ENV_LOCAL_FILE"
  REAL_DOCKER_COMPOSE_OVERRIDE_FILE="$DOCKER_COMPOSE_OVERRIDE_FILE"
  REAL_DOCKER_COMPOSE_FILE="$DOCKER_COMPOSE_FILE"
  REAL_SESSION_FILE="$SESSION_FILE"

  ENV_LOCAL_FILE="$DRY_RUN_DIR/.env.local"
  DOCKER_COMPOSE_OVERRIDE_FILE="$DRY_RUN_DIR/compose.override.yml"
  DOCKER_COMPOSE_FILE="$DRY_RUN_DIR/compose.yml"
  SESSION_FILE="$DRY_RUN_DIR/framework.session.backend.yaml"

  if [ -f "$REAL_ENV_LOCAL_FILE" ]; then cp "$REAL_ENV_LOCAL_FILE" "$ENV_LOCAL_FILE"; fi
  if [ -f "$REAL_DOCKER_COMPOSE_OVERRIDE_FILE" ]; then cp "$REAL_DOCKER_COMPOSE_OVERRIDE_FILE" "$DOCKER_COMPOSE_OVERRIDE_FILE"; fi
  if [ -f "$REAL_DOCKER_COMPOSE_FILE" ]; then cp "$REAL_DOCKER_COMPOSE_FILE" "$DOCKER_COMPOSE_FILE"; fi
fi

###########
# Functions

readForYesOrNo() {
  returnVal=""

  while [ "$returnVal" != "y" ] && [ "$returnVal" != "n" ]; do
    read -r -p "$1 : " returnVal
  done

  echo "$returnVal"
}

readForYesOrNoToBool() {
  returnVal=""

  while [ "$returnVal" != "y" ] && [ "$returnVal" != "n" ]; do
    read -r -p "$1 : " returnVal
  done

  if [ "$returnVal" = "y" ]; then
    echo "1"
  else
    echo "0"
  fi
}

readAMandatoryResponse() {
  returnVal=""

  while [ -z "$returnVal" ]; do
    if [ "$#" = "2" ]; then
      read -r -p "$1 (default : ${2}) : " returnVal
    else
      read -r -p "$1 : " returnVal
    fi

    if [ -z "$returnVal" ] && [ "$#" = "2" ]; then
      returnVal="${2}"
    fi
  done

  echo "$returnVal"
}

readAMandatoryFileResponse() {
  returnVal=""

  while [ -z "$returnVal" ] || [ ! -r "$returnVal" ] || [ ! -f "$returnVal" ]; do
    if [ "$#" = "2" ]; then
      read -r -p "$1 (default : ${2}) : " returnVal
    else
      read -r -p "$1 : " returnVal
    fi

    if [ -z "$returnVal" ] && [ "$#" = "2" ]; then
      returnVal="${2}"
    fi
  done

  echo "$returnVal"
}

updateSecret() {
  if [ "$DRY_RUN" = "1" ]; then
    echo "[dry-run] would store Symfony secret: $1"
    return
  fi
  echo "Set $1 into Symfony Secret"
  echo -n "$2" | APP_ENV="$APP_ENV" ${PHP} bin/console secrets:set $1 -
}

# Copy a source file into place. In dry-run, when the final source does not exist yet (fresh
# checkout: only the *.dist examples are shipped), fall back to "<src>.dist" so the preview is built
# from the example. A real run always uses the final file (and fails loudly if it is missing).
cpTemplate() {
  if [ -f "$1" ]; then
    cp "$1" "$2"
  elif [ "$DRY_RUN" = "1" ] && [ -f "$1.dist" ]; then
    echo "[dry-run] $1 absent — using example $1.dist for preview"
    cp "$1.dist" "$2"
  elif [ "$DRY_RUN" = "1" ]; then
    echo "[dry-run] $1 (and $1.dist) absent — starting $2 empty for preview"
    : >"$2"
  else
    cp "$1" "$2"
  fi
}

updateFile() {
  [ "$DRY_RUN" = "1" ] || echo "Set $2 in $1"
  case "$3" in
    *\ * ) sed -i "s~^\([- ]*\)$2=.*$~\\1$2=\"$3\"~g" "$1" ;;
    * ) sed -i "s~^\([- ]*\)$2=.*$~\\1$2=$3~g" "$1" ;;
  esac
}

# Upsert a KEY=value pair into a flat .env style file: replace it when an uncommented line already
# exists, otherwise append a fresh line. Unlike updateFile() this also handles keys that are only
# present commented out (e.g. the optional SPACE_DC_* defaults in .env.local.dist) or absent.
setEnvVar() {
  if grep -qE "^[- ]*$2=" "$1"; then
    updateFile "$1" "$2" "$3"
  else
    [ "$DRY_RUN" = "1" ] || echo "Append $2 in $1"
    case "$3" in
      *\ * ) printf '%s="%s"\n' "$2" "$3" >>"$1" ;;
      * ) printf '%s=%s\n' "$2" "$3" >>"$1" ;;
    esac
  fi
}

# Upsert a KEY=value pair into the Docker Compose override YAML: replace it when an uncommented
# "- KEY=value" line already exists, otherwise insert it right after the FIRST "environment:" block
# (the application service). Keeps the file valid YAML instead of appending a bare line at EOF.
setComposeEnv() {
  if grep -qE "^[- ]*$2=" "$1"; then
    updateFile "$1" "$2" "$3"
  else
    [ "$DRY_RUN" = "1" ] || echo "Insert $2 in $1"
    awk -v k="$2" -v v="$3" '
      !inserted && /^[[:space:]]*environment:[[:space:]]*$/ {
        print
        print "            - " k "=" v
        inserted = 1
        next
      }
      { print }
    ' "$1" >"$1.tmp" && mv "$1.tmp" "$1"
  fi
}

########
#Prompts

useSfSeret=$(readForYesOrNo "Use Symfony Secrets [y/n]")
useDockerCompose=$(readForYesOrNo "Configure to use with a local Docker Compose [y/n]")

if [ "$useDockerCompose" = "y" ]; then
  useDockerComposeFranken=$(readForYesOrNo "Prefere use FrankenPHP instead PHP FPM [y/n]")

  mongoDbDSN="mongodb://space_user:space_pwd@db/teknoo_space"
  amqpDSN="amqp://space:space_pwd@amqp:5672/"
  mercurePublishUrl="https://localhost/"
  mercureSubscribeUrl="https://localhost/"
else
  mongoDbDSN=$(readAMandatoryResponse "MongoDB DSN")
  amqpDSN=$(readAMandatoryResponse "AMQP DSN")
  mercurePublishUrl=$(readAMandatoryResponse "Mercure Server URL")
  mercureSubscribeUrl="$mercurePublishUrl"
fi

mercureJwtToken=$(readAMandatoryResponse "Mercure JWT Token")
useCatalog=$(readForYesOrNoToBool "Use Cluster Catalog ? [y/n]")
if [ "$useCatalog" = "0" ]; then
  kubernetesApi=$(readAMandatoryResponse "Kubernetes API Url")
  kubernetesCAFile=$(readAMandatoryFileResponse "Kubernetes CA file")
  kubernetesToken=$(readAMandatoryResponse "Kubernetes API Service Token to create new namespace and roles")
  kubernetesClusterName=$(readAMandatoryResponse "Kubernetes cluster name")
  kubernetesHnc=$(readForYesOrNoToBool "Kubernetes cluster use hierarchical namespaces [y/n]")
  kubernetesDashboard=$(readAMandatoryResponse "Kubernetes Dashboard URL")
else
  clusterCatalogFile=$(readAMandatoryFileResponse "Cluster Catalog file")
fi
kubernetesVersionLevel=$(readAMandatoryResponse "Kubernetes version level [1.30/1.32/1.35/1.36]" "1.30")
kubernetesVerifySsl=$(readForYesOrNoToBool "Kubernetes: verify the API TLS certificate (say n for self-signed dev clusters) [y/n]")
kubernetesClientTimeout=$(readAMandatoryResponse "Kubernetes: API client timeout (seconds)" "3")
mongoDbName=$(readAMandatoryResponse "MongoDB database name" "teknoo_space")
imgBuilderPlatforms=$(readAMandatoryResponse "Image builder platforms (e.g. linux/amd64, linux/arm64)" "linux/amd64")
dockerGlobalRegistryApi=$(readAMandatoryResponse "Docker Registry API Url")
dockerGlobalRegistryUser=$(readAMandatoryResponse "Docker Registry User")
dockerGlobalRegistryPassword=$(readAMandatoryResponse "Docker Registry Password")
dockerPrivateRegistryUrl=$(readAMandatoryResponse "Docker Private Registry Url")

# Optional second deployment target: a remote Docker host driven over Ansible (spec .plans/ANSIBLE_DOCKER.md
# §7). All SPACE_DC_* vars are optional and fall back to the library defaults shown below; the per-cluster
# SSH credentials themselves live in the cluster catalog JSON / AccountCluster form, not here.
dcAnsibleBinary=""
dcTimeout=""
dcDeployRoot=""
dcNetworkDriver=""
dcTraefikContainer=""
dcTraefikDynamicDir=""
dcTraefikCertsDir=""
dcTraefikCertResolver=""
dcTraefikEntrypointWeb=""
dcTraefikEntrypointWebSecure=""
dcTraefikEntrypointTcp=""
dcTraefikEntrypointUdp=""
dcHttpsBackendInsecureSkipVerify=""
dcRegistryImage=""
dcRegistryNetwork=""
dcRegistryPort=""
dcRegistryTls=""
useDockerComposeTarget=$(readForYesOrNo "Configure a Docker Compose deployment target (SPACE_DC_*) [y/n]")
if [ "$useDockerComposeTarget" = "y" ]; then
  dcAnsibleBinary=$(readAMandatoryResponse "Docker Compose: ansible-playbook binary" "ansible-playbook")
  dcTimeout=$(readAMandatoryResponse "Docker Compose: Ansible run timeout (seconds)" "300")
  dcDeployRoot=$(readAMandatoryResponse "Docker Compose: deploy root on the host" "/opt/paas")
  dcNetworkDriver=$(readAMandatoryResponse "Docker Compose: network driver" "bridge")
  dcTraefikContainer=$(readAMandatoryResponse "Docker Compose: Traefik container name" "traefik")
  dcTraefikDynamicDir=$(readAMandatoryResponse "Docker Compose: Traefik dynamic config dir" "/etc/traefik/dynamic")
  dcTraefikCertsDir=$(readAMandatoryResponse "Docker Compose: Traefik certificates dir" "/etc/traefik/certs")
  read -r -p "Docker Compose: Traefik default certresolver (leave empty to keep library default) : " dcTraefikCertResolver
  dcTraefikEntrypointWeb=$(readAMandatoryResponse "Docker Compose: Traefik web entrypoint" "web")
  dcTraefikEntrypointWebSecure=$(readAMandatoryResponse "Docker Compose: Traefik websecure entrypoint" "websecure")
  dcTraefikEntrypointTcp=$(readAMandatoryResponse "Docker Compose: Traefik tcp entrypoint" "tcp")
  dcTraefikEntrypointUdp=$(readAMandatoryResponse "Docker Compose: Traefik udp entrypoint" "udp")
  dcHttpsBackendInsecureSkipVerify=$(readAMandatoryResponse "Docker Compose: skip TLS verify on HTTPS backends [true/false]" "false")
  dcRegistryImage=$(readAMandatoryResponse "Docker Compose: per-account registry image" "registry:2")
  dcRegistryNetwork=$(readAMandatoryResponse "Docker Compose: per-account registry network" "space-registry")
  dcRegistryPort=$(readAMandatoryResponse "Docker Compose: per-account registry port" "5000")
  dcRegistryTls=$(readAMandatoryResponse "Docker Compose: per-account registry TLS [true/false]" "false")
fi
mFAProvider=$(readAMandatoryResponse "2FA Provider [google_authenticator/generic]" "google_authenticator")
mailerDSN=$(readAMandatoryResponse "Mailer DSN" "null://null")
mailerSenderAddress=$(readAMandatoryResponse "Mailer sender adress")
jwtMaxAgeDelay=$(readAMandatoryResponse "JWT: Max days to live")
oauthEnabled=$(readForYesOrNoToBool "OAuth Enabled [y/n]")
redisEnabled=$(readForYesOrNoToBool "Redis Enabled [y/n]")
enableExtensions=$(readForYesOrNoToBool "Enable extension [y/n]")

if [ "$enableExtensions" = "1" ]; then
  extensionClassLoader=$(readAMandatoryResponse "Extension loader [file/composer]" "file")

  if [ "$extensionClassLoader" = "file" ]; then
    extensionFile=$(readAMandatoryResponse "Extension file name" "extensions/enabled.json")
  fi
fi

oauthServerType=""
oauthServerUrl=""
oauthClientId=""
oauthClientSecret=""
redisHost=""
redisPort=""

if [ "$oauthEnabled" = "1" ]; then
  oauthServerType=$(readAMandatoryResponse "OAuth Server Type [digital_ocean/github/gitlab/google/jira/microsoft/generic]")
  if [ "$oauthServerType" = "gitlab" ]; then
    oauthServerUrl=$(readAMandatoryResponse "OAuth Gitlab server")
  fi
  oauthClientId=$(readAMandatoryResponse "OAuth Client Id")
  oauthClientSecret=$(readAMandatoryResponse "OAuth Client Secret")
fi

if [ "$redisEnabled" = "1" ]; then
  redisHost=$(readAMandatoryResponse "Redis Host")
  redisPort=$(readAMandatoryResponse "Redis Port")

  cp "$REDIS_SESSION_FILE" "$SESSION_FILE"
else
  cp "$FILE_SESSION_FILE" "$SESSION_FILE"
fi

###############
# Configuration

if [ "$useSfSeret" = "y" ]; then
  if [ "$DRY_RUN" = "1" ]; then
    echo "[dry-run] would run: bin/console secrets:generate-keys -r"
  else
    echo "Generate Symfony Secrets"
    APP_ENV="$APP_ENV" ${PHP} bin/console secrets:generate-keys -r
  fi
fi

if [ "$useDockerCompose" = "y" ]; then
  if [ -f "$DOCKER_COMPOSE_FILE" ]; then
    [ "$DRY_RUN" = "1" ] || echo "Backup existant $DOCKER_COMPOSE_FILE to $DOCKER_COMPOSE_FILE.bkp"
    cp "$DOCKER_COMPOSE_FILE" "$DOCKER_COMPOSE_FILE.bck"
  fi

  if [ -f "$DOCKER_COMPOSE_OVERRIDE_FILE" ]; then
    [ "$DRY_RUN" = "1" ] || echo "Backup existant $DOCKER_COMPOSE_OVERRIDE_FILE to $DOCKER_COMPOSE_OVERRIDE_FILE.bkp"
    cp "$DOCKER_COMPOSE_OVERRIDE_FILE" "$DOCKER_COMPOSE_OVERRIDE_FILE.bck"
  else
    [ "$DRY_RUN" = "1" ] || echo "Creating new $DOCKER_COMPOSE_OVERRIDE_FILE file"

    if [ "$useDockerComposeFranken" = "y" ]; then
      cpTemplate "$DOCKER_COMPOSE_FRANKENPHP_OVERRIDE_FILE" "$DOCKER_COMPOSE_OVERRIDE_FILE"
    else
      cpTemplate "$DOCKER_COMPOSE_FPM_OVERRIDE_FILE" "$DOCKER_COMPOSE_OVERRIDE_FILE"
    fi
  fi

  if [ "$useDockerComposeFranken" = "y" ]; then
    cpTemplate "$DOCKER_COMPOSE_FRANKENPHP_FILE" "$DOCKER_COMPOSE_FILE"
  else
    cpTemplate "$DOCKER_COMPOSE_FPM_FILE" "$DOCKER_COMPOSE_FILE"
  fi
fi

if [ -f "$ENV_LOCAL_FILE" ]; then
  [ "$DRY_RUN" = "1" ] || echo "Backup of existing $ENV_LOCAL_FILE to $ENV_LOCAL_FILE.bkp"
  cp "$ENV_LOCAL_FILE" "$ENV_LOCAL_FILE.bck"
else
  [ "$DRY_RUN" = "1" ] || echo "Creating new $ENV_LOCAL_FILE file"

  APP_REMEMBER_SECRET=$(cat /proc/sys/kernel/random/uuid | sed 's/[-]//g' | sha256sum | head -c 48; echo)
  APP_SECRET=$(cat /proc/sys/kernel/random/uuid | sed 's/[-]//g' | sha256sum | head -c 48; echo)
  SPACE_CODE_GENERATOR_SALT=$(cat /proc/sys/kernel/random/uuid | sed 's/[-]//g' | sha256sum | head -c 48; echo)
  TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE=$(cat /proc/sys/kernel/random/uuid | sed 's/[-]//g' | sha256sum | head -c 48; echo)
  SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY_PASSPHRASE=$(cat /proc/sys/kernel/random/uuid | sed 's/[-]//g' | sha256sum | head -c 48; echo)
  SPACE_JWT_PASSPHRASE=$(cat /proc/sys/kernel/random/uuid | sed 's/[-]//g' | sha256sum | head -c 48; echo)

  if [ ! -f var/keys/messages/private.pem ]; then
    if [ "$DRY_RUN" = "1" ]; then
      echo "[dry-run] would generate RSA keypair: var/keys/messages/{private,public}.pem"
    else
      openssl genpkey -algorithm RSA -aes256 -out var/keys/messages/private.pem -pass pass:"$TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE" -pkeyopt rsa_keygen_bits:2048
      openssl rsa -in var/keys/messages/private.pem -pubout -out var/keys/messages/public.pem -passin pass:"$TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE"
    fi
  fi

  if [ ! -f var/keys/variables/private.pem ]; then
    if [ "$DRY_RUN" = "1" ]; then
      echo "[dry-run] would generate RSA keypair: var/keys/variables/{private,public}.pem"
    else
      openssl genpkey -algorithm RSA -aes256 -out var/keys/variables/private.pem -pass pass:"$SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY_PASSPHRASE" -pkeyopt rsa_keygen_bits:2048
      openssl rsa -in var/keys/variables/private.pem -pubout -out var/keys/variables/public.pem -passin pass:"$SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY_PASSPHRASE"
    fi
  fi

  if [ "$useSfSeret" = "y" ]; then
      cp .env.local.dist "$ENV_LOCAL_FILE"

      echo "Set random secrets: APP_REMEMBER_SECRET, APP_SECRET and SPACE_CODE_GENERATOR_SALT, TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE, SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY_PASSPHRASE, SPACE_JWT_PASSPHRASE"
      updateSecret "APP_REMEMBER_SECRET" "$APP_REMEMBER_SECRET"
      updateSecret "APP_SECRET" "$APP_SECRET"
      updateSecret "SPACE_CODE_GENERATOR_SALT" "$SPACE_CODE_GENERATOR_SALT"
      updateSecret "SPACE_JWT_PASSPHRASE" "$SPACE_JWT_PASSPHRASE"
      updateSecret "TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE" "$TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE"
      updateSecret "SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY_PASSPHRASE" "$SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY_PASSPHRASE"
  else
      cp .env.local.unsecure.dist "$ENV_LOCAL_FILE"

      echo "Set random secrets in $ENV_LOCAL_FILE: APP_REMEMBER_SECRET, APP_SECRET and SPACE_CODE_GENERATOR_SALT"
      updateFile "$ENV_LOCAL_FILE" "APP_REMEMBER_SECRET" "$APP_REMEMBER_SECRET"
      updateFile "$ENV_LOCAL_FILE" "APP_SECRET" "$APP_SECRET"
      updateFile "$ENV_LOCAL_FILE" "SPACE_CODE_GENERATOR_SALT" "$SPACE_CODE_GENERATOR_SALT"
      updateFile "$ENV_LOCAL_FILE" "SPACE_JWT_PASSPHRASE" "$SPACE_JWT_PASSPHRASE"
      updateFile "$ENV_LOCAL_FILE" "TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE" "$TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE"
      updateFile "$ENV_LOCAL_FILE" "SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY_PASSPHRASE" "$SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY_PASSPHRASE"
  fi

  if [ "$useDockerCompose" = "y" ]; then
      updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "APP_REMEMBER_SECRET" "$APP_REMEMBER_SECRET"
      updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "APP_SECRET" "$APP_SECRET"
      updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_CODE_GENERATOR_SALT" "$SPACE_CODE_GENERATOR_SALT"
      updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_JWT_PASSPHRASE" "$SPACE_JWT_PASSPHRASE"
      updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE" "$TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE"
      updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY_PASSPHRASE" "$SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY_PASSPHRASE"
  fi
fi

MESSENGER_EXECUTE_JOB_DSN="$amqpDSN%2f/execute_job"
MESSENGER_HISTORY_SENT_DSN="$amqpDSN%2f/history_sent"
MESSENGER_JOB_DONE_DSN="$amqpDSN%2f/job_done"
MESSENGER_NEW_JOB_DSN="$amqpDSN%2f/new_job"

if [ "$useSfSeret" = "y" ]; then
  updateSecret "MAILER_DSN" "$mailerDSN"
  updateSecret "MERCURE_JWT_TOKEN" "$mercureJwtToken"
  updateSecret "MESSENGER_EXECUTE_JOB_DSN" "$MESSENGER_EXECUTE_JOB_DSN"
  updateSecret "MESSENGER_HISTORY_SENT_DSN" "$MESSENGER_HISTORY_SENT_DSN"
  updateSecret "MESSENGER_JOB_DONE_DSN" "$MESSENGER_JOB_DONE_DSN"
  updateSecret "MESSENGER_NEW_JOB_DSN" "$MESSENGER_NEW_JOB_DSN"
  updateSecret "MONGODB_SERVER" "$mongoDbDSN"
  if [ "$oauthServerType" = "digital_ocean" ]; then
    updateSecret "OAUTH_DO_CLIENT_ID" "$oauthClientId"
    updateSecret "OAUTH_DO_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$oauthServerType" = "generic" ]; then
    updateSecret "OAUTH_CLIENT_ID" "$oauthClientId"
    updateSecret "OAUTH_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$oauthServerType" = "github" ]; then
    updateSecret "OAUTH_GH_CLIENT_ID" "$oauthClientId"
    updateSecret "OAUTH_GH_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$oauthServerType" = "gitlab" ]; then
    updateSecret "OAUTH_GITLAB_CLIENT_ID" "$oauthClientId"
    updateSecret "OAUTH_GITLAB_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$oauthServerType" = "google" ]; then
    updateSecret "OAUTH_GOOGLE_CLIENT_ID" "$oauthClientId"
    updateSecret "OAUTH_GOOGLE_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$oauthServerType" = "jira" ]; then
    updateSecret "OAUTH_JIRA_CLIENT_ID" "$oauthClientId"
    updateSecret "OAUTH_JIRA_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$oauthServerType" = "microsoft" ]; then
    updateSecret "OAUTH_MS_CLIENT_ID" "$oauthClientId"
    updateSecret "OAUTH_MS_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$useCatalog" = "0" ]; then
    updateSecret "SPACE_KUBERNETES_CA_VALUE" "$(cat "$kubernetesCAFile")"
    updateSecret "SPACE_KUBERNETES_CREATE_TOKEN" "$kubernetesToken"
  fi
  updateSecret "SPACE_OCI_GLOBAL_REGISTRY_PWD" "$dockerGlobalRegistryPassword"
else
  updateFile "$ENV_LOCAL_FILE" "MAILER_DSN" "$mailerDSN"
  updateFile "$ENV_LOCAL_FILE" "MERCURE_JWT_TOKEN" "$mercureJwtToken"
  updateFile "$ENV_LOCAL_FILE" "MESSENGER_EXECUTE_JOB_DSN" "$MESSENGER_EXECUTE_JOB_DSN"
  updateFile "$ENV_LOCAL_FILE" "MESSENGER_HISTORY_SENT_DSN" "$MESSENGER_HISTORY_SENT_DSN"
  updateFile "$ENV_LOCAL_FILE" "MESSENGER_JOB_DONE_DSN" "$MESSENGER_JOB_DONE_DSN"
  updateFile "$ENV_LOCAL_FILE" "MESSENGER_NEW_JOB_DSN" "$MESSENGER_NEW_JOB_DSN"
  updateFile "$ENV_LOCAL_FILE" "MONGODB_SERVER" "$mongoDbDSN"
  if [ "$oauthServerType" = "digital_ocean" ]; then
    updateFile "$ENV_LOCAL_FILE" "OAUTH_DO_CLIENT_ID" "$oauthClientId"
    updateFile "$ENV_LOCAL_FILE" "OAUTH_DO_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$oauthServerType" = "generic" ]; then
    updateFile "$ENV_LOCAL_FILE" "OAUTH_CLIENT_ID" "$oauthClientId"
    updateFile "$ENV_LOCAL_FILE" "OAUTH_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$oauthServerType" = "github" ]; then
    updateFile "$ENV_LOCAL_FILE" "OAUTH_GH_CLIENT_ID" "$oauthClientId"
    updateFile "$ENV_LOCAL_FILE" "OAUTH_GH_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$oauthServerType" = "gitlab" ]; then
    updateFile "$ENV_LOCAL_FILE" "OAUTH_GITLAB_CLIENT_ID" "$oauthClientId"
    updateFile "$ENV_LOCAL_FILE" "OAUTH_GITLAB_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$oauthServerType" = "google" ]; then
    updateFile "$ENV_LOCAL_FILE" "OAUTH_GOOGLE_CLIENT_ID" "$oauthClientId"
    updateFile "$ENV_LOCAL_FILE" "OAUTH_GOOGLE_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$oauthServerType" = "jira" ]; then
    updateFile "$ENV_LOCAL_FILE" "OAUTH_JIRA_CLIENT_ID" "$oauthClientId"
    updateFile "$ENV_LOCAL_FILE" "OAUTH_JIRA_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$oauthServerType" = "microsoft" ]; then
    updateFile "$ENV_LOCAL_FILE" "OAUTH_MS_CLIENT_ID" "$oauthClientId"
    updateFile "$ENV_LOCAL_FILE" "OAUTH_MS_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$useCatalog" = "0" ]; then
    updateFile "$ENV_LOCAL_FILE" "SPACE_KUBERNETES_CA_VALUE" "$(cat "$kubernetesCAFile")"
    updateFile "$ENV_LOCAL_FILE" "SPACE_KUBERNETES_CREATE_TOKEN" "$kubernetesToken"
  fi
  updateFile "$ENV_LOCAL_FILE" "SPACE_OCI_GLOBAL_REGISTRY_PWD" "$dockerGlobalRegistryPassword"
fi

if [ "$useDockerCompose" = "y" ]; then
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MAILER_DSN" "$mailerDSN"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MERCURE_JWT_TOKEN" "$mercureJwtToken"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MERCURE_PUBLISHER_JWT_KEY" "$mercureJwtToken"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MERCURE_SUBSCRIBER_JWT_KEY" "$mercureJwtToken"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MESSENGER_EXECUTE_JOB_DSN" "$MESSENGER_EXECUTE_JOB_DSN"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MESSENGER_HISTORY_SENT_DSN" "$MESSENGER_HISTORY_SENT_DSN"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MESSENGER_JOB_DONE_DSN" "$MESSENGER_JOB_DONE_DSN"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MESSENGER_NEW_JOB_DSN" "$MESSENGER_NEW_JOB_DSN"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MONGODB_SERVER" "$mongoDbDSN"
  if [ "$oauthServerType" = "digital_ocean" ]; then
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "OAUTH_DO_CLIENT_ID" "$oauthClientId"
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "OAUTH_DO_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$oauthServerType" = "generic" ]; then
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "OAUTH_CLIENT_ID" "$oauthClientId"
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "OAUTH_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$oauthServerType" = "github" ]; then
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "OAUTH_GH_CLIENT_ID" "$oauthClientId"
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "OAUTH_GH_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$oauthServerType" = "gitlab" ]; then
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "OAUTH_GITLAB_CLIENT_ID" "$oauthClientId"
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "OAUTH_GITLAB_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$oauthServerType" = "google" ]; then
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "OAUTH_GOOGLE_CLIENT_ID" "$oauthClientId"
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "OAUTH_GOOGLE_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$oauthServerType" = "jira" ]; then
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "OAUTH_JIRA_CLIENT_ID" "$oauthClientId"
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "OAUTH_JIRA_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$oauthServerType" = "microsoft" ]; then
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "OAUTH_MS_CLIENT_ID" "$oauthClientId"
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "OAUTH_MS_CLIENT_SECRET" "$oauthClientSecret"
  fi
  if [ "$useCatalog" = "0" ]; then
    updateFile "$ENV_LOCAL_FILE" "SPACE_KUBERNETES_CA_VALUE" "$(cat "$kubernetesCAFile")"
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_KUBERNETES_CREATE_TOKEN" "$kubernetesToken"
  fi
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_OCI_GLOBAL_REGISTRY_PWD" "$dockerGlobalRegistryPassword"
fi

updateFile "$ENV_LOCAL_FILE" "APP_ENV" "$APP_ENV"
updateFile "$ENV_LOCAL_FILE" "MAILER_REPLY_TO_ADDRESS" "$mailerSenderAddress"
updateFile "$ENV_LOCAL_FILE" "MAILER_SENDER_ADDRESS" "$mailerSenderAddress"
updateFile "$ENV_LOCAL_FILE" "MERCURE_PUBLISH_URL" "$mercurePublishUrl"
updateFile "$ENV_LOCAL_FILE" "MERCURE_SUBSCRIBER_URL" "$mercureSubscribeUrl"
updateFile "$ENV_LOCAL_FILE" "OAUTH_ENABLED" "$oauthEnabled"
updateFile "$ENV_LOCAL_FILE" "OAUTH_SERVER_TYPE" "$oauthServerType"
updateFile "$ENV_LOCAL_FILE" "OAUTH_GITLAB_SERVER_URL" "$oauthServerUrl"
updateFile "$ENV_LOCAL_FILE" "SPACE_2FA_PROVIDER" "$mFAProvider"
updateFile "$ENV_LOCAL_FILE" "SPACE_JWT_MAX_DAYS_TO_TIVE" "$jwtMaxAgeDelay"
if [ "$useCatalog" = "0" ]; then
  updateFile "$ENV_LOCAL_FILE" "SPACE_KUBERNETES_DASHBOARD" "$kubernetesDashboard"
  updateFile "$ENV_LOCAL_FILE" "SPACE_KUBERNETES_MASTER" "$kubernetesApi"
  updateFile "$ENV_LOCAL_FILE" "SPACE_CLUSTER_NAME" "$kubernetesClusterName"
  updateFile "$ENV_LOCAL_FILE" "SPACE_CLUSTER_TYPE" "kubernetes"
  updateFile "$ENV_LOCAL_FILE" "SPACE_KUBERNETES_CLUSTER_USE_HNC" "$kubernetesHnc"
else
  updateFile "$ENV_LOCAL_FILE" "SPACE_CLUSTER_CATALOG_FILE" "$clusterCatalogFile"
fi
updateFile "$ENV_LOCAL_FILE" "MONGODB_NAME" "$mongoDbName"
updateFile "$ENV_LOCAL_FILE" "SPACE_IMG_BUILDER_PLATFORMS" "$imgBuilderPlatforms"
updateFile "$ENV_LOCAL_FILE" "SPACE_KUBERNETES_CLIENT_VERIFY_SSL" "$kubernetesVerifySsl"
updateFile "$ENV_LOCAL_FILE" "SPACE_KUBERNETES_CLIENT_TIMEOUT" "$kubernetesClientTimeout"
updateFile "$ENV_LOCAL_FILE" "SPACE_KUBERNETES_VERSION_LEVEL" "$kubernetesVersionLevel"
updateFile "$ENV_LOCAL_FILE" "SPACE_OCI_GLOBAL_REGISTRY_URL" "$dockerGlobalRegistryApi"
updateFile "$ENV_LOCAL_FILE" "SPACE_OCI_GLOBAL_REGISTRY_USERNAME" "$dockerGlobalRegistryUser"
updateFile "$ENV_LOCAL_FILE" "SPACE_OCI_REGISTRY_URL" "$dockerPrivateRegistryUrl"
if [ "$useDockerComposeTarget" = "y" ]; then
  setEnvVar "$ENV_LOCAL_FILE" "SPACE_DC_ANSIBLE_BINARY" "$dcAnsibleBinary"
  setEnvVar "$ENV_LOCAL_FILE" "SPACE_DC_TIMEOUT" "$dcTimeout"
  setEnvVar "$ENV_LOCAL_FILE" "SPACE_DC_DEPLOY_ROOT" "$dcDeployRoot"
  setEnvVar "$ENV_LOCAL_FILE" "SPACE_DC_NETWORK_DRIVER" "$dcNetworkDriver"
  setEnvVar "$ENV_LOCAL_FILE" "SPACE_DC_TRAEFIK_CONTAINER" "$dcTraefikContainer"
  setEnvVar "$ENV_LOCAL_FILE" "SPACE_DC_TRAEFIK_DYNAMIC_DIR" "$dcTraefikDynamicDir"
  setEnvVar "$ENV_LOCAL_FILE" "SPACE_DC_TRAEFIK_CERTS_DIR" "$dcTraefikCertsDir"
  setEnvVar "$ENV_LOCAL_FILE" "SPACE_DC_TRAEFIK_ENTRYPOINT_WEB" "$dcTraefikEntrypointWeb"
  setEnvVar "$ENV_LOCAL_FILE" "SPACE_DC_TRAEFIK_ENTRYPOINT_WEBSECURE" "$dcTraefikEntrypointWebSecure"
  setEnvVar "$ENV_LOCAL_FILE" "SPACE_DC_TRAEFIK_ENTRYPOINT_TCP" "$dcTraefikEntrypointTcp"
  setEnvVar "$ENV_LOCAL_FILE" "SPACE_DC_TRAEFIK_ENTRYPOINT_UDP" "$dcTraefikEntrypointUdp"
  setEnvVar "$ENV_LOCAL_FILE" "SPACE_DC_HTTPS_BACKEND_INSECURE_SKIP_VERIFY" "$dcHttpsBackendInsecureSkipVerify"
  setEnvVar "$ENV_LOCAL_FILE" "SPACE_DC_REGISTRY_IMAGE" "$dcRegistryImage"
  setEnvVar "$ENV_LOCAL_FILE" "SPACE_DC_REGISTRY_NETWORK" "$dcRegistryNetwork"
  setEnvVar "$ENV_LOCAL_FILE" "SPACE_DC_REGISTRY_PORT" "$dcRegistryPort"
  setEnvVar "$ENV_LOCAL_FILE" "SPACE_DC_REGISTRY_TLS" "$dcRegistryTls"
  if [ -n "$dcTraefikCertResolver" ]; then
    setEnvVar "$ENV_LOCAL_FILE" "SPACE_DC_TRAEFIK_CERTRESOLVER" "$dcTraefikCertResolver"
  fi
fi
updateFile "$ENV_LOCAL_FILE" "SPACE_REDIS_HOST" "$redisHost"
updateFile "$ENV_LOCAL_FILE" "SPACE_REDIS_PORT" "$redisPort"
updateFile "$ENV_LOCAL_FILE" "TEKNOO_PAAS_SECURITY_ALGORITHM" "rsa"
updateFile "$ENV_LOCAL_FILE" "TEKNOO_PAAS_SECURITY_PRIVATE_KEY" "var/keys/messages/private.pem"
updateFile "$ENV_LOCAL_FILE" "TEKNOO_PAAS_SECURITY_PUBLIC_KEY" "var/keys/messages/public.pem"
updateFile "$ENV_LOCAL_FILE" "SPACE_PERSISTED_VAR_SECURITY_ALGORITHM" "rsa"
updateFile "$ENV_LOCAL_FILE" "SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY" "var/keys/variables/private.pem"
updateFile "$ENV_LOCAL_FILE" "SPACE_PERSISTED_VAR_SECURITY_PUBLIC_KEY" "var/keys/variables/public.pem"
if [ "$enableExtensions" = "1" ]; then
  updateFile "$ENV_LOCAL_FILE" "TEKNOO_EAST_EXTENSION_DISABLED" ""
  if [ "$extensionClassLoader" = "file" ]; then
    updateFile "$ENV_LOCAL_FILE" "TEKNOO_EAST_EXTENSION_LOADER" "Teknoo\\\\East\\\\Foundation\\\\Extension\\\\FileLoader"
    updateFile "$ENV_LOCAL_FILE" "TEKNOO_EAST_EXTENSION_FILE" "$extensionFile"
  else
    updateFile "$ENV_LOCAL_FILE" "TEKNOO_EAST_EXTENSION_LOADER" "Teknoo\\\\East\\\\Foundation\\\\Extension\\\\ComposerLoader"
  fi
else
  updateFile "$ENV_LOCAL_FILE" "TEKNOO_EAST_EXTENSION_DISABLED" "1"
fi

if [ "$useDockerCompose" = "y" ]; then
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "APP_ENV" "$APP_ENV"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MAILER_REPLY_TO_ADDRESS" "$mailerSenderAddress"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MAILER_SENDER_ADDRESS" "$mailerSenderAddress"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MERCURE_PUBLISH_URL" "$mercurePublishUrl"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "MERCURE_SUBSCRIBER_URL" "$mercureSubscribeUrl"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "OAUTH_ENABLED" "$oauthEnabled"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "OAUTH_SERVER_TYPE" "$oauthServerType"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "OAUTH_GITLAB_SERVER_URL" "$oauthServerUrl"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_2FA_PROVIDER" "$mFAProvider"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_JWT_MAX_DAYS_TO_TIVE" "$jwtMaxAgeDelay"
  if [ "$useCatalog" = "0" ]; then
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_KUBERNETES_DASHBOARD" "$kubernetesDashboard"
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_KUBERNETES_MASTER" "$kubernetesApi"
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_CLUSTER_NAME" "$kubernetesClusterName"
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_CLUSTER_TYPE" "kubernetes"
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_KUBERNETES_CLUSTER_USE_HNC" "$kubernetesHnc"
  else
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_CLUSTER_CATALOG_FILE" "$clusterCatalogFile"
  fi
  setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "MONGODB_NAME" "$mongoDbName"
  setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_IMG_BUILDER_PLATFORMS" "$imgBuilderPlatforms"
  setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_KUBERNETES_CLIENT_VERIFY_SSL" "$kubernetesVerifySsl"
  setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_KUBERNETES_CLIENT_TIMEOUT" "$kubernetesClientTimeout"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_KUBERNETES_VERSION_LEVEL" "$kubernetesVersionLevel"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_OCI_GLOBAL_REGISTRY_URL" "$dockerGlobalRegistryApi"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_OCI_GLOBAL_REGISTRY_USERNAME" "$dockerGlobalRegistryUser"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_OCI_REGISTRY_URL" "$dockerPrivateRegistryUrl"
  if [ "$useDockerComposeTarget" = "y" ]; then
    setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_DC_ANSIBLE_BINARY" "$dcAnsibleBinary"
    setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_DC_TIMEOUT" "$dcTimeout"
    setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_DC_DEPLOY_ROOT" "$dcDeployRoot"
    setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_DC_NETWORK_DRIVER" "$dcNetworkDriver"
    setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_DC_TRAEFIK_CONTAINER" "$dcTraefikContainer"
    setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_DC_TRAEFIK_DYNAMIC_DIR" "$dcTraefikDynamicDir"
    setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_DC_TRAEFIK_CERTS_DIR" "$dcTraefikCertsDir"
    setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_DC_TRAEFIK_ENTRYPOINT_WEB" "$dcTraefikEntrypointWeb"
    setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_DC_TRAEFIK_ENTRYPOINT_WEBSECURE" "$dcTraefikEntrypointWebSecure"
    setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_DC_TRAEFIK_ENTRYPOINT_TCP" "$dcTraefikEntrypointTcp"
    setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_DC_TRAEFIK_ENTRYPOINT_UDP" "$dcTraefikEntrypointUdp"
    setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_DC_HTTPS_BACKEND_INSECURE_SKIP_VERIFY" "$dcHttpsBackendInsecureSkipVerify"
    setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_DC_REGISTRY_IMAGE" "$dcRegistryImage"
    setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_DC_REGISTRY_NETWORK" "$dcRegistryNetwork"
    setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_DC_REGISTRY_PORT" "$dcRegistryPort"
    setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_DC_REGISTRY_TLS" "$dcRegistryTls"
    if [ -n "$dcTraefikCertResolver" ]; then
      setComposeEnv "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_DC_TRAEFIK_CERTRESOLVER" "$dcTraefikCertResolver"
    fi
  fi
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_REDIS_HOST" "$redisHost"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_REDIS_PORT" "$redisPort"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "TEKNOO_PAAS_SECURITY_ALGORITHM" "rsa"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "TEKNOO_PAAS_SECURITY_PRIVATE_KEY" "var/keys/messages/private.pem"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "TEKNOO_PAAS_SECURITY_PUBLIC_KEY" "var/keys/messages/public.pem"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_PERSISTED_VAR_SECURITY_ALGORITHM" "rsa"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY" "var/keys/variables/private.pem"
  updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "SPACE_PERSISTED_VAR_SECURITY_PUBLIC_KEY" "var/keys/variables/public.pem"
  if [ "$enableExtensions" = "1" ]; then
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "TEKNOO_EAST_EXTENSION_DISABLED" ""
    if [ "$extensionClassLoader" = "file" ]; then
      updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "TEKNOO_EAST_EXTENSION_LOADER" "Teknoo\\\\East\\\\Foundation\\\\Extension\\\\FileLoader"
      updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "TEKNOO_EAST_EXTENSION_FILE" "$extensionFile"
    else
      updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "TEKNOO_EAST_EXTENSION_LOADER" "Teknoo\\\\East\\\\Foundation\\\\Extension\\\\ComposerLoader"
    fi
  else
    updateFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "TEKNOO_EAST_EXTENSION_DISABLED" "1"
  fi

  echo ""
fi

if [ "$DRY_RUN" = "1" ]; then
  dumpDryRunFile() {
    echo ""
    echo "==================================================================="
    echo " FILE: $2"
    echo "==================================================================="
    if [ -f "$1" ]; then
      cat "$1"
    else
      echo "(not generated for these answers)"
    fi
    echo ""
  }

  echo ""
  echo "** $GREEN DRY RUN — no file was modified. Files that WOULD be written: $NC **"

  dumpDryRunFile "$ENV_LOCAL_FILE" "$REAL_ENV_LOCAL_FILE"
  dumpDryRunFile "$SESSION_FILE" "$REAL_SESSION_FILE"
  if [ "$useDockerCompose" = "y" ]; then
    dumpDryRunFile "$DOCKER_COMPOSE_OVERRIDE_FILE" "$REAL_DOCKER_COMPOSE_OVERRIDE_FILE"
    dumpDryRunFile "$DOCKER_COMPOSE_FILE" "$REAL_DOCKER_COMPOSE_FILE"
  fi
  echo ""
else
  echo ""
  echo "** $GREEN Space is configured $NC **"
  echo ""
fi
