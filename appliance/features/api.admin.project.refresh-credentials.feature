@api @admin
Feature: API admin endpoints to refresh projects' credentials
  In order to keep the projects of the accounts out of the reach of the administration
  As an administrator of Space
  I can not refresh the credentials of a project

  Cluster's credentials and informations in project are not linked to account's environments, defined clusters
  and Account's clusters. But, users can easily add a configured environment in a cluster from the project api. From
  this same api, users' can refresh all credentials of environments added from the account, hosted on a defined cluster
  or an account's cluster. An administrator of Space is not a user of an account: it can not open a project, so it can
  not refresh its credentials.

  Background:
    Given a Space app instance
    And a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"

  Scenario: From the API, as Admin, refresh credentials on a project not associated to an account's environment is refused
    Given a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a custom project "my project" and a prefix "a-prefix" on custom cluster
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to refresh credentials of the last project as admin
    Then get a JSON response
    But an 403 error
    And the last project's cluster remains unchanged

  Scenario: From the API, as Admin, refresh credentials on a project associated to an account's environment on an account cluster is refused
    Given an account clusters "Cluster Company" and a slug "my-company-cluster"
    And an account environment on "Cluster Company" for the environment "prod"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to refresh credentials of the last project as admin
    Then get a JSON response
    But an 403 error
