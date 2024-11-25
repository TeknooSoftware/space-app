# Teknoo Software - Space - Change Log

## [1.0.0] - 2024-11-25
### Stable Version
- First stable version.
- Update to `Teknoo East PaaS` 4.2.
- Fix deprecation with PHP 8.4

## [1.0.0-RC9] - 2024-11-21
### Release Candidate
- Last Release Candidate. Stable release come the 25th November.
- Fix some mistakes.
- Update `teknoo.space.assets.version`.
- Update libs and dev libraries.
- Update libs and temp fix about incompatibility between symfony/type-info and phpstan/phpdoc-parser.
- Update Symfony lib about CVE and other issues.
- Fix space.sh make call without change working directory.
- Update illuminates libraries.
- Allow running PHP Unit Tests in extensions.
- Update behat configuration.
- Update twig and fixing issue with json twig template with Twig 3.15.
- Update make tool.
- Add cli tool to manage extensions.
- Update and fix configuration cli tool.
- Rename `SPACE_KUBERNETES_CLUSTER_NAME` to `SPACE_CLUSTER_NAME`.
- Rename `SPACE_KUBERNETES_CLUSTER_TYPE` to `SPACE_CLUSTER_TYPE`.
- Rename `di.variables.kubernetes.php` to `di.variables.clusters.php`.
- Rename `teknoo.space.kubernetes.default_cluster` to `teknoo.space.clusters.default_cluster`.

## [1.0.0-RC8] - 2024-11-08
### Release Candidate
- Huge realase for an RC (To improve the future of Space and migrate some behavior from internals projects to Space 
  or others publics components of Teknoo to reduce the maintenance).
  - The final release come soon.
- Update to `Teknoo Recipe 6`, `Teknoo East Foundation 8`, `Teknoo East Common 3` and `Teknoo East PaaS 4`.
- Rename `Cookbook` to `Plan`, use `EditablePlan` instead of additionals steps and remove `AdditionalsSteps`
- Update libraries `league/flysystem` and `mongodb/mongodb`.
- Update `bacon/bacon-qr-code` and `illuminate` libraries.
- Update `knpuniversity/oauth2-client-bundle` and `php-http` libraries.
- Update `Doctrine` libraries
- Update `Symfony` and `Twig libraries` to fix CVE.
- Update `endroid/qr-code` to 6.
- Require PHP 8.3 or newer.
- Add and enable from `Teknoo East Foundation` a new extension system, to store into the extensions folder to add 
  easily extensions. Environments variables about projects' buildings (OCI libraries, pods and services extensions, 
  etc) still availables. But for complexes features, this extension behavior is provided. With extensions, you can :
  - Update the Definition of Containers
  - Update the list of Symfony Bundle
  - Add some routes to Symfony
  - Add some twig templates
  - Add new endpoints and features
  - Complete the definitions of clusters, OCI libraries, extensions.
  - Add some assets
  Without change a file into the `config` folder. `Space Enterprise edition` will use this feature, it will a set of 
  extensions.
  - Available extensions modules in `Space` are :
    - `Teknoo\East\FoundationBundle\Extension\Bundles` : to add bundles to load.
      - Bundles can load their own Symfony's container definition (`services.yaml`), translation, template and code
    - `Teknoo\East\FoundationBundle\Extension\PHPDI`: to alter the Definition of Containers. 
    - `Teknoo\East\FoundationBundle\Extension\Routes`: to add some routes
    - `Teknoo\Space\Infrastructures\Twig\SpaceExtension\Twig`: to complete some templates (in `Space App`) :
      - `space_top_header_menu` to complete the top right menu
      - `space_left_brand` to complete the top left logo
      - `space_left_menu` to complete the left menu
      - `space_container` to prepend contents in front of the main content
      It's not possible to add blocks from this calls.
    - `Teknoo\East\Common\FrontAsset\Extensions\SourceLoader`
- Add `ClusterCatalog` to get Default Cluster.
- Replace call of `uniqid` by `random_bytes`.
- Complete tests
- Prevent issue in `dashboard.list.html.twig` when the variable `pageCount` is not present
- `DashboardInfoInterface` contracts and components to `ClustersInfo`.
- Rework how cluster and environment are selected in the dashboard, to migrate them into in a step and be reused.
- Add variables into `dashboard.list.html.twig` to allow one direction collection, without pagination.
- `AccountEnvironment` support metadata to complete env data from extension.
- Cleaning `Behat` Test, split context in traits, to avoid multiple contexts (to simplify tests writing) and 
  avoid huge PHP file, to be more understandable. Convert to public some privates methods to allow using the context 
  into extensions.

