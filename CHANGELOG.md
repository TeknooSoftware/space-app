# Teknoo Software - Space - Change Log

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
  - Add Cookbook `UserGetJwtToken` to allow user to get a token
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
