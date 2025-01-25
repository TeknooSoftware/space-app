Feature: Cluster's credentials and informations in project are not linked to account's environments, defined clusters
  and Account's clusters. But, users can easily add a configured environment in a cluster from the project api. From
  this same api, users' can refresh all credentials of environments added from the account, hosted on a defined cluster
  or an account's cluster. #TODO

  Scenario: Run refresh credentials on a project not associated to an account's environment, project must stay unchanged
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

  Scenario: Run refresh credentials on a project associated to an account's environment on a catalog cluster
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
      | space_project.project.clusters.0.name                       | Demo Kube Cluster                     |
      | space_project.project.clusters.0.type                       | kubernetes                            |
      | space_project.project.clusters.0.address                    | https://kubernetes.localhost:12345    |
      | space_project.project.clusters.0.environment.name           | prod                                  |
      | space_project.project.clusters.0.identity.caCertificate     | -----BEGIN CERTIFICATE-----BarFoo     |
      | space_project.project.clusters.0.identity.clientCertificate |                                       |
      | space_project.project.clusters.0.identity.clientKey         |                                       |
      | space_project.project.clusters.0.identity.token             | anotherFakeToken                      |
    Then get a JSON reponse
    And the serialized updated project "Behats Test"
    When the API is called to refresh credentials of the last project
    Then get a JSON reponse
    And the serialized project "my project"
    And the last project's cluster returns to its original state from the clusters catalog

  Scenario: Run refresh credentials on a project associated to an account's environment on an account cluster
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an account clusters "Cluster Company" and a slug "my-company-cluster"
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
      | space_project.project.clusters.0.name                       | Cluster Company                       |
      | space_project.project.clusters.0.type                       | kubernetes                            |
      | space_project.project.clusters.0.address                    | https://foo/bar:12345                 |
      | space_project.project.clusters.0.environment.name           | prod                                  |
      | space_project.project.clusters.0.identity.caCertificate     | -----BEGIN CERTIFICATE-----BarFoo     |
      | space_project.project.clusters.0.identity.clientCertificate |                                       |
      | space_project.project.clusters.0.identity.clientKey         |                                       |
      | space_project.project.clusters.0.identity.token             | anotherFakeToken                      |
    Then get a JSON reponse
    And the serialized updated project "Behats Test"
    When the API is called to refresh credentials of the last project
    Then get a JSON reponse
    And the serialized project "my project"
    And the last project's cluster returns to its original state from the account cluster