## [1.0.0-RC7] - 2024-09-27
### Release Candidate
- Update PHPSecLib and Nikic Php Parser
- Update sebastien's libs
- Update CS libs
- Update PHPUnit and PHPStan libs and doctrine/mongodb-odm
- Update Symfony libs
- Enable PHP8.4 in tests
- Update illuminate libraries and laravel/serializable-closure
- Update lcobucci/clock
- Update zenstruck/messenger-test and phpstan

## [1.0.0-RC6] - 2024-09-13
### Release Candidate
- Update Illuminate libraries
- Update MongoDB ODM Bundle
- Update endroid/qr-code libs
- Update East PaaS library
- Update Twg 3.14
- Update Laminas Diactoros library
- Switch to PHPUnit 11
- Update PHPUnit Configuration
- Update devs libraries
- Fix and restore blackfire in docker dev images

## [1.0.0-RC5] - 2024-08-31
### Release Candidate
- Update to last Symfony
- Update to Twig 3.12
- Remove deprecation in twig templates
- Use trim in rendering service instead of spaceless filter for api "views"

## [1.0.0-RC4] - 2024-08-14
### Release Candidate
- Update to last guzzle
- Update to last laravel collections libraries
- Update to last dev libraries
- Require last version of Symfony 6.4 or 7.1
- Update mongo docker dev image to allow choose another mongo version thanks to the build argument `MONGO_VERSION`
- Fix Symfony configuration
- Fix docker compose file

## [1.0.0-RC3] - 2024-07-22
### Release Candidate
- Require last version of Symfony 6.4 or 7.1
- Require scheb/2fa libraries 7.5

## [1.0.0-RC2] - 2024-06-24
### Release Candidate
- Use East PaaS 3.4.2
- Update to PHPUnit 11
- Update to last laravel collections libraries
- Update to phpseclib/phpseclib 3.0.39
- Fixing deprecations

## [1.0.0-RC1] - 2024-06-04
### Release Candidate
- Fix mapping in Doctrine ODM
- Fix and complete some test
- Fix BC breaks and some bug with persisted variables
- Fix issue when a secret var is unsecret, and harmonize behavior of secret encrypted and non secret encrypted
- Use East PaaS 3.4
- Use Lexik JWT 2
- Replace Hook collection management and delete env vars:
    - SPACE_COMPOSER_PATH_JSON
    - SPACE_COMPOSER_PATH_FILE
    - SPACE_COMPOSER_TIMEOUT
    - SPACE_SFCONSOLE_PATH_JSON
    - SPACE_SFCONSOLE_PATH_FILE
    - SPACE_SFCONSOLE_TIMEOUT
    - SPACE_NPM_PATH_JSON
    - SPACE_NPM_PATH_FILE
    - SPACE_NPM_TIMEOUT
    - SPACE_PIP_PATH_JSON
    - SPACE_PIP_PATH_FILE
    - SPACE_PIP_TIMEOUT
    - SPACE_MAKE_PATH_JSON
    - SPACE_MAKE_PATH_FILE
    - SPACE_MAKE_TIMEOUT
  replaced by `SPACE_HOOKS_COLLECTION_JSON` or `SPACE_HOOKS_COLLECTION_FILE` to allow more hooks configuration with
  several versions of composer, npm, pip, or make.
  - Fix Quota creation
  - Update to Symfony 7.1, Symfony 6.4 still supported

## [1.0.0-beta43] - 2024-05-08
### Beta Release
- Huge and massive update. The last version before the RC1.
- Use last available libraries
  - Use `States` v6.2+ 
  - Use `East Common` v2.10+ 
  - Use `East PaaS` v3.3.1+
    - Use `DefaultsCompiler`, `DefaultsBag` instead `$storageIdentifier`, `$defaultStorageSize`, `$ociRegistryConfig`.
      - Update `JobSetDefaults` and `NewJobSetDefaults`.
    - Allow configuration of heterogeneous clusters, Job's namespace and hierarchical namespaces are now managed from
      the cluster's definition and not from the account and the job.
    - Remove namespaces information from `Job` and `JobUnit`.
    - Remove namespaces information from `CompiledDeploymentInterface` (and remove `foreachNamespace`).
    - See the `East PaaS` changelog to get other update information
  - Use `scheb/2fa` v7.3+
