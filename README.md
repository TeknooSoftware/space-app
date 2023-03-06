Teknoo Software - Space
=======================

[![Latest Stable Version](https://poser.pugx.org/teknoo/states/v/stable)](https://packagist.org/packages/teknoo/space-app)
[![Latest Unstable Version](https://poser.pugx.org/teknoo/states/v/unstable)](https://packagist.org/packages/teknoo/space-app)
[![Total Downloads](https://poser.pugx.org/teknoo/space-app/downloads)](https://packagist.org/packages/teknoo/space-app)
[![License](https://poser.pugx.org/teknoo/space-app/license)](https://packagist.org/packages/teknoo/space-app)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)

Space is a `Platform as a Service` application built on `Teknoo East PaaS`, `Teknoo Kubernetes Client` libraries
and Symfony components. The application is multi-account, multi-users and multi-projects, to build and deploy projects 
on dedicated containerized platforms on Kubernetes cluster.

This is the `Standard` version of Space. It is released under MIT licence. This version includes :

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

A free support is available by Github issues of this repository.
About priority support, please contact us at <contact@teknoo.software>.
A commercial `Enterprise` version is planned with some additional features.

Support this project
---------------------

This project is free and will remain free, but it is developed on my personal time. 
If you like it and help me maintain it and evolve it, don't hesitate to support me on 
[Patreon](https://patreon.com/teknoo_software).
Thanks :) Richard. 

Installation & Requirements
---------------------------

This library requires :

    * PHP 8.2+
    * A PHP autoloader (Composer is recommended)
    * Teknoo/Immutable
    * Teknoo/States
    * Teknoo/Recipe
    * Teknoo/East-Foundation
    * Teknoo/space-app
    * Teknoo/Kubernetes Clent
    * Symfony 6.2+
    * Flysystem
    * Buildah

Environnements variables configuration
--------------------------------------

Credits
-------
Richard Déloge - <richard@teknoo.software> - Lead developer.
Teknoo Software - <https://teknoo.software>

About Teknoo Software
---------------------
**Teknoo Software** is a PHP software editor, founded by Richard Déloge.
Teknoo Software's goals : Provide to our partners and to the community a set of high quality services or software,
 sharing knowledge and skills.

License
-------
Space is licensed under the XXX License - see the licenses folder for details

Contribute :)
-------------

You are welcome to contribute to this project. [Fork it on Github](CONTRIBUTING.md)
