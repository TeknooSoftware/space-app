Teknoo Software - Space
=======================

![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/TeknooSoftware/space-app)
[![License](https://shields.io/badge/license-MIT-green?style=flat)](https://raw.githubusercontent.com/TeknooSoftware/space-app/main/LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)

Space is a `Platform as a Service` application, a continuous integration/delivery/deployment solution, built on
`Teknoo East PaaS`, `Teknoo Kubernetes Client` and the `Symfony` components. The application is multi-account,
multi-users and multi-projects, and able to build and deploy projects on dedicated containerized platforms on
`Kubernetes` cluster.

This is the `Standard` version of Space. It is released under MIT licence. This version includes :

* an account represents the top entity (a company, a service, a foundation, an human, etc...)
* an account has at least one user.
* an user represent an human.
* an account has projects.
* projects have deployment jobs.
* all projects must be hosted on a Git instance, reachable via the protocoles HTTPS or SSH.
* projects' images are built thanks to Buildah.
* Kubernetes clusters 1.22+ are supported.
* a job represents a deployment
* a job can provide several variables to pass to the compiler about the deployment.
    * variables can be persisted to the project to be reused in the future in next deployments.
    * projects can host persisted variables to be used in all next deployments.
    * accounts can host also persisted variables to be used on all deployments of all of this projects if they are not
      already defined in projects.
    * persisted variables can contains secrets.
        * Warning, secrets are not visible in Space's web app, but they are passed unencrypted to the workers if
          encryption is not enabled between servers and agents. Fill environments variables about East PaaS Encryption
          described later in this document..
* Space is bundled with Composer hook to create a PHP project. Python and PIP, NodeJs and NPM and gcc and Make are
  also supported.
* Space allow any users to subscribe, but it's not manage billings.
    * Subscriptions can be restricted with unique code to forbid non granted user to subscribe
* Space supports 2FA authentication with an TOTP application like Google Authenticator.

A free support is available by Github issues of this repository.
About priority support, please contact us at <contact@teknoo.software>.
A commercial `Enterprise` version is planned with some additional features.

Support this project
---------------------
This project is free and will remain free. It is fully supported by the activities of the EIRL.
If you like it and help me maintain it and evolve it, don't hesitate to support me on
[Patreon](https://patreon.com/teknoo_software) or [Github](https://github.com/sponsors/TeknooSoftware).

Thanks :) Richard.

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
Space is licensed under the MIT License - see the licenses folder for details.

Requirements
------------

This applications requires

    * PHP 8.2+
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
    * Teknoo/space-app
    * Teknoo/Kubernetes Clent
    * Symfony 6.4+
    * Doctrine ODM 2.6+ / MongoDB
    * Flysystem
    * Buildah

Installation
------------

Space can be installed with standards composer command, but a Makefile is available to help to install and use it.
`make` is available in the folder `application`, but you can use the link `space.sh` instead of at the root of this
project. `make` commandes are :

* **Generics**:
    * `help`:          Show this help.
    * `verify`:        Download dependencies via Composer and verify space installation.
* **Installations**:
    * `install`:       To install all PHP vendors for Space, thanks to Composer, without dev libraries, build Symfony
      app
      and warmup caches.
    * `dev-install`:   To install all PHP vendors for Space, thanks to Composer, including dev libraries.
    * `update`:        Install and update all dependencies according to composer configuration
      Set the env var DEPENDENCIES to lowest to download lowest vendors versions instead of lasts
      versions.
    * `config`:        To set values in env file to configure Space.
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
    * `composerunsed`: To detect unused vendor in the code.
    * `audit`:         Run an audit on vendors to detect CVE and deprecated libraries.
* **Testing**:
    * `test`:          Run tests (units tests and behavior tests, with a code coverage) to check if the installation can
      work properly.
    * `test-without-coverage`:  Run tests (units tests and behavior tests without a code coverage).
* **Cleaning**:
    * `clean`:         Remove all PHP vendors, composer generated map, clean all Symfony builds, caches and logs.

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
            * `SPACE_KUBERNETES_INGRESS_DEFAULT_ANNOTATIONS_FILE` : (php file returning an array).
* Web configuration
    * Doctrine ODM
        * `MONGODB_SERVER` : (string) mongodb DSN.
        * `MONGODB_NAME` : (string) database name.
    * Symfony
        * `APP_SECRET` : (string) `framework.secret` value.
        * `APP_REMEMBER_SECRET` : (string) `remember_me.secret` value in Symfony firewall.
        * `MESSENGER_NEW_JOB_DSN` : (string) Messenger DSN to push to event bus (like AMQP) to dispatch a new deployment
          request.
    * Mailer.
        * `MAILER_TRANSPORT` : (string) mailer protocol. `smtp` by default. *Optional*
        * `MAILER_HOST` : (string) mail server. *Optional*
        * `MAILER_USER` : (string) username to access to the mail server. *Optional*
        * `MAILER_PASSWORD` : (string) password to access to the mail server. *Optional*
    * Mercure :
        * `SPACE_MERCURE_PUBLISHING_ENABLED` : (int/bool) to enable or not mercure protocol to allow redirection of user
          to
          the final job page when it is started. *Optional*
        * `MERCURE_SUBSCRIBER_URL` : (string) Mercure url used by browser to fetch the job page url. *Optional*
        * `MERCURE_JWT_TOKEN` : (string) Token to authenticate request. *Optional*
    * OCI images building :
        * `SPACE_OCI_REGISTRY_IMAGE` : (string) image of the registry `registry:latest` by default.
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
          account
          `space-client-` by default. *Optional*
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
      * Managed kubernetes cluster :
        * One cluster (legacy):
          * `SPACE_KUBERNETES_MASTER` : (string) Default URL of Kubernetes API server.
          * `SPACE_KUBERNETES_DASHBOARD` : (string) Kubernetes Dashboard URL to use to display this dashboard in the
              Space dashboard. *Optional*
          * `SPACE_KUBERNETES_CREATE_TOKEN` : (string) Service account's token dedicated to creation of new client account
            (namespace, role, etc..).
          * `SPACE_KUBERNETES_CA_VALUE` : (string) Default CA for custom TLS certificate of the K8S API Service.
            *Optional*
          * `SPACE_KUBERNETES_CLUSTER_NAME` : (string) name of the default Kubernetes cluster in the project's form.
          * `SPACE_KUBERNETES_CLUSTER_TYPE` : (string) type of cluster in the project's form.
                `kubernetes` by default. *Optional*
          * `SPACE_KUBERNETES_CLUSTER_ENV` : (string) name of the default environment created with the project.
                `prod` by default. *Optional*
        * Several clusters : 
          * `SPACE_KUBERNETES_CLUSTER_CATALOG_JSON` : (json string).
          * `SPACE_KUBERNETES_CLUSTER_CATALOG_FILE` : (php file returning an array).
          * Dictionary's structure (`.` represent a subarray) :
            * `master` : (string) Default URL of Kubernetes API server.
            * `dashboard` : (string) Kubernetes Dashboard URL to use to display this dashboard in the
               Space dashboard. *Optional*
            * `create_account.token`: (string) Service account's token dedicated to creation of new client account
               (namespace, role, etc..).
            * `create_account.ca_cert` : (string) Default CA for custom TLS certificate of the K8S API Service.
                 *Optional*
            * `name` : (string) name of the default Kubernetes cluster in the project's form.
            * `type` : (string) type of cluster in the project's form.
                  `kubernetes` by default. *Optional*
            * `env` : (string) name of the default environment created with the project.
                  `prod` by default. *Optional*

    * Subscription
        * `SPACE_CODE_SUBSCRIPTION_REQUIRED` : (int/bool) to restrict user's subscriptions only for users with a
          valid code. *Optional*
        * `SPACE_CODE_GENERATOR_SALT` : (string) salt used to compute the code with the account's name. *Optional*
        * `SPACE_SUBSCRIPTION_DEFAULT_PLAN` : (string) *Optional* Defaut plan to apply when a new account is created
        * Plan (to apply quota) *Optional*
          * `SPACE_SUBSCRIPTION_PLAN_CATALOG_JSON` : (json string).
          * `SPACE_SUBSCRIPTION_PLAN_CATALOG_FILE` : (php file returning an array).
          * Dictionary's structure (`[].` represent a collection of subarray) :
              * `id` : (string) Plan identifier.
              * `name` : (string) Humain readable plan name
              * `quotas[].category` : (string) `compute` or `memory` - Category of the quota 
              * `quotas[].type` : (string) name of the quota
              * `quotas[].capacity` : (string) total of capacity allowed for an account (sum of all containers's `limit`)
              * `quotas[].require` : (string) *Optional* Total of requires / requests allowed for an account

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
                * `SPACE_PAAS_IMAGE_LIBRARY_JSON` : (json string).
                * `SPACE_PAAS_IMAGE_LIBRARY_FILE` : (php file returning an array).
            * Global variables availables for all jobs **(Only one of these options)** *Optional* :
                * `SPACE_PAAS_GLOBAL_VARIABLES_JSON` : (json string).
                * `SPACE_PAAS_GLOBAL_VARIABLES_FILE` : (php file returning an array).

            * Compilation extensions (to be use with `extends` instruction in the `.paas.yaml`) :
                * Pods **(Only one of these options)** *Optional* :
                    * `SPACE_PAAS_COMPILATION_PODS_EXTENDS_LIBRARY_JSON` : (json string).
                    * `SPACE_PAAS_COMPILATION_PODS_EXTENDS_LIBRARY_FILE` : (php file returning an array).
                * Containers **(Only one of these options)** *Optional* :
                    * `SPACE_PAAS_COMPILATION_CONTAINERS_EXTENDS_LIBRARY_JSON` : (json string).
                    * `SPACE_PAAS_COMPILATION_CONTAINERS_EXTENDS_LIBRARY_FILE` : (php file returning an array).
                * Services **(Only one of these options)** *Optional* :
                    * `SPACE_PAAS_COMPILATION_SERVICES_EXTENDS_LIBRARY_JSON` : (json string).
                    * `SPACE_PAAS_COMPILATION_SERVICES_EXTENDS_LIBRARY_FILE` : (php file returning an array).
                * Ingresses **(Only one of these options)** *Optional* :
                    * `SPACE_PAAS_COMPILATION_INGRESSES_EXTENDS_LIBRARY_JSON` : (json string).
                    * `SPACE_PAAS_COMPILATION_INGRESSES_EXTENDS_LIBRARY_FILE` : (php file returning an array).

        * Hook :
            * Composer :
                * `SPACE_COMPOSER_PATH_JSON` : (json string) path to the composer executable. `$PATH/composer` by
                  default
                    * `SPACE_COMPOSER_PATH_FILE` : file alternative
                * `SPACE_COMPOSER_TIMEOUT` : (int) max time allowed to install dependencies via Composer.
                  Can't be bigger than `SPACE_WORKER_TIME_LIMIT`. *Optional*
            * Symfony Console :
                * `SPACE_SFCONSOLE_PATH_JSON` : (json string) path to the symfony console executable. `bin/console` by
                  default
                    * `SPACE_SFCONSOLE_PATH_FILE` : file alternative
                * `SPACE_SFCONSOLE_TIMEOUT` : (int) max time allowed to install dependencies via symfony console.
                  Can't be bigger than `SPACE_WORKER_TIME_LIMIT`. *Optional*
            * Npm :
                * `SPACE_NPM_PATH_JSON` : (json string) path to the npm executable. `$PATH/npm` by default
                    * `SPACE_NPM_PATH_FILE` : file alternative
                * `SPACE_NPM_TIMEOUT` : (int) max time allowed to install dependencies via Npm.
                  Can't be bigger than `SPACE_WORKER_TIME_LIMIT`. *Optional*
            * Pip :
                * `SPACE_PIP_PATH_JSON` : (json string) path to the pip executable. `$PATH/pip` by default
                    * `SPACE_PIP_PATH_FILE` : file alternative
                * `SPACE_PIP_TIMEOUT` : (int) max time allowed to install dependencies via Pip.
                  Can't be bigger than `SPACE_WORKER_TIME_LIMIT`. *Optional*
            * Make :
                * `SPACE_MAKE_PATH_JSON` : (json string) path to the make executable. `$PATH/make` by default
                    * `SPACE_MAKE_PATH_FILE` : file alternative
                * `SPACE_MAKE_TIMEOUT` : (int) max time allowed to install dependencies via Make.
                  Can't be bigger than `SPACE_WORKER_TIME_LIMIT`. *Optional*

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

Commands
--------

* worker to prepare a new job : `bin/console messenger:consume new_job`
* worker to persist histories of jobs : `bin/console messenger:consume history_sent`
* worker to persist final results of jobs : `bin/console messenger:consume job_done`
* worker to execute jobs : `bin/console messenger:consume execute_job`

Contribute :)
-------------
You are welcome to contribute to this project. [Fork it on Github](CONTRIBUTING.md)