- Allow an account to have several managed environment.
  - Rename cookbook `AccountCredential` to `AccountEnvironment`.
  - Rename cookbook `AccountInstall` to `AccountEnvironmentInstall`.
  - Rename cookbook `AccountReinstall` to `AccountEnvironmentReinstall`.
  - The 'first' environment is not longer created automatically at accounts' creations.
  - Management of environment in account's setting.
  - `SubscriptionPlan` requires the count of allowed managed environments (minimum is 1)
  - Add steps to load `SubscriptionPlan` of an account.
  - Add `AccountEnvironmentResume` as DTO to manage Accounts' environments.
  - When an environment is created, the kubernetes's namespace is computed from the account name and the environment 
    name.
    - If the namespace already exist and the namespace is not owned by the account, an error is thrown, Space will no 
      longer increment until an available namespace. (Change in `CreateNamespace` steps and simplify the code).
  - If the count of environments exceed the count of allowed environments, an error is also thrown.
  - Fix issue with docker secret not created into the environment namespace introduced in last Space version.
- Improve `additional steps` of `East PaaS` cookbook to be more readable.
- Rename all property and variables `environmentName` to `envName` to have consistency with the library `East PaaS`.
- Rename `PersistedVariable` to `ProjectPersistedVariable`, to clarify difference with `AccountPersistedVariable`.
- Improve API endpoints to be consistency.
  - Add admin endpoints to install/reinstall and refresh quotas, registries or environments.
  - Add user endpoints to install/reinstall environments.
- Improve HTTP endpoints to be consistency.
- Support encryption of secrets in persisted variables (`AccountPersistedVariable` and `ProjectPersistedVariable`)
  - Build on `Teknoo\East\Paas\Contracts\Security\EncryptionInterface`, but with differents keys to those used for
    messages between workers.
  - Add `encryptionAlgorithm` property to `AccountPersistedVariable` and `ProjectPersistedVariable`.
  - Add `Teknoo\Space\Contracts\Object\EncryptableVariableInterface`
  - Non encrypted secret are automatically encrypted at next save.
  - The Web interface / API cannot decrypt secret, only the `new_job` worker.
  - Encryption is optional, but if the secret encryption is disable, encrypted secret are unusable.
- Improve functionals tests (with Behat) to support all API operations in all availables configurations. 
- Some other templates and translations fixes.
- Update documentation.
- Fix issues with big histories in account or project.

## [1.0.0-beta42] - 2024-03-12
### Beta Release
- Split `AccountCredential` (and loader, writers, steps and cookbook) to `AccountCredential` and `AccountRegistry`
  - `AccountCredential` keeps only credentials about a cluster
  - `AccountRegistry` keeps only credentials about registry OCI
  - Fix `JobSetDefaults` with this new architecture
- Private OCI registry are into dedicated namespace, not included into the client namespace
  - Add `SPACE_KUBERNETES_REGISTRY_ROOT_NAMESPACE` env to set the prefix namespace for this new namespace
- Fix `CreateNamespace` to find an available namespaces available on each clusters. Warning, the namespace must be
  the same on each clusters, if a previous namespace already exist on a cluster, it will be not deleted.
- Complete `Config\Cluster` to define the cluster able to host private registry
  - Add `getKubernetesRegistryClient` method to get a clone of kubernetes client dedicated to registry operations, 
    in the dedicated namespace.

## [1.0.0-beta41] - 2024-03-08
### Beta Release
- Add requests and limits on cpu and memory to accounts'registries deployments. 
  This customized with following envs vars:
  - `SPACE_OCI_REGISTRY_REQUESTS_CPU` : (string) vcore requests for the registry `10m` by default. *Optional*
  - `SPACE_OCI_REGISTRY_REQUESTS_MEMORY` : (string) memory requests for the registry `30Mi` by default. *Optional*
  - `SPACE_OCI_REGISTRY_LIMITS_CPU` : (string) vcore limits, `100m` by default. *Optional*
  - `SPACE_OCI_REGISTRY_LIMITS_MEMORY` : (string) memory limits `256Mi` by default. *Optional*
  - `SPACE_OCI_REGISTRY_URL` : (string) url for each private registry of each account.

