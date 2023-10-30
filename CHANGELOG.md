# Teknoo Software - Space - Change Log

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
