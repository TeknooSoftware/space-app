# Teknoo Software - Space - Change Log

## [1.0.0-beta7] - 2023-07-13
### Beta6 Release
- Symfony 6.3.1
- Switch to PaaS Symfony metapackage
- Support PHP-DI 7.0+
- Support Laminas Diactoros 3.0+
- Support Python, PIP, Node, NPM and Make
- Fix missing call to space.js

## [1.0.0-beta6] - 2023-06-26
### Beta6 Release
- Symfony 6.3.1
- Switch to PaaS Symfony metapackage

## [1.0.0-beta5] - 2023-06-19
### Beta5 Release
- Fix bug with supports of new hooks.

## [1.0.0-beta4] - 2023-06-14
### Beta4 Release
- Update Teknoo libs
- Update to PaaS lib 1.8
- Support NPM, PIP and Make
- Replace in the container `app.` prefix to `teknoo.space.`

## [1.0.0-beta3] - 2023-06-07
### Beta3 Release
- Update Teknoo libs
- Require Symfony 6.3 or newer

## [1.0.0-beta2] - 2023-05-22
### Beta2 Release
- Support Statefulsets
- Stateful projects use now a statefulsets instead of a deployment

## [1.0.0-beta1] - 2023-05-16
### Beta1 Release
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
