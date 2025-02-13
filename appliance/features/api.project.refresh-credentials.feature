Feature: API endpoints to refresh projects' credentials
  In order to manage account's projects
  As an user of an account
  I want to refresh easily all my projects' credentials when environments are updated.

  Cluster's credentials and informations in project are not linked to account's environments, defined clusters
  and Account's clusters. But, users can easily add a configured environment in a cluster from the project api. From
  this same api, users' can refresh all credentials of environments added from the account, hosted on a defined cluster
  or an account's cluster.

  Scenario: From the API, refresh credentials on a non-owned project and get an error
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
    When the API is called to refresh credentials of the last project
    Then get a JSON reponse
    But an 403 error

  Scenario: From the API, refresh credentials on a project not associated to an account's environment, the project
  must stay unchanged
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a custom project "my project" and a prefix "a-prefix" on custom cluster
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to refresh credentials of the last project
    Then get a JSON reponse
    And the serialized project "my project"
    And the last project's cluster remains unchanged

  Scenario: From the API, refresh credentials on a project associated to an account's environment on a catalog cluster
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
      | space_project.project.name                                  | my project                            |
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
      | space_project.project.clusters.2.name                       | Demo Kube Cluster                     |
      | space_project.project.clusters.2.type                       | kubernetes                            |
      | space_project.project.clusters.2.address                    | https://kubernetes.localhost:12345    |
      | space_project.project.clusters.2.environment.name           | prod                                  |
      | space_project.project.clusters.2.identity.caCertificate     | -----BEGIN CERTIFICATE-----BarFoo     |
      | space_project.project.clusters.2.identity.clientCertificate |                                       |
      | space_project.project.clusters.2.identity.clientKey         |                                       |
      | space_project.project.clusters.2.identity.token             | anotherFakeToken                      |
    Then get a JSON reponse
    And the serialized updated project "my project"
    When the API is called to refresh credentials of the last project
    Then get a JSON reponse
    And the serialized project "my project"
    And the last project's cluster returns to its original state from the clusters catalog

  Scenario: From the API, refresh credentials on a project associated to an account's environment on an account cluster
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an account clusters "Cluster Company" and a slug "my-company-cluster"
    And an account environment on "Cluster Company" for the environment "prod"
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
      | space_project.project.name                                  | my project                            |
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
      | space_project.project.clusters.2.name                       | Cluster Company                       |
      | space_project.project.clusters.2.type                       | kubernetes                            |
      | space_project.project.clusters.2.address                    | https://foo/bar:12345                 |
      | space_project.project.clusters.2.environment.name           | prod                                  |
      | space_project.project.clusters.2.identity.caCertificate     | -----BEGIN CERTIFICATE-----BarFoo     |
      | space_project.project.clusters.2.identity.clientCertificate |                                       |
      | space_project.project.clusters.2.identity.clientKey         |                                       |
      | space_project.project.clusters.2.identity.token             | anotherFakeToken                      |
    Then get a JSON reponse
    And the serialized updated project "my project"
    When the API is called to refresh credentials of the last project
    Then get a JSON reponse
    And the serialized project "my project"
    And the last project's cluster returns to its original state from the account cluster
