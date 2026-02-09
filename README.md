Teknoo Software - Space
=======================

![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/TeknooSoftware/space-app)
[![License](https://shields.io/badge/license-BSD3-green?style=flat)](https://raw.githubusercontent.com/TeknooSoftware/space-app/main/LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)

Space is a `Platform as a Service` or a `Platform as a Code` application, a continuous integration/delivery/deployment
solution, built on `Teknoo East PaaS`, `Teknoo Kubernetes Client` and several `Symfony` components. The application
is multi-account, multi-users and multi-projects. It able to build and deploy IT projects on dedicated containerized
platforms on cluster. Space supports natively `Kubernetes` cluster but it was designed to support other types of
clusters by writting some drivers.

This is the `Standard` version of Space. It is released under the 3-Clause BSD license. This version includes :

* `East PaaS` integration
* Accounts and users management
    * Support of OAuth2 and MFA
    * an account represents the top entity (a company, a service, a foundation, an human, etc...)
    * an account has at least one user.
    * an user represents an human.
    * an account can have several environments.
* Quota
    * Applied on accounts.
    * Distributed on projects.
* Cluster namespace installation.
    * For each environment of accounts.
* Projects and jobs management
    * Projects are owned by account
    * all projects must be hosted on a Git instance, reachable via the protocols HTTPS or SSH.
    * projects' images are built thanks to Buildah.
    * Kubernetes clusters 1.30+ are supported.
    * a job represents a deployment
    * a job can provide several variables to pass to the compiler about the deployment.
        * variables can be persisted to the project to be reused in the future in next deployments.
        * projects can host persisted variables to be used in all next deployments.
        * accounts can host also persisted variables to be used on all deployments of all of this projects if they are
          not
          already defined in projects.
        * persisted variables can contains secrets.
            * **Warning**, secrets are not visible in Space's web app, but they are passed unencrypted to the workers if
              encryption is not enabled between servers and agents. Fill environments variables about East PaaS
              Encryption
              described later in this document.
* Web UI interface
* RESTFull API interfaces
* Deployment workers
* Kubernetes integration
    * include a Dashboard integration
* Space can allow any users to subscribe, but it's not manage billings.
    * Subscriptions can be restricted with unique code to forbid non granted user to subscribe.

A free support is available by Github issues of this repository.
About priority support, please contact us at <contact@teknoo.software>.
A commercial `Enterprise` version is planned with some additional features.

Support this project
---------------------
This project is free and will remain free. It is fully supported by the activities of the EIRL and
by the `Enterprise` edition sales.
If you like it and help me maintain it and evolve it, don't hesitate to support me on
[Patreon](https://patreon.com/teknoo_software) or [Github](https://github.com/sponsors/TeknooSoftware).

Thanks :) Richard.

Enterprise edition
------------------

The Enterprise edition includes :

* Bundled hooks
    * `Composer`
    * `PIP`
    * `NPM`
    * `Symfony Console`
    * `Laravel Artisan`
    * `Make tool`
* A set of containers, pods, services and ingresses libraries to reduce the size of your `space.paas.yml` file.
    * It's called `BigBang`.
* Helm charts to install and configuring your Space instance in your kubernetes and embedding Space in your Kubernetes.
* Trivy audit reports in the Space's Dashboard.
* Backup feature in your pods.
* AI Assistant to write `space.paas.yaml`
* WebHooks call.
* a commercial support.

The Enterprise edition is currently in alpha. With sponsoring, you can get a perpetual license and sources and update to
the first stable realase (for an internal use only). Please contact me at <richard@teknoo.software> to get a quote or
more information.

Credits
-------
EIRL Richard Déloge - <https://deloge.io> - Lead developer.
SASU Teknoo Software - <https://teknoo.software>

About Teknoo Software
---------------------
**Teknoo Software** is a PHP software editor, founded by Richard Déloge, as part of EIRL Richard Déloge.
Teknoo Software's goals : Provide to our partners and to the community a set of high quality services or software,
sharing knowledge and skills.

License
-------
Space is licensed under the 3-Clause BSD License - see the licenses folder for details.

Requirements
------------

This applications requires

    * PHP 8.4+
    * A PHP autoloader (Composer is recommended)
    * A webserver (like Httpd/nginx + PHP-FPM)
    * A MongoDB server (for the web interfaces and all workers except the builder)
    * A AMQP server, like RabbitMQ for the coomunication between components
    * A mercure server for the web interface and new job worker.
    * Buildah (Only for the builder)

This application is bundled with :

    * Teknoo/Immutable
    * Teknoo/States
    * Teknoo/Recipe
    * Teknoo/East-Foundation
    * Teknoo/East-Common
    * Teknoo/East-PaaS
    * Teknoo/Kubernetes Clent
    * Symfony 6.4+ or 7.3+
    * Doctrine ODM 3.5+ / MongoDB
    * FlySystem
    * Buildah

Extensions
----------

Space comes from an extension system, provided by `Teknoo East Foundation` since the 8 version. The extension allows
developpers to add more features and alter the Space behavior easily than edit Space's environments variables. Notably
extensions can :
* Add more Symfony Bundles.
* Complete the PHP-DI configuration.
* including :   
* Add more Recipe's steps, decorate bundled EditabledPlan and add more Plan.
* Add/Complete `Teknoo East PaaS` compiler. (By decorating `CompilerCollectionInterface`).
* Update the hooks collection, like `SPACE_HOOKS_COLLECTION_JSON`.
* Update the containers libraries, like `SPACE_PAAS_COMPILATION_CONTAINERS_EXTENDS_LIBRARY_JSON`.
* Update the pods libraries, like `SPACE_PAAS_COMPILATION_PODS_EXTENDS_LIBRARY_JSON`.
* Update the services libraries, like `SPACE_PAAS_COMPILATION_SERVICES_EXTENDS_LIBRARY_JSON`.
* Update the ingresses libraries, like `SPACE_PAAS_COMPILATION_INGRESSES_EXTENDS_LIBRARY_JSON`.
* Update the `globals`, like `SPACE_PAAS_GLOBAL_VARIABLES_JSON`.
* Update other configuration editable from environments variables.
* Add more routes and templates.
* Add entries to twig menus (top left menu and the main left menu).
* Change the logo.
* Add or alter assets (CSS, JS).

*The `Space Enterprise Edition` is a `Space Standard Edition` (free, and under the 3-Clause BSD license) with a
commercial plugin `Enterprise` (under commercial LICENSE) and a commercial support.*

Installation
------------

Space can be installed with standards composer command, but a Makefile is available to help to install and use it.
`make` is available in the folder `application`, but you can use the link `space.sh` instead of at the root of this
project. Commands are listed in the next section.

Space.sh CLI Tool
-----------------

This tool can be executed directly by calling the `space.sh` tool from the `application` folder or fron the root
project.
You can also use the `make` command directly under the folder `application` but extensions are not managed

* **Generics**:
    * `help`:          Show this help.
    * `verify`:        Download dependencies via Composer and verify space installation.
* **Installations**:
    * `install`:       To install all PHP vendors for Space, thanks to Composer, without dev libraries, build Symfony
      app and warmup caches.
    * `dev-install`:   To install all PHP vendors for Space, thanks to Composer, including dev libraries.
    * `update`:        Install and update all dependencies according to composer configuration without dev libraries,
      build Symfony app and warmup caches.
      Set the env var DEPENDENCIES to lowest to download lowest vendors versions instead of lasts versions.
    * `dev-update`:    Install and update all dependencies according to composer configuration, including dev libraries.
      Set the env var DEPENDENCIES to lowest to download lowest vendors versions instead of lasts versions.
* **Configuration**:
    * `config`:             To set values in env file to configure Space.
    * `create-admin`:       To create an administrator in users, requires "email" and "password" parameter.
    * `extension-list`:     To list available extension
    * `extension-enable`:   To enable an extension into Space, requires "name" parameter
    * `extension-disable`:  To disable an extension into Space, requires "name" parameter
* **Docker**:
    * `build`:         To build docker images to run locally Space on Docker.
    * `start`:         To start or refresh the docker stack and use Space locally on localhost.
    * `stop`:          To stop the docker stack.
    * `restart`:       To restart the docker stack.
* **QA**:
    * `qa`:            Run a set of quality tests, to detect bugs, securities or qualities issues.
    * `qa-offline`:    Run a set of quality tests, without audit, in offline, to detect bugs, securities or qualities
      issues.
    * `lint`:          To detect error in PHP file causing compilation errors.
    * `phpstan`:       To run code analyze with PHPStan to prevent bugs.
    * `phpcs`:         To check if the code follow the PSR 12.
    * `audit`:         Run an audit on vendors to detect CVE and deprecated libraries.
* **Testing**:
    * `test`:          Run tests (units tests and behavior tests, with a code coverage) to check if the installation can
      work properly.
    * `test-without-coverage`:  Run tests (units tests and behavior tests without a code coverage).
* **Cleaning**:
    * `clean`:         Remove all PHP vendors, composer generated map, clean all Symfony builds, caches and logs
    * `warmup`:        Clear cache and warming , dump autoloader
* **Extensions**:
    * `ext <extension name>`: To call a command from an extension.

Environnements variables configuration
--------------------------------------

* Global configuration :
    * `SPACE_HOSTNAME` : (string) url of the Space instance.
    * East PaaS Encryption :
        * Encryptions capacities in messages between servers and agents or workers :
        * `TEKNOO_PAAS_SECURITY_ALGORITHM` (with `rsa` ou `dsa`).
        * `TEKNOO_PAAS_SECURITY_PRIVATE_KEY` to define the private key location in the filesystem (to decrypt).
        * (optional) `TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE` about the passphrase to unlock the private key.
        * `TEKNOO_PAAS_SECURITY_PUBLIC_KEY` to define the public key location in the filesystem (to encrypt).
        * Default kubernetes annotations for ingresses **(Only one of these options)** *Optional* :
            * `SPACE_KUBERNETES_INGRESS_DEFAULT_ANNOTATIONS_JSON` : (json string).
            * `SPACE_KUBERNETES_INGRESS_DEFAULT_ANNOTATIONS_FILE` : (json file).
    * Persited variable Encryption :
        * Encryptions of persisted variables between servers and agents or workers :
        * `SPACE_PERSISTED_VAR_AGENT_MODE`: *optional* To force the agent mode.
          (by default it is enable only with cli sapi)
        * `SPACE_PERSISTED_VAR_SECURITY_ALGORITHM` (with `rsa` ou `dsa`).
        * `SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY` to define the private key location in the filesystem (to decrypt).
        * (optional) `SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY_PASSPHRASE` about the passphrase to unlock the private
          key.
        * `SPACE_PERSISTED_VAR_SECURITY_PUBLIC_KEY` to define the public key location in the filesystem (to encrypt).
    * Space Extensions : It's provided by `East Foundation` and use it's default configuration
        * `TEKNOO_EAST_EXTENSION_DISABLED` : *optional* To disable extension (By default, if this env var is set and NOT
          empty, the extension behavior will be disabled
        * `TEKNOO_EAST_EXTENSION_LOADER` : *optional* The full class name to find extensions. The class must implements
          the
          interface `Teknoo\East\Foundation\Extension\LoaderInterface`. There are to bundled loaded, but you can use
          our :
            * `Teknoo\East\Foundation\Extension\FileLoader` : Extensions are referenced into an array in a json file.
            * `Teknoo\East\Foundation\Extension\ComposerLoader` : Browse all loaded class from the autoloader's mapping
              to
              find all extensions. (Configuration less but poor performances).
        * `TEKNOO_EAST_EXTENSION_FILE` : *optional* If the extension loader is the `FileLoader`, the file referencing
          all
          extensions must be a json file returning an array of full class string of extension to load. The file is by
          default available at `extensions/enabled.json` from the common working directory of Space.
* Web configuration
    * Doctrine ODM
        * `MONGODB_SERVER` : (string) mongodb DSN.
        * `MONGODB_NAME` : (string) database name.
    * Symfony
        * `APP_SECRET` : (string) `framework.secret` value.
        * `APP_REMEMBER_SECRET` : (string) `remember_me.secret` value in Symfony firewall.
        * `MESSENGER_NEW_JOB_DSN` : (string) Messenger DSN to push to event bus (like AMQP) to dispatch a new deployment
          request.
    * Support
        * `SPACE_SUPPORT_CONTACT` : (string) Email address (or URI) for support contact displayed in the UI. *Optional*
    * 2FA
        * `SPACE_2FA_PROVIDER` : (string) Two factor provider to use (e.g. `google` or `generic`). `google` by default.
          *Optional*
    * Redis (sessions)
        * `SPACE_REDIS_HOST` : (string) Redis host used for sessions. *Optional*
        * `SPACE_REDIS_PORT` : (int) Redis port used for sessions. `6379` by default. *Optional*
    * Mailer.
        * `MAILER_DSN` : (string) Email transport configuration (Symfony Mailer DSN). *Optional*
        * `MAILER_SENDER_ADDRESS` : (string) Default sender email address. *Optional*
        * `MAILER_SENDER_NAME` : (string) Default sender display name. *Optional*
        * `MAILER_FORBIDDEN_WORDS` : (string) Comma-separated forbidden words to filter emails. *Optional*
        * `SPACE_MAIL_MAX_ATTACHMENTS` : (int) Maximum number of attachments allowed per email. *Optional*
        * `SPACE_MAIL_MAX_FILE_SIZE` : (int) Maximum file size per attachment in bytes. *Optional*
    * Mercure :
        * `SPACE_MERCURE_PUBLISHING_ENABLED` : (int/bool) to enable or not mercure protocol to allow redirection of user
          to
          the final job page when it is started. *Optional*
        * `MERCURE_SUBSCRIBER_URL` : (string) Mercure url used by browser to fetch the job page url. *Optional*
        * `MERCURE_JWT_TOKEN` : (string) Token to authenticate request. *Optional*
    * JWT :
        * `SPACE_JWT_SECRET_KEY` : (string) Path to the private key used to sign JWT tokens.
        * `SPACE_JWT_PUBLIC_KEY` : (string) Path to the public key used to verify JWT tokens.
        * `SPACE_JWT_PASSPHRASE` : (string) Passphrase to unlock the private key.
        * `SPACE_JWT_TTL` : (int) Token time-to-live in seconds.
        * `SPACE_JWT_ENABLE_IN_QUERY` : (int/bool) Allow JWT token to be passed via query string. *Optional*
        * `SPACE_JWT_MAX_DAYS_TO_TIVE`: (string) Maximum life in days for JWT token
    * OAuth :
        * `OAUTH_ENABLED` : (int/bool) Enable or disable OAuth login buttons in UI. *Optional*
        * `OAUTH_SERVER_TYPE` : (string) Provider type when using a generic/custom server. *Optional*
        * DigitalOcean:
            * `OAUTH_DO_CLIENT_ID` : (string) OAuth client id for DigitalOcean.
            * `OAUTH_DO_CLIENT_SECRET` : (string) OAuth client secret for DigitalOcean.
        * GitHub:
            * `OAUTH_GH_CLIENT_ID` : (string) OAuth client id for GitHub.
            * `OAUTH_GH_CLIENT_SECRET` : (string) OAuth client secret for GitHub.
        * GitLab:
            * `OAUTH_GITLAB_CLIENT_ID` : (string) OAuth client id for GitLab.
            * `OAUTH_GITLAB_CLIENT_SECRET` : (string) OAuth client secret for GitLab.
            * `OAUTH_GITLAB_SERVER_URL` : (string) Base URL of your GitLab instance (for self‑hosted).
        * Google:
            * `OAUTH_GOOGLE_CLIENT_ID` : (string) OAuth client id for Google.
            * `OAUTH_GOOGLE_CLIENT_SECRET` : (string) OAuth client secret for Google.
        * Jira:
            * `OAUTH_JIRA_CLIENT_ID` : (string) OAuth client id for Jira.
            * `OAUTH_JIRA_CLIENT_SECRET` : (string) OAuth client secret for Jira.
        * Microsoft:
            * `OAUTH_MS_CLIENT_ID` : (string) OAuth client id for Microsoft.
            * `OAUTH_MS_CLIENT_SECRET` : (string) OAuth client secret for Microsoft.
    * OCI images building :
        * `SPACE_OCI_REGISTRY_IMAGE` : (string) image of the registry `registry:latest` by default. *Optional*
        * `SPACE_OCI_REGISTRY_REQUESTS_CPU` : (string) vcore requests for the registry `10m` by default. *Optional*
        * `SPACE_OCI_REGISTRY_REQUESTS_MEMORY` : (string) memory requests for the registry `30Mi` by default. *Optional*
        * `SPACE_OCI_REGISTRY_LIMITS_CPU` : (string) vcore limits, `100m` by default. *Optional*
        * `SPACE_OCI_REGISTRY_LIMITS_MEMORY` : (string) memory limits `256Mi` by default. *Optional*
        * `SPACE_OCI_REGISTRY_URL` : (string) url for each private registry of each account.
          This url will be prefixed by the account slug.
        * `SPACE_OCI_REGISTRY_TLS_SECRET` : (string) name of the secret storing TLS certificate in the kubernetes
          cluster
          `registry-certs` by default.
        * `SPACE_OCI_REGISTRY_PVC_SIZE` : (string) size claimed by the PVC dedicated to the private registry of each
          account
          `4Gi` by default.
        * `SPACE_OCI_GLOBAL_REGISTRY_URL` : (string) url of the global oci image registry, reachable by all deployment
          on
          this instance.
        * `SPACE_OCI_GLOBAL_REGISTRY_USERNAME` : (string) username to access to this registry.
        * `SPACE_OCI_GLOBAL_REGISTRY_PWD` : (string) password to access to this registry.
    * Kubernetes :
        * `SPACE_KUBERNETES_CLIENT_TIMEOUT` : (int) max time in seconds allowed for each Kubernetes's API query.
          `3` by default. *Optional*
        * `SPACE_KUBERNETES_CLIENT_VERIFY_SSL` : (int/bool) to enable SSL check for each Kubernetes's API.
          `1` by default. *Optional*
        * `SPACE_KUBERNETES_ROOT_NAMESPACE` : (string) Prefix value to use for Kubernetes namespace for each client
          account. `space-client-` by default. *Optional*
        * `SPACE_KUBERNETES_REGISTRY_ROOT_NAMESPACE` : (string) Prefix value to use for Kubernetes namespace dedicated
          to registry for each client account. `space-registry-` by default. *Optional*
        * `SPACE_STORAGE_CLASS` : (string) Default storage class name to use in PVC.
          `nfs.csi.k8s.io` by default. *Optional*
        * `SPACE_STORAGE_DEFAULT_SIZE` : (string) Default size to use in PVC. `3Gi` by default. *Optional*
        * `SPACE_KUBERNETES_INGRESS_DEFAULT_CLASS`: (string) Default value of `kubernetes.io/ingress.class` in
          ingresses.
          `public` by default. *Optional*
        * `SPACE_CLUSTER_ISSUER` : (string) Default value of `cert-manager.io/cluster-issuer` in ingresses.
          `lets-encrypt` by default. *Optional*
        * `SPACE_KUBERNETES_SECRET_ACCOUNT_TOKEN_WAITING_TIME` : (int) max waiting time in seconds about the service
          account token creation. `5` by default. *Optional*
        * Ingress provider mapping **(Only one of these options)** *Optional* :
            * `SPACE_INGRESS_PROVIDER_JSON` : (json string).
            * `SPACE_INGRESS_PROVIDER_FILE` : (json file).
            * Dictionary's structure : `{'pattern': 'type'}` where :
                * `pattern` : (string) Regular expression to match against the ingress class name.
                * `type` : (string) Ingress provider type. Valid values: `nginx`, `traefik`, `traefik1`, `traefik2`,
                  `haproxy`, `aws`, or `gce`. Defaults to `nginx` if no match or invalid type.
        * Managed kubernetes cluster :
            * One cluster (legacy):
                * `SPACE_KUBERNETES_MASTER` : (string) Default URL of Kubernetes API server.
                * `SPACE_KUBERNETES_DASHBOARD` : (string) Kubernetes Dashboard URL to use to display this dashboard in
                  the
                  Space dashboard. *Optional*
                * `SPACE_KUBERNETES_CREATE_TOKEN` : (string) Service account's token dedicated to creation of new client
                  account namespace, role, etc..).
                * `SPACE_KUBERNETES_CA_VALUE` : (string) Default CA for custom TLS certificate of the K8S API Service.
                  *Optional*
                * `SPACE_CLUSTER_NAME` : (string) name of the default Kubernetes cluster in the project's form.
                * `SPACE_CLUSTER_TYPE` : (string) type of cluster in the project's form.
                  `kubernetes` by default. *Optional*
            * Several clusters :
                * `SPACE_CLUSTER_CATALOG_JSON` : (json array).
                * Dictionary's structure (`.` represent a subarray) :
                    * `master` : (string) Default URL of Kubernetes API server.
                    * `dashboard` : (string) Kubernetes Dashboard URL to use to display this dashboard in the
                      Space dashboard. *Optional*
                    * `create_account.token`: (string) Service account's token dedicated to creation of new client
                      account
                      (namespace, role, etc..).
                    * `create_account.ca_cert` : (string) Default CA for custom TLS certificate of the K8S API Service.
                      *Optional*
                    * `name` : (string) name of the default Kubernetes cluster in the project's form.
                    * `type` : (string) type of cluster in the project's form.
                      `kubernetes` by default. *Optional*
                    * `storage_provisioner` : (string) Default storage provisioner *Optional*
                    * `support_registry` : (bool) If the cluster can host private OCI registries *Optional*
                    * `use_hnc` : (bool) If the cluster use hierarchical namespace *Optional*

    * Subscription
        * `SPACE_CODE_SUBSCRIPTION_REQUIRED` : (int/bool) to restrict user's subscriptions only for users with a
          valid code. *Optional*
        * `SPACE_CODE_GENERATOR_SALT` : (string) salt used to compute the code with the account's name. *Optional*
        * `SPACE_SUBSCRIPTION_DEFAULT_PLAN` : (string) *Optional* Defaut plan to apply when a new account is created
        * Plan (to apply quota) *Optional* **(Only one of these options)** :
            * `SPACE_SUBSCRIPTION_PLAN_CATALOG_JSON` : (json array).
            * `SPACE_SUBSCRIPTION_PLAN_CATALOG_FILE` : (json file).
            * Dictionary's structure (`[].` represent a collection of subarray) :
                * `id` : (string) Plan identifier.
                * `name` : (string) Humain readable plan name
                * `envsCountAllowed` : (int) count of managed clusters's namespace/env allowed for this plan
                * `quotas[].category` : (string) `compute` or `memory` - Category of the quota
                * `quotas[].type` : (string) name of the quota
                * `quotas[].capacity` : (string) total of capacity allowed for an account
                  (sum of all containers's `limit`)
                * `quotas[].require` : (string) *Optional* Total of requires / requests allowed for an account
                * `clusters`: (string[]) *Optional* List of clusters allowed with this plan (available later)

    * Job create
        * `SPACE_NEW_JOB_WAITING_TIME` : (int) time in seconds to wait before redirect user to the job page. *Optional*

* Workers configuration :
    * Workers only (not builder) :
        * Doctrine ODM :
            * `MONGODB_SERVER` : (string) mongodb DSN
            * `MONGODB_NAME` : (string) database name

    * Symfony Messengers:
        * `MESSENGER_HISTORY_SENT_DSN` : (string) Messenger DSN to push to event bus (like AMQP) to dispatch deployment
          event from builder worker to persist it.
        * `MESSENGER_JOB_DONE_DSN` : (string) Messenger DSN to push to event bus (like AMQP) to dispatch the final
          deployment event from builder worker when it's done.
        * `MESSENGER_EXECUTE_JOB_DSN` : (string) Messenger DSN to push to event bus (like AMQP) to dispatch a configured
          deployment of a project to a builder worker.

    * Mercure (Only for new Job workers):
        * `SPACE_MERCURE_PUBLISHING_ENABLED` : (int/bool) to enable or not mercure protocol to allow redirection of user
          to
          the final job page when it is started. *Optional*
        * `MERCURE_PUBLISH_URL` : (string) Mercure url to push the job page url to follow the deployment. *Optional*
        * `MERCURE_JWT_TOKEN` : (string) Token to authenticate request. *Optional*

    * Healthcheck (for all workers, agents and builders) :
        * `SPACE_PING_FILE` : (string) file used by Space's workers and builder to indicate the state of health, read by
          the orchestrator. `/tmp/ping_file` by default. *Optional*
        * `SPACE_PING_SECONDS` : (int) number of seconds between each update in the `ping file`, `60` by default.
          *Optional*

    * Execute job / deployment :
        * Global configuration :
        * `SPACE_JOB_ROOT` : (string) path where run reployment (git clone and build image), `/tmp` by default.
        * `SPACE_WORKER_TIME_LIMIT` : (int) max time allowed for each deployment before kill it. *Optional*
        * `SPACE_GIT_TIMEOUT` : (int) max time allowed to clone a project in the deployment.
          Can't be bigger than `SPACE_WORKER_TIME_LIMIT`. *Optional*
        * PaaS Compilation :
            * Embedded OCI library with Dockerfile **(Only one of these options)** *Optional* :
                * `SPACE_PAAS_IMAGE_LIBRARY_JSON` : (json array).
            * Global variables availables for all jobs **(Only one of these options)** *Optional* :
                * `SPACE_PAAS_GLOBAL_VARIABLES_JSON` : (json array).

            * Compilation extensions (to be use with `extends` instruction in the `.paas.yaml`) :
                * Pods **(Only one of these options)** *Optional* :
                    * `SPACE_PAAS_COMPILATION_PODS_EXTENDS_LIBRARY_JSON` : (json string).
                    * `SPACE_PAAS_COMPILATION_PODS_EXTENDS_LIBRARY_FILE` : (json file).
                * Containers **(Only one of these options)** *Optional* :
                    * `SPACE_PAAS_COMPILATION_CONTAINERS_EXTENDS_LIBRARY_JSON` : (json string).
                    * `SPACE_PAAS_COMPILATION_CONTAINERS_EXTENDS_LIBRARY_FILE` : (json file).
                * Services **(Only one of these options)** *Optional* :
                    * `SPACE_PAAS_COMPILATION_SERVICES_EXTENDS_LIBRARY_JSON` : (json string).
                    * `SPACE_PAAS_COMPILATION_SERVICES_EXTENDS_LIBRARY_FILE` : (json file).
                * Ingresses **(Only one of these options)** *Optional* :
                    * `SPACE_PAAS_COMPILATION_INGRESSES_EXTENDS_LIBRARY_JSON` : (json string).
                    * `SPACE_PAAS_COMPILATION_INGRESSES_EXTENDS_LIBRARY_FILE` : (json file).

        * Hooks : To define hooks usable in Space (Composer, NPM, PIP, Make, etc..)
            * `SPACE_HOOKS_COLLECTION_JSON` : (json array).
            * Dictionary's structure (`.` represent a subarray) :
                * `name` : (string) Default URL of Kubernetes API server.
                * `type` : (string) "composer", "npm", "pip", "make", "symfony_console", or a class name implementing
                  the
                  `HookInterface`.
                * `command` : (array of string or string) path to the composer executable. `$PATH` joker is usable.
                * `timeout`: (int) max time allowed to install dependencies via Composer.
                  Can't be bigger than `SPACE_WORKER_TIME_LIMIT`. *Optional*, 240s by default

        * OCI Image building :
            * `SPACE_IMG_BUILDER_CMD` : (string) name of the tool to use to create OCI/Docker image.
              `buildah` by default. *Optional*
            * `SPACE_IMG_BUILDER_TIMEOUT` : (int) max time allowed to install create OCI/Docker image.
              Can't be bigger than `SPACE_WORKER_TIME_LIMIT`. *Optional*
            * `SPACE_IMG_BUILDER_PLATFORMS` : (string) name of the platform whose image is dedicated.
              `linux/amd64` by default. *Optional*

        * Kubernetes :
            * `SPACE_KUBERNETES_CLIENT_TIMEOUT` : (int) max time in seconds allowed for each Kubernetes's API query.
              `3` by default. *Optional*
            * `SPACE_KUBERNETES_CLIENT_VERIFY_SSL` : (int/bool) to enable SSL check for each Kubernetes's API.
              `1` by default. *Optional*
            * `SPACE_STORAGE_CLASS` : (string) Default storage class name to use in PVC.
              `nfs.csi.k8s.io` by default. *Optional*
            * `SPACE_KUBERNETES_INGRESS_DEFAULT_CLASS`: (string) Default value of `kubernetes.io/ingress.class` in
              ingresses.
              `public` by default. *Optional*
            * `SPACE_CLUSTER_ISSUER` : (string) Default value of `cert-manager.io/cluster-issuer` in ingresses.
              `lets-encrypt` by default. *Optional*
            * Ingress provider mapping **(Only one of these options)** *Optional* :
                * `SPACE_INGRESS_PROVIDER_JSON` : (json string).
                * `SPACE_INGRESS_PROVIDER_FILE` : (json file).
                * Dictionary's structure : `{'pattern': 'type'}` where :
                    * `pattern` : (string) Regular expression to match against the ingress class name.
                    * `type` : (string) Ingress provider type. Valid values: `nginx`, `traefik`, `traefik1`,
                      `traefik2`, `haproxy`, `aws`, or `gce`. Defaults to `nginx` if no match or invalid type.

Worker Commands
---------------

To launch workers on your environment if you does not use docker compose :

* worker to prepare a new job : `bin/console messenger:consume new_job`
* worker to persist histories of jobs : `bin/console messenger:consume history_sent`
* worker to persist final results of jobs : `bin/console messenger:consume job_done`
* worker to execute jobs : `bin/console messenger:consume execute_job`

Contribute :)
-------------
You are welcome to contribute to this project. [Fork it on Github](CONTRIBUTING.md)
