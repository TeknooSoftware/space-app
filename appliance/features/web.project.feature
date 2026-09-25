@web
Feature: Web interface to manage account's projects
  In order to manage account's projects
  As a user of an account
  I want to manage my account's projects only

  On a space instance, each allowed users can register a new project on its attached account. A project must hosted on
  a source repository like GIT. Currently Space support only GIT via https and ssh, with tls keys or access token.
  A project can be compiled and pushed to a private OCI registry dedicated to the account and deploy builded containers
  to a cluster / servers (only Kubernetes is currently supported) in a dedicated namespace reserved to the account's
  environment selected on the job deployment.
  The Project's configuration store only informations about get the project from a repository and clusters where deploy
  it, per environment. The project's configuration can store variables. All others informations are stored directly into
  the space.paas.yaml file available at the root of the source repostirory.

  Background:
    Given a Space app instance
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user

  Scenario: From the UI, list projects of my account
    Given a standard project "my project"
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    Then the user obtains a project list:
      | Name       |
      | my project |

  Scenario: From the UI, create a new project on my account
    Given the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to new project page
    Then it obtains a empty project's form
    When it submits the form:
      | field                                                      | value                           |
      | space_project._token                                       | <auto>                          |
      | space_project.project.name                                 | my project                      |
      | space_project.projectMetadata.projectUrl                   | https://my.project.demo         |
      | space_project.project.prefix                               | demo                            |
      | space_project.project.sourceRepository.pullUrl             | https://oauth:token@gitlab.demo |
      | space_project.project.sourceRepository.defaultBranch       | main                            |
      | space_project.project.sourceRepository.identity.name       | git                             |
      | space_project.project.sourceRepository.identity.privateKey |                                 |
      | space_project.project.imagesRegistry.apiUrl                | <auto>                          |
      | space_project.project.imagesRegistry.identity.username     | <auto>                          |
      | space_project.project.imagesRegistry.identity.password     | <auto>                          |
      | space_project.addClusterName                               | Demo Kube Cluster               |
      | space_project.addClusterEnv                                | prod                            |
    Then the project must be persisted
    And the user obtains the form:
      | field                                                      | value                                 |
      | space_project.project.name                                 | my project                            |
      | space_project.projectMetadata.projectUrl                   | https://my.project.demo               |
      | space_project.project.prefix                               | demo                                  |
      | space_project.project.sourceRepository.pullUrl             | https://oauth:token@gitlab.demo       |
      | space_project.project.sourceRepository.defaultBranch       | main                                  |
      | space_project.project.sourceRepository.identity.name       | git                                   |
      | space_project.project.sourceRepository.identity.privateKey |                                       |
      | space_project.project.imagesRegistry.apiUrl                | my-company.registry.demo.teknoo.space |
      | space_project.project.imagesRegistry.identity.username     | my-company-registry                   |
      | space_project.project.imagesRegistry.identity.password     |                                       |
      | space_project.project.clusters.0.name                      | Demo Kube Cluster                     |
      | space_project.project.clusters.0.type                      | kubernetes                            |
      | space_project.project.clusters.0.address                   | https://kubernetes.localhost:12345    |
      | space_project.project.clusters.0.environment.name          | prod                                  |
      | space_project.project.clusters.0.identity.caCertificate    | -----BEGIN CERTIFICATE-----FooBar     |
      | space_project.project.clusters.0.identity.token            |                                       |

  Scenario: From the UI, update a project on my account
    Given a standard project "my project" and a prefix "demo"
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    Then the user obtains a project list:
      | Name       |
      | my project |
    When it opens the project page of "my project"
    Then the user obtains the form:
      | field                                                      | value                                 |
      | space_project.project.name                                 | my project                            |
      | space_project.projectMetadata.projectUrl                   | https://my.project.demo               |
      | space_project.project.prefix                               | demo                                  |
      | space_project.project.sourceRepository.pullUrl             | https://oauth:token@gitlab.demo       |
      | space_project.project.sourceRepository.defaultBranch       | main                                  |
      | space_project.project.sourceRepository.identity.name       | git                                   |
      | space_project.project.sourceRepository.identity.privateKey |                                       |
      | space_project.project.imagesRegistry.apiUrl                | my-company.registry.demo.teknoo.space |
      | space_project.project.imagesRegistry.identity.username     | my-company-registry                   |
      | space_project.project.imagesRegistry.identity.password     |                                       |
      | space_project.project.clusters.0.name                      | Demo Kube Cluster                     |
      | space_project.project.clusters.0.type                      | kubernetes                            |
      | space_project.project.clusters.0.address                   | https://kubernetes.localhost:12345    |
      | space_project.project.clusters.0.environment.name          | prod                                  |
      | space_project.project.clusters.0.identity.caCertificate    | -----BEGIN CERTIFICATE-----FooBar     |
      | space_project.project.clusters.0.identity.token            |                                       |
      | space_project.project.clusters.0.identity.username         |                                       |
    And the form field "space_project.project.clusters.0.identity.username" is read only
    And the form field "space_project.project.clusters.0.identity.caCertificate" is shown for every cluster type
    And the form field "space_project.project.clusters.0.identity.clientKey" is shown for every cluster type
    And the form field "space_project.project.clusters.0.identity.clientCertificate" is shown only for the cluster types "kubernetes"
    And the form field "space_project.project.clusters.0.identity.token" is shown only for the cluster types "kubernetes"
    And the form field "space_project.project.clusters.0.identity.username" is shown only for the cluster types "docker-compose"
    And the form field "space_project.project.clusters.0.address" is labelled "API Address" for the cluster type "kubernetes"
    And the form field "space_project.project.clusters.0.address" is labelled "SSH Address" for the cluster type "docker-compose"
    And the form field "space_project.project.clusters.0.identity.caCertificate" is labelled "CA Certificate" for the cluster type "kubernetes"
    And the form field "space_project.project.clusters.0.identity.caCertificate" is labelled "SSH Host keys (known_hosts)" for the cluster type "docker-compose"
    And the form field "space_project.project.clusters.0.identity.clientKey" is labelled "Client Key" for the cluster type "kubernetes"
    And the form field "space_project.project.clusters.0.identity.clientKey" is labelled "SSH Private key" for the cluster type "docker-compose"
    And the form field "space_project.project.clusters.0.useHierarchicalNamespaces" is hidden
    And the form field "space_project.project.clusters.0.useHierarchicalNamespaces" is disabled
    When it submits the form:
      | field                                                      | value                          |
      | space_project._token                                       | <auto>                         |
      | space_project.project.name                                 | my project 2                   |
      | space_project.projectMetadata.projectUrl                   | https://my2.project.demo       |
      | space_project.project.prefix                               | demo                           |
      | space_project.project.sourceRepository.pullUrl             | https://oauth:tok2@gitlab.demo |
      | space_project.project.sourceRepository.defaultBranch       | main                           |
      | space_project.project.sourceRepository.identity.name       | git                            |
      | space_project.project.sourceRepository.identity.privateKey |                                |
      | space_project.project.imagesRegistry.apiUrl                | <auto>                         |
      | space_project.project.imagesRegistry.identity.username     | <auto>                         |
      | space_project.project.imagesRegistry.identity.password     | <auto>                         |
      | space_project.project.clusters.0.name                      | <auto>                         |
      | space_project.project.clusters.0.type                      | <auto>                         |
      | space_project.project.clusters.0.address                   | <auto>                         |
      | space_project.project.clusters.0.environment.name          | <auto>                         |
      | space_project.project.clusters.0.identity.caCertificate    | <auto>                         |
      | space_project.project.clusters.0.identity.token            | <auto>                         |
    Then the project must be updated
    And the user obtains the form:
      | field                                                      | value                                 |
      | space_project.project.name                                 | my project 2                          |
      | space_project.projectMetadata.projectUrl                   | https://my2.project.demo              |
      | space_project.project.prefix                               | demo                                  |
      | space_project.project.sourceRepository.pullUrl             | https://oauth:tok2@gitlab.demo        |
      | space_project.project.sourceRepository.defaultBranch       | main                                  |
      | space_project.project.sourceRepository.identity.name       | git                                   |
      | space_project.project.sourceRepository.identity.privateKey |                                       |
      | space_project.project.imagesRegistry.apiUrl                | my-company.registry.demo.teknoo.space |
      | space_project.project.imagesRegistry.identity.username     | my-company-registry                   |
      | space_project.project.imagesRegistry.identity.password     |                                       |
      | space_project.project.clusters.0.name                      | Demo Kube Cluster                     |
      | space_project.project.clusters.0.type                      | kubernetes                            |
      | space_project.project.clusters.0.address                   | https://kubernetes.localhost:12345    |
      | space_project.project.clusters.0.environment.name          | prod                                  |
      | space_project.project.clusters.0.identity.caCertificate    | -----BEGIN CERTIFICATE-----FooBar     |
      | space_project.project.clusters.0.identity.token            |                                       |

  Scenario: From the UI, update a project deployed on a managed Docker Compose cluster, its SSH username is read only and kept
    Given an account clusters "Cluster Company" and a slug "my-company-cluster" on docker compose
    And an account environment on "Cluster Company" for the environment "prod"
    And a standard project "my project" and a prefix "demo" on "Cluster Company" for "prod"
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    When it opens the project page of "my project"
    Then the user obtains the form:
      | field                                                    | value                                            |
      | space_project.project.name                               | my project                                       |
      | space_project.project.clusters.0.name                    | Cluster Company                                  |
      | space_project.project.clusters.0.type                    | docker-compose                                   |
      | space_project.project.clusters.0.address                 | ssh://deployer@docker-host.my-company-cluster.behat:22 |
      | space_project.project.clusters.0.environment.name        | prod                                             |
      | space_project.project.clusters.0.identity.username       | deployer                                         |
    And the form field "space_project.project.clusters.0.identity.username" is read only
    When it submits the form:
      | field                                                        | value                          |
      | space_project._token                                         | <auto>                         |
      | space_project.project.name                                   | my project 2                   |
      | space_project.projectMetadata.projectUrl                     | https://my2.project.demo       |
      | space_project.project.prefix                                 | demo                           |
      | space_project.project.sourceRepository.pullUrl               | https://oauth:tok2@gitlab.demo |
      | space_project.project.sourceRepository.defaultBranch         | main                           |
      | space_project.project.sourceRepository.identity.name         | git                            |
      | space_project.project.sourceRepository.identity.privateKey   |                                |
      | space_project.project.imagesRegistry.apiUrl                  | <auto>                         |
      | space_project.project.imagesRegistry.identity.username       | <auto>                         |
      | space_project.project.imagesRegistry.identity.password       | <auto>                         |
      | space_project.project.clusters.0.name                        | <auto>                         |
      | space_project.project.clusters.0.type                        | <auto>                         |
      | space_project.project.clusters.0.address                     | <auto>                         |
      | space_project.project.clusters.0.namespace                   | <auto>                         |
      | space_project.project.clusters.0.environment.name            | <auto>                         |
      | space_project.project.clusters.0.identity.caCertificate      | <auto>                         |
      | space_project.project.clusters.0.identity.clientCertificate  | <auto>                         |
      | space_project.project.clusters.0.identity.clientKey          | <auto>                         |
      | space_project.project.clusters.0.identity.token              | <auto>                         |
      | space_project.project.clusters.0.identity.username           | <auto>                         |
    Then the project must be updated
    And the SSH username of the last project's cluster is "deployer"
    And the user obtains the form:
      | field                                              | value        |
      | space_project.project.name                         | my project 2 |
      | space_project.project.clusters.0.identity.username | deployer     |
    And the form field "space_project.project.clusters.0.identity.username" is read only

  Scenario: From the UI, as Admin, update the SSH username of a project deployed on a managed Docker Compose cluster
    Given an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And an account clusters "Cluster Company" and a slug "my-company-cluster" on docker compose
    And an account environment on "Cluster Company" for the environment "prod"
    And a standard project "my project" and a prefix "demo" on "Cluster Company" for "prod"
    And the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    And it is redirected to the dashboard
    When it goes to the admin project page
    Then the user obtains the form:
      | field                                              | value           |
      | space_project.project.name                         | my project      |
      | space_project.project.clusters.0.name              | Cluster Company |
      | space_project.project.clusters.0.identity.username | deployer        |
    And the form field "space_project.project.clusters.0.identity.username" is editable
    And the form field "space_project.project.clusters.0.identity.caCertificate" is shown for every cluster type
    And the form field "space_project.project.clusters.0.identity.clientKey" is shown for every cluster type
    And the form field "space_project.project.clusters.0.identity.clientCertificate" is shown only for the cluster types "kubernetes"
    And the form field "space_project.project.clusters.0.identity.token" is shown only for the cluster types "kubernetes"
    And the form field "space_project.project.clusters.0.identity.username" is shown only for the cluster types "docker-compose"
    And the form field "space_project.project.clusters.0.address" is labelled "API Address" for the cluster type "kubernetes"
    And the form field "space_project.project.clusters.0.address" is labelled "SSH Address" for the cluster type "docker-compose"
    And the form field "space_project.project.clusters.0.identity.caCertificate" is labelled "CA Certificate" for the cluster type "kubernetes"
    And the form field "space_project.project.clusters.0.identity.caCertificate" is labelled "SSH Host keys (known_hosts)" for the cluster type "docker-compose"
    And the form field "space_project.project.clusters.0.identity.clientKey" is labelled "Client Key" for the cluster type "kubernetes"
    And the form field "space_project.project.clusters.0.identity.clientKey" is labelled "SSH Private key" for the cluster type "docker-compose"
    When it submits the form:
      | field                                                        | value                          |
      | space_project._token                                         | <auto>                         |
      | space_project.project.name                                   | my project 2                   |
      | space_project.projectMetadata.projectUrl                     | https://my2.project.demo       |
      | space_project.project.prefix                                 | demo                           |
      | space_project.project.sourceRepository.pullUrl               | https://oauth:tok2@gitlab.demo |
      | space_project.project.sourceRepository.defaultBranch         | main                           |
      | space_project.project.sourceRepository.identity.name         | git                            |
      | space_project.project.sourceRepository.identity.privateKey   |                                |
      | space_project.project.imagesRegistry.apiUrl                  | <auto>                         |
      | space_project.project.imagesRegistry.identity.username       | <auto>                         |
      | space_project.project.imagesRegistry.identity.password       | <auto>                         |
      | space_project.project.clusters.0.name                        | <auto>                         |
      | space_project.project.clusters.0.type                        | <auto>                         |
      | space_project.project.clusters.0.address                     | <auto>                         |
      | space_project.project.clusters.0.namespace                   | <auto>                         |
      | space_project.project.clusters.0.environment.name            | <auto>                         |
      | space_project.project.clusters.0.identity.caCertificate      | <auto>                         |
      | space_project.project.clusters.0.identity.clientCertificate  | <auto>                         |
      | space_project.project.clusters.0.identity.clientKey          | <auto>                         |
      | space_project.project.clusters.0.identity.token              | <auto>                         |
      | space_project.project.clusters.0.identity.username           | paas                           |
      | space_project.project.clusters.0.locked                      | 1                              |
    Then the project must be updated
    And the SSH username of the last project's cluster is "paas"

  Scenario: From the UI, as Admin, update a project on a cluster using hierarchical namespaces, the value is kept hidden
    Given an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And a cluster supporting hierarchical namespace
    And a standard project "my project" and a prefix "demo"
    And the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    And it is redirected to the dashboard
    When it goes to the admin project page
    Then the user obtains the form:
      | field                                 | value             |
      | space_project.project.name            | my project        |
      | space_project.project.clusters.0.name | Demo Kube Cluster |
    And the form field "space_project.project.clusters.0.useHierarchicalNamespaces" is hidden
    When it submits the form:
      | field                                                        | value                          |
      | space_project._token                                         | <auto>                         |
      | space_project.project.name                                   | my project 2                   |
      | space_project.projectMetadata.projectUrl                     | https://my2.project.demo       |
      | space_project.project.prefix                                 | demo                           |
      | space_project.project.sourceRepository.pullUrl               | https://oauth:tok2@gitlab.demo |
      | space_project.project.sourceRepository.defaultBranch         | main                           |
      | space_project.project.sourceRepository.identity.name         | git                            |
      | space_project.project.sourceRepository.identity.privateKey   |                                |
      | space_project.project.imagesRegistry.apiUrl                  | <auto>                         |
      | space_project.project.imagesRegistry.identity.username       | <auto>                         |
      | space_project.project.imagesRegistry.identity.password       | <auto>                         |
      | space_project.project.clusters.0.name                        | <auto>                         |
      | space_project.project.clusters.0.type                        | <auto>                         |
      | space_project.project.clusters.0.address                     | <auto>                         |
      | space_project.project.clusters.0.namespace                   | <auto>                         |
      | space_project.project.clusters.0.useHierarchicalNamespaces   | <auto>                         |
      | space_project.project.clusters.0.environment.name            | <auto>                         |
      | space_project.project.clusters.0.identity.caCertificate      | <auto>                         |
      | space_project.project.clusters.0.identity.clientCertificate  | <auto>                         |
      | space_project.project.clusters.0.identity.clientKey          | <auto>                         |
      | space_project.project.clusters.0.identity.token              | <auto>                         |
      | space_project.project.clusters.0.identity.username           | <auto>                         |
      | space_project.project.clusters.0.locked                      | 1                              |
    Then the project must be updated
    And the last project's cluster uses hierarchical namespaces

  Scenario: From the UI, the hierarchical namespaces switch of a project's cluster is shown when enabled
    Given the hierarchical namespaces field is shown in the web interface
    And a standard project "my project" and a prefix "demo"
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    When it opens the project page of "my project"
    Then the user obtains the form:
      | field                                 | value             |
      | space_project.project.name            | my project        |
      | space_project.project.clusters.0.name | Demo Kube Cluster |
    And the form field "space_project.project.clusters.0.useHierarchicalNamespaces" is displayed as a checkbox

  Scenario: From the UI, open a non-owned project and get an error
    Given a standard project "my project"
    And an account for "My Firm" with the account namespace "my-firm"
    And a user, called "Hanin" "Roger" with the "hanin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the user is signed in with "hanin@teknoo.space" and the password "Test2@Test"
    And It goes to project page of "my project" of "My Company"
    Then the user must have a 403 error

  Scenario: From the UI, delete a project
    Given a standard project "my project"
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    Then the user obtains a project list:
      | Name       |
      | my project |
    When It goes to delete the project "my project" of "My Company"
    Then the user obtains a project list:
      | Name |

  Scenario: From the UI, delete a non-owned project and get an error
    Given a standard project "my project"
    And an account for "My Firm" with the account namespace "my-firm"
    And a user, called "Hanin" "Roger" with the "hanin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the user is signed in with "hanin@teknoo.space" and the password "Test2@Test"
    And It goes to delete the project "my project" of "My Company"
    Then the user must have a 403 error
    And the project is not deleted