## [1.0.0-beta40] - 2024-03-07
### Beta Release
- Fix issue with relative resources requires :
  - % of initial quota capacity and not remaining capacity
- Fix issue when a soft quota is relative to the hard limit
- Fix template url to got to a project when the user is an admin 

## [1.0.0-beta39] - 2024-03-07
### Beta Release
- Fix issue with `JobStart` cookbook requires the cluster catalog (not available here)
  - Complete NewJob to pass from the webserver required value of the cluster catalog, needed by `JobStart` cookbook.

## [1.0.0-beta38] - 2024-03-06
### Beta Release
- Update to Teknoo East PaaS 2.8+ :
  - Add support of quotas into an Account
  - Quota are defined for each account, quotas are categorized
    - `compute`, like `cpu` or `gpu`
    - `memory`, like `memory`, `storage`, `huge-page`
  - Add `quotas` section under `paas` section in the deployment file (`.paas.yaml`)
  - Add `resource` section for each container, with `require` and `limit`
    - `require`: minimum fraction of resources types required to be started
    - `limit`: maximum fraction of resources types for the container. (For a container, not a replicas)
    - If containers have no resources defined (or not fully defined), East PaaS, thanks to its `ResourceManager` will
      share remaining resources to containers.
    - requirements and limits can be relative (a % of the quota's capacity)
  - If the sum of requirements exceed the quota, the deployment compilation will be failed and will never be executed.
- Support administration of quotas in account. (Not available from user's interface, only from the admin interface)
- Support `ResourceQuota` in the kubernetes cluster, and update them from Space admin interface for an account
  - Quota are automatically during the account creation (from the Space admin interface, or during a subscription)
  - Admin can refresh Quota in the Kubernetes cluster from the Space admin interface
- Add `subscriptionPlan` into `AccountData` and `SubscriptionPlan` and its catalog as config objetcts 
  (like `ClusterConfig` and `ClusterCatalog`).
  - A SubscriptionPlan is only a string id, an human readable name and the set of quota to apply to account.
- Add `SetQuota` step into Account creation and editing, to update account's quota from the Subscription plan catalog.
  - Admin can update account's plan, not users cannot do it.
- Rework PHP DI definitions and migrate dynamics var env's value from a json or a file into a 
  dedicated file `di.variables.from.envs.php`.
- Add env vars `SPACE_CLUSTER_CATALOG_JSON` or `SPACE_CLUSTER_CATALOG_FILE` to define clusters 
  catalog introduced into the version `1.0.0-beta37`.
- Add env var `SPACE_SUBSCRIPTION_PLAN_CATALOG_JSON` or `SPACE_SUBSCRIPTION_PLAN_CATALOG_FILE` to define subscription 
  plan with quota.
- Update README 
- Complete and clean tests

## [1.0.0-beta37] - 2024-02-26
### Beta Release
- Use East PaaS 2.7 with defaults instead extra
- Use East Common 2.9 and `VisitableTrait` and Recipe's loop feature
- Prepare Space to manage several cluster :
  - a Space's Project can use external Kubernetes Cluster, but Space app could manage 
    (create namespace and service account, install OCI registry, display dashboard, ...) only one cluster.
- Centralize cluster's configuration into a new object, generated at bootstrap `Teknoo\Space\Object\Config\Cluster`, 
  grouped into a `Teknoo\Space\Object\Config\ClusterCatalog`.
  - Several clusters will can defined via the DI key `teknoo.space.cluster_catalog.definitions`. (In a future version)
  - Cookbooks has been updated to use now this catalog.
  - The source repository and the image registry **MUST** be shared between all clusters
  - **This work will still take several versions of Space**, We will work on :
    - Account installation to support clusters with an external OCI registry. 
      - Not install an oci/docker registry in the client's namespace, just put the dockerconfig secret)
    - the rework of `AccountCredentials` :
      - split clusters's config and registry's config.
      - Encrypt token and password
    - on the encryption of secret persisted variables

## [1.0.0-beta36] - 2024-02-22
### Beta Release
- Support of locked cluster
- Clean some code
- use East PaaS 2.6:
    - `ClusterCredentialsType` does not show `password` and `token`. And add a non mapped `clear` field to force empty field
      (else the empty value is ignored to avoid to lost data).
    - `SshIdentityType` does not show `privateKey`. And add a non mapped `clear` field to force empty field
      (else the empty value is ignored to avoid to lost data).
    - `XRegistryAuthType` does not show `password`. And add a non mapped `clear` field to force empty field
      (else the empty value is ignored to avoid to lost data).
    - Add `locked` status to cluster to forbid cluster's update when the form's option `allowEditingOfLocked` is not set to
      true. For admin's forms, the attribute is at true.
      - This new attribute has no impact to deployment, only for CRUD operations
- Prepare wallet of account credentials, to support severals default clusters in one Space Instance.

## [1.0.0-beta35] - 2024-02-19
### Beta Release
- Support Symfony 7.0 (Space stay compliant and tested with Symfony 6.4 (LTS Version)).
  - An LTS version shipped with Symfony 6.4 will be also shipped
- Enable PHP 8.3 in docker's devs file.
- Disable Blackfire (not compliant with XDebug with PHP 8.3).
- Enable Redis

## [1.0.0-beta34] - 2024-02-16
### Beta Release
- Upgrade to MongoDb 7.
- Update libs.
- Factorize pods' transcribers for Kubernetes.
- Fix issue in `Job`'s `History` sorting :
  - `Final` must be at the top of the history chain.
- Limit verbose about `extra` in history.

## [1.0.0-beta33] - 2024-02-13
### Beta Release
- Add HTTP API to manage projects and its settings or account's settings. (An API for Job is already present).
- Add HTTP API, dedicated to Admin, to manages accounts and users or projects.
- Remove last "billing" reference : in `BillingName` to `LegalName`.
- Remove `formOptions` empty entry in routes.
- Fix cookbooks's ingredients.
- Require Recipe 4.6.1
- Require East Foundation 7.6.1
- Require East Common 2.8
- Require East PaaS 2.5
- Clean Composer.json

## [1.0.0-beta32] - 2024-02-06
### Beta Release
- Require East Foundation 4.6
- Require East Foundation 7.5.1
- Require East PaaS 2.4.9

## [1.0.0-beta31] - 2024-02-01
### Beta Release
- Add Recovery acces method when user lost its password : The TOTP still enabled when to recover an access
- Require East Common 2.7
- Require East Foundation 7.5.1
- Require East Pass 2.4.7
- Common `DatesService` is deprecated, use Foundation's `DatesService` instead

## [1.0.0-beta30] - 2024-01-17
### Beta Release
- Improve HTML Header title to be more understandable by user
- Fix breadcrumb texts
- Fix some issue in make command
- Fix issues with JWT TTL env var not parsed to int
- Improve twig blocks' names about breadcrumb
- Fix several issues with breadcrumb text
- Enable support of 2FA with a generic TOTP and not only Google Authenticator
  - Add the env key `SPACE_2FA_PROVIDER` to select the 2FA provider to use. Users already configured with a provider
    still use it.
- Fix some issue in 2FA form
- Fix subscription form and search form to use bootstrap 5 instead of bootstrap 4
- Fix some issue in search 
- Improve DI parameters about default values
- Set Mailer's DSN in env var, no longer concatened in the configuration
- Fix Docker Compose stack to embed also workers
- Support of OAuth2 authentication with providers (only one provider can be used at a time) :
  - Digital Ocean
  - Github
  - Gitlab
  - Google
  - Jira
  - Microsoft
  - Generic OAuth will be planned soon
    - Add the env key `OAUTH_SERVER_TYPE` to set the provider type
    - Add the env key `OAUTH_ENABLED` to show oauth button in sign in form
    - Add the env key `OAUTH_SERVER_URL` to set the server (for gitlab only)
    - Add the env key `OAUTH_CLIENT_ID` and `OAUTH_CLIENT_SECRET` to authenticate the server to use the OAuth API

## [1.0.0-beta29] - 2024-01-09
### Beta Release
- Add bin/config.sh script to help to configure a fresh Space installation 
- Update Makefile to add an help command
  - Add `make help` to display commands
  - Add `make verify`
  - Add `make install` to install Space in production mode
  - Add `make dev-install` to install Space in development mode
  - Rename `make` to `make update` to update Space's vendor in upper or lowest mode
  - Add `make config` to configure a fresh Space installation
  - Add `make build`, `make start`, `make stop` and `make restart` to manage docker-compose stack
  - Other commands stay unchanged
- Fix Symfony Messenger configuration to use default `memory` transport until Space is not configured to avoid error
- Add `.env.local.unsecure.dist` to help developpers / ops to configure a Space without Symfony Secret
  - use `.env.local.dist` to configure non-secrets envs.
  - Use `make config` to configure Space with Symfony secrets

## [1.0.0-beta28] - 2023-12-06
### Beta Release
- Update to Symfony 6.4.1 and Teknoo East Foundation 7.4
- Fix core dump issue in php dev image, bug from xdebug
- Use Sleep service from East Foundation

## [1.0.0-beta27] - 2023-11-29
### Beta Release
- Update to Symfony 6.4
- Fix deprecations in Symfony
- Improve cookies securities
- Update Teknoo Libs
- Update devs libraries

## [1.0.0-beta26] - 2023-11-29
### Beta Release
- Update to East PaaS 2.4.2
- Add aliases on ingress
- Update to last libraries bugfixes

## [1.0.0-beta25] - 2023-11-26
### Beta Release
- Update to East Common 2.6
- Fix bug in search :
  - routes to allow search in lists
  - use regex instead text matching
- refresh credentials in project refresh also registry url
- fix issue in account creation
- support doctrine odm 2.6.1

## [1.0.0-beta24] - 2023-11-17
### Beta Release
- Update to East Common 2.5
  - Add `teknoo.space.rendering.clean_html` for Space's routes and set to true all theses parameters :
    - `teknoo.east.common.rendering.clean_html`
    - `teknoo.east.common.admin.rendering.clean_html`
    - `teknoo.east.paas.admin.rendering.clean_html`
    - `teknoo.east.paas.rendering.clean_html`
    - `teknoo.space.rendering.clean_html`
- Apply last patchs from Symfony
  
## [1.0.0-beta23] - 2023-11-12
### Beta Release
- Update to East PaaS 2.3
- Support of Symfony Console in hook:
  - `SPACE_SFCONSOLE_PATH_JSON` : (json string) path to the symfony console executable. `bin/console` by default
    - `SPACE_SFCONSOLE_PATH_FILE` : file alternative
  - `SPACE_SFCONSOLE_TIMEOUT` : (int) max time allowed to install dependencies via symfony console.
      Can't be bigger than `SPACE_WORKER_TIME_LIMIT`. *Optional*
- QA
- Prevent somes bugs in container

## [1.0.0-beta22] - 2023-11-10
### Beta Release
- Update to Symfony 6.3.8 to fix CVEs
- Update to last Teknoo Recipe and East Common to fix some bug
- Minify assets

## [1.0.0-beta21] - 2023-10-30
### Beta Release
- Update to last Symfony 6.3 minor

## [1.0.0-beta20] - 2023-10-22
### Beta Release
- Update to last Symfony 6.3 minor
- Update libs and use Teknoo Kubernetes 1.4.3

## [1.0.0-beta19] - 2023-10-08
### Beta Release
- Fix some boostrap classes in templates
- Update libs and use East Common 2.3.2
- Prevent issue with subforms in javascripts

## [1.0.0-beta18] - 2023-10-01
### Beta Release
- Support Hooks running in a dedicated container

## [1.0.0-beta17] - 2023-09-20
### Beta Release
- Replace `SPACE_COMPOSER_PATH`, `SPACE_NPM_PATH`, `SPACE_PIP_PATH` and `SPACE_MAKE_PATH` to
  - `SPACE_COMPOSER_PATH_JSON` (need a json array or json string) or `SPACE_COMPOSER_PATH_FILE` 
  - `SPACE_NPM_PATH_JSON` (need a json array or json string) or `SPACE_NPM_PATH_FILE`
  - `SPACE_PIP_PATH_JSON` (need a json array or json string) or `SPACE_PIP_PATH_FILE`
  - `SPACE_MAKE_PATH_JSON` (need a json array or json string) or `SPACE_MAKE_PATH_FILE` 
  - Path can be an array and not only a binary path, to use a docker container instead of a binary.

## [1.0.0-beta16] - 2023-09-16
### Beta Release
- Improve error manager when encryption configuration differe between servers and workers

## [1.0.0-beta15] - 2023-09-13
### Beta Release
- Add encryption capacities in messages between servers and agents or workers
  - Define env var `TEKNOO_PAAS_SECURITY_ALGORITHM` (with `rsa` ou `dsa`).
  - Define env var `TEKNOO_PAAS_SECURITY_PRIVATE_KEY` to define the private key location in the filesystem (to decrypt).
  - Define env var (optional) `TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE` about the passphrase to unlock the
    private key.
  - Define env var `TEKNOO_PAAS_SECURITY_PUBLIC_KEY` to define the public key location in the filesystem (to encrypt).
- Add #[SensitiveParameter] to prevent leak.

## [1.0.0-beta14] - 2023-08-30
### Beta Release
- Add JWT firewall thanks to `LexikJWTAuthenticationBundle`
  - To get a JWT token from the interface (not allowed directly from the bundle to keep 2FA).
  - Add Plan `UserGetJwtToken` to allow user to get a token
  - JWT token can be passed to the api in HTTP Header or, if the env `SPACE_JWT_ENABLE_IN_QUERY` is at true, 
    in the query, with the `bearer` parameter
- Add API v1 to execute new job on a project, list, get and delete jobs
  - An API to manage projects, projects' variables and secrets is planned for later
  - The API is behind the JWT firewall
  - The API accepts JSON body and URL encoded body
  - The API will be compliant with Swagger later
- Fix open project url from job

## [1.0.0-beta13] - 2023-08-02
### Beta Release
- Improve SendEmail feature

## [1.0.0-beta12] - 2023-07-30
### Beta Release
- Clean some code
- Add mail feature
  - Add support form contact
- Improve template structure

## [1.0.0-beta11] - 2023-07-19
### Beta Release
- Use East PaaS 2.0.3
- Add `letsencrypt' option in ingress's meta to allow lets encrypt generation for yours ingresses

## [1.0.0-beta10] - 2023-07-18
### Beta Release
- Fix venv

## [1.0.0-beta9] - 2023-07-15
### Beta Release
- Fix issue into list of projects

## [1.0.0-beta8] - 2023-07-14
### Beta Release
- Restore PHP-DI Compilation
- Use East PaaS 2.0.1

## [1.0.0-beta7] - 2023-07-13
### Beta Release
- Symfony 6.3.1
- Switch to PaaS Symfony metapackage
- Support PHP-DI 7.0+
- Support Laminas Diactoros 3.0+
- Support Python, PIP, Node, NPM and Make
- Fix missing call to space.js

## [1.0.0-beta6] - 2023-06-26
### Beta Release
- Symfony 6.3.1
- Switch to PaaS Symfony metapackage

## [1.0.0-beta5] - 2023-06-19
### Beta Release
- Fix bug with supports of new hooks.

## [1.0.0-beta4] - 2023-06-14
### Beta Release
- Update Teknoo libs
- Update to PaaS lib 1.8
- Support NPM, PIP and Make
- Replace in the container `app.` prefix to `teknoo.space.`

## [1.0.0-beta3] - 2023-06-07
### Beta3= Release
- Update Teknoo libs
- Require Symfony 6.3 or newer

## [1.0.0-beta2] - 2023-05-22
### Beta2= Release
- Support Statefulsets
- Stateful projects use now a statefulsets instead of a deployment

## [1.0.0-beta1] - 2023-05-16
### Beta1= Release
* First public release, imported from private alpha release
* built on `Teknoo East PaaS`, `Teknoo Kubernetes Client` libraries
  and Symfony components.
* an account represents the top entity (a company, a service, a foundation, an human, etc...
* an account has at least one user.
* an user represent an human.
* an account has projects.
* projects have deployment jobs.
* all projects must be hosted on a Git instance, reachable via the protocoles HTTPS or SSH.
* projects' images are built thanks to Buildah.
* only Kubernetes clusters 1.22+ are supported.
* a job represents a deployment
* a job can provide severals variables to pass to the compiler about the deployment.
    * variables can be persisted to the project to be reused in the future in next deployments.
    * projects can host persisted variables to be used in all next deployments.
    * accounts can host also persisted variables to be used on all deployments of all of this projects if
      they are not already defined in projects.
    * persisted variables can contains secrets.
        * Warning, currently secrets are not visible in Space's web app but they are passed unencrypted to the agents.
* Space is bundled with a Composer hook to build PHP Project. NPM and PIP supports is also planned.
* Space allow any users to subcribe, but it's not manage billings.
  * Subscriptions can be restricted with uniques codes to forbid non selected user to subscribe.
* Space supports 2FA authentication with an TOTP application like Google Authenticator.
