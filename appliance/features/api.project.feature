Feature: API endpoints to manage account's projects
  In order to manage account's projects
  As an user of an account
  I want to manage my account's projects only

  On a space instance, each allowed users can register a new project on its attached account. A project must hosted on
  a source repository like GIT. Currently Space support only GIT via https and ssh, with tls keys or access token.
  A project can be compiled and pushed to a private OCI registry dedicated to the account and deploy builded containers
  to a cluster / servers (only Kubernetes is currently supported) in a dedicated namespace reserved to the account's
  environment selected on the job deployment.
  The Project's configuration store only informations about get the project from a repository and clusters where deploy
  it, per environment. The project's configuration can store variables. All others informations are stored directly into
  the space.paas.yaml file available at the root of the source repostirory.

  Scenario: From the API, list owned projects
    Given A Space app instance
    And A memory document database
    And an account for "My First Company" with the account namespace "my-first-company"
    And an user, called "Albert" "Jean" with the "albert@teknoo.space" with the password "Test2@Test"
    And "5" standard projects "project X" and a prefix "a-prefix"
    And an account for "My Other Company" with the account namespace "my-other-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And "5" standard projects "other project X" and a prefix "other-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to list of projects
    Then get a JSON reponse
    And is a serialized collection of "5" items on "1" pages
    And the a list of serialized owned projects

  Scenario: From the API, create a project, via a request with a form url encoded body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a project:
      | field                                                       | value                                 |
      | space_project.project.name                                  | Behats Test                           |
      | space_project.projectMetadata.projectUrl                    | https://behat.tests                   |
      | space_project.project.prefix                                | behat-test                            |
      | space_project.project.sourceRepository.pullUrl              | https://oauth:foo@gitlab.teknoo.space |
      | space_project.project.sourceRepository.defaultBranch        | master                                |
      | space_project.project.sourceRepository.identity.name        | git                                   |
      | space_project.project.sourceRepository.identity.privateKey  |                                       |
      | space_project.project.imagesRegistry.apiUrl                 | registry.teknoo.space                 |
      | space_project.project.imagesRegistry.identity.auth          |                                       |
      | space_project.project.imagesRegistry.identity.username      | teknoo-software                       |
      | space_project.project.imagesRegistry.identity.password      | azertyy                               |
      | space_project.project.clusters.0.name                       | Demo Kube Cluster                     |
      | space_project.project.clusters.0.type                       | kubernetes                            |
      | space_project.project.clusters.0.address                    | https://kubernetes.localhost:12345    |
      | space_project.project.clusters.0.environment.name           | prod                                  |
      | space_project.project.clusters.0.identity.caCertificate     | -----BEGIN CERTIFICATE-----           |
      | space_project.project.clusters.0.identity.clientCertificate |                                       |
      | space_project.project.clusters.0.identity.clientKey         |                                       |
      | space_project.project.clusters.0.identity.token             | fooBar                                |
    Then get a JSON reponse
    And the serialized created project "Behats Test"
    And there is a project in the memory for this account

  Scenario: From the API, create a project, via a request with a json body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a project with a json body:
      | field                                         | value                                 |
      | project.name                                  | Behats Test                           |
      | projectMetadata.projectUrl                    | https://behat.tests                   |
      | project.prefix                                | behat-test                            |
      | project.sourceRepository.pullUrl              | https://oauth:foo@gitlab.teknoo.space |
      | project.sourceRepository.defaultBranch        | master                                |
      | project.sourceRepository.identity.name        | git                                   |
      | project.sourceRepository.identity.privateKey  |                                       |
      | project.imagesRegistry.apiUrl                 | registry.teknoo.space                 |
      | project.imagesRegistry.identity.auth          |                                       |
      | project.imagesRegistry.identity.username      | teknoo-software                       |
      | project.imagesRegistry.identity.password      | azertyy                               |
      | project.clusters.0.name                       | Demo Kube Cluster                     |
      | project.clusters.0.type                       | kubernetes                            |
      | project.clusters.0.address                    | https://kubernetes.localhost:12345    |
      | project.clusters.0.environment.name           | prod                                  |
      | project.clusters.0.identity.caCertificate     | -----BEGIN CERTIFICATE-----           |
      | project.clusters.0.identity.clientCertificate |                                       |
      | project.clusters.0.identity.clientKey         |                                       |
      | project.clusters.0.identity.token             | fooBar                                |
    Then get a JSON reponse
    And the serialized created project "Behats Test"
    And there is a project in the memory for this account

  Scenario: From the API, create a project exceeding the allowed capacity, via a request with a form url encoded body
  and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And "3" standard projects "other project X" and a prefix "other-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a project:
      | field                                                       | value                                 |
      | space_project.project.name                                  | Behats Test                           |
      | space_project.projectMetadata.projectUrl                    | https://behat.tests                   |
      | space_project.project.prefix                                | behat-test                            |
      | space_project.project.sourceRepository.pullUrl              | https://oauth:foo@gitlab.teknoo.space |
      | space_project.project.sourceRepository.defaultBranch        | master                                |
      | space_project.project.sourceRepository.identity.name        | git                                   |
      | space_project.project.sourceRepository.identity.privateKey  |                                       |
      | space_project.project.imagesRegistry.apiUrl                 | registry.teknoo.space                 |
      | space_project.project.imagesRegistry.identity.auth          |                                       |
      | space_project.project.imagesRegistry.identity.username      | teknoo-software                       |
      | space_project.project.imagesRegistry.identity.password      | azertyy                               |
      | space_project.project.clusters.0.name                       | Demo Kube Cluster                     |
      | space_project.project.clusters.0.type                       | kubernetes                            |
      | space_project.project.clusters.0.address                    | https://kubernetes.localhost:12345    |
      | space_project.project.clusters.0.environment.name           | prod                                  |
      | space_project.project.clusters.0.identity.caCertificate     | -----BEGIN CERTIFICATE-----           |
      | space_project.project.clusters.0.identity.clientCertificate |                                       |
      | space_project.project.clusters.0.identity.clientKey         |                                       |
      | space_project.project.clusters.0.identity.token             | fooBar                                |
    Then get a JSON reponse
    But an 400 error

  Scenario: From the API, create a project exceeding the allowed capacity, via a request with a json body
  and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And "3" standard projects "other project X" and a prefix "other-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a project with a json body:
      | field                                         | value                                 |
      | project.name                                  | Behats Test                           |
      | projectMetadata.projectUrl                    | https://behat.tests                   |
      | project.prefix                                | behat-test                            |
      | project.sourceRepository.pullUrl              | https://oauth:foo@gitlab.teknoo.space |
      | project.sourceRepository.defaultBranch        | master                                |
      | project.sourceRepository.identity.name        | git                                   |
      | project.sourceRepository.identity.privateKey  |                                       |
      | project.imagesRegistry.apiUrl                 | registry.teknoo.space                 |
      | project.imagesRegistry.identity.auth          |                                       |
      | project.imagesRegistry.identity.username      | teknoo-software                       |
      | project.imagesRegistry.identity.password      | azertyy                               |
      | project.clusters.0.name                       | Demo Kube Cluster                     |
      | project.clusters.0.type                       | kubernetes                            |
      | project.clusters.0.address                    | https://kubernetes.localhost:12345    |
      | project.clusters.0.environment.name           | prod                                  |
      | project.clusters.0.identity.caCertificate     | -----BEGIN CERTIFICATE-----           |
      | project.clusters.0.identity.clientCertificate |                                       |
      | project.clusters.0.identity.clientKey         |                                       |
      | project.clusters.0.identity.token             | fooBar                                |
    Then get a JSON reponse
    But an 400 error

  Scenario: From the API, get an owned project
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get the last project
    Then get a JSON reponse
    And the serialized project "my project"

  Scenario: From the API, get an non-owned project and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get the last project
    Then get a JSON reponse
    But an 403 error

  Scenario: From the API, edit an owned project, via a request with a form url encoded body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to edit a project:
      | field                                                       | value                                 |
      | space_project.project.name                                  | Behats Test                           |
      | space_project.projectMetadata.projectUrl                    | https://behat.tests                   |
      | space_project.project.prefix                                | behat-test                            |
      | space_project.project.sourceRepository.pullUrl              | https://oauth:foo@gitlab.teknoo.space |
      | space_project.project.sourceRepository.defaultBranch        | master                                |
      | space_project.project.sourceRepository.identity.name        | git                                   |
      | space_project.project.sourceRepository.identity.privateKey  |                                       |
      | space_project.project.imagesRegistry.apiUrl                 | registry.teknoo.space                 |
      | space_project.project.imagesRegistry.identity.auth          |                                       |
      | space_project.project.imagesRegistry.identity.username      | teknoo-software                       |
      | space_project.project.imagesRegistry.identity.password      | azertyy                               |
      | space_project.project.clusters.0.name                       | Demo Kube Cluster                     |
      | space_project.project.clusters.0.type                       | kubernetes                            |
      | space_project.project.clusters.0.address                    | https://kubernetes.localhost:12345    |
      | space_project.project.clusters.0.environment.name           | prod                                  |
      | space_project.project.clusters.0.identity.caCertificate     | -----BEGIN CERTIFICATE-----FooBar     |
      | space_project.project.clusters.0.identity.clientCertificate |                                       |
      | space_project.project.clusters.0.identity.clientKey         |                                       |
      | space_project.project.clusters.0.identity.token             | aFakeToken                            |
    Then get a JSON reponse
    And the serialized updated project "Behats Test"

  Scenario: From the API, edit an non-owned project, via a request with a form url encoded body and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to edit a project:
      | field                                                       | value                                 |
      | space_project.project.name                                  | Behats Test                           |
      | space_project.projectMetadata.projectUrl                    | https://behat.tests                   |
      | space_project.project.prefix                                | behat-test                            |
      | space_project.project.sourceRepository.pullUrl              | https://oauth:foo@gitlab.teknoo.space |
      | space_project.project.sourceRepository.defaultBranch        | master                                |
      | space_project.project.sourceRepository.identity.name        | git                                   |
      | space_project.project.sourceRepository.identity.privateKey  |                                       |
      | space_project.project.imagesRegistry.apiUrl                 | registry.teknoo.space                 |
      | space_project.project.imagesRegistry.identity.auth          |                                       |
      | space_project.project.imagesRegistry.identity.username      | teknoo-software                       |
      | space_project.project.imagesRegistry.identity.password      | azertyy                               |
      | space_project.project.clusters.0.name                       | Demo Kube Cluster                     |
      | space_project.project.clusters.0.type                       | kubernetes                            |
      | space_project.project.clusters.0.address                    | https://kubernetes.localhost:12345    |
      | space_project.project.clusters.0.environment.name           | prod                                  |
      | space_project.project.clusters.0.identity.caCertificate     | -----BEGIN CERTIFICATE-----FooBar     |
      | space_project.project.clusters.0.identity.clientCertificate |                                       |
      | space_project.project.clusters.0.identity.clientKey         |                                       |
      | space_project.project.clusters.0.identity.token             | aFakeToken                            |
    Then get a JSON reponse
    But an 403 error

  Scenario: From the API, edit an owned project, via a request with a json body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to edit a project with a json body:
      | field                                         | value                                 |
      | project.name                                  | Behats Test                           |
      | projectMetadata.projectUrl                    | https://behat.tests                   |
      | project.prefix                                | behat-test                            |
      | project.sourceRepository.pullUrl              | https://oauth:foo@gitlab.teknoo.space |
      | project.sourceRepository.defaultBranch        | master                                |
      | project.sourceRepository.identity.name        | git                                   |
      | project.sourceRepository.identity.privateKey  |                                       |
      | project.imagesRegistry.apiUrl                 | registry.teknoo.space                 |
      | project.imagesRegistry.identity.auth          |                                       |
      | project.imagesRegistry.identity.username      | teknoo-software                       |
      | project.imagesRegistry.identity.password      | azertyy                               |
      | project.clusters.0.name                       | Demo Kube Cluster                     |
      | project.clusters.0.type                       | kubernetes                            |
      | project.clusters.0.address                    | https://kubernetes.localhost:12345    |
      | project.clusters.0.environment.name           | prod                                  |
      | project.clusters.0.identity.caCertificate     | -----BEGIN CERTIFICATE-----FooBar     |
      | project.clusters.0.identity.clientCertificate |                                       |
      | project.clusters.0.identity.clientKey         |                                       |
      | project.clusters.0.identity.token             | aFakeToken                            |
    Then get a JSON reponse
    And the serialized updated project "Behats Test"

  Scenario: From the API, edit an non-owned project, via a request with a json body and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to edit a project with a json body:
      | field                                         | value                                 |
      | project.name                                  | Behats Test                           |
      | projectMetadata.projectUrl                    | https://behat.tests                   |
      | project.prefix                                | behat-test                            |
      | project.sourceRepository.pullUrl              | https://oauth:foo@gitlab.teknoo.space |
      | project.sourceRepository.defaultBranch        | master                                |
      | project.sourceRepository.identity.name        | git                                   |
      | project.sourceRepository.identity.privateKey  |                                       |
      | project.imagesRegistry.apiUrl                 | registry.teknoo.space                 |
      | project.imagesRegistry.identity.auth          |                                       |
      | project.imagesRegistry.identity.username      | teknoo-software                       |
      | project.imagesRegistry.identity.password      | azertyy                               |
      | project.clusters.0.name                       | Demo Kube Cluster                     |
      | project.clusters.0.type                       | kubernetes                            |
      | project.clusters.0.address                    | https://kubernetes.localhost:12345    |
      | project.clusters.0.environment.name           | prod                                  |
      | project.clusters.0.identity.caCertificate     | -----BEGIN CERTIFICATE-----FooBar     |
      | project.clusters.0.identity.clientCertificate |                                       |
      | project.clusters.0.identity.clientKey         |                                       |
      | project.clusters.0.identity.token             | aFakeToken                            |
    Then get a JSON reponse
    But an 403 error

  Scenario: From the API, delete an owned project
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to delete the last project
    Then get a JSON reponse
    And the serialized deleted project
    And the project is deleted

  Scenario: From the API, delete an non-owned project and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to delete the last project
    Then get a JSON reponse
    But an 403 error
    And the project is not deleted

  Scenario: From the API, delete an owned project via a request with DELETE method
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to delete the last project with DELETE method
    Then get a JSON reponse
    And the serialized deleted project
    And the project is deleted

  Scenario: From the API, delete a non-owned project via a request with DELETE method and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to delete the last project with DELETE method
    Then get a JSON reponse
    But an 403 error
    And the project is not deleted