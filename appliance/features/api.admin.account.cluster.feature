@api @admin
Feature: API admin endpoints to administrate custom clusters of accounts.
  In order to keep the clusters of the accounts out of the reach of the administration
  As an administrator of Space
  I can list the clusters of an account, read only, but I can not read or change one of them

  On space, users on a same account can define clusters to use with their environments and for all projects.
  These clusters will complete the official `Cluster Catalog` defined by the Space instance's administrators.
  These account's clusters must be used like defined clusters, Space must be able to initialize environment's namespace
  on these cluster like defined clusted, and environments hosted on these clusters must be used like environments on
  defined clusters. But users of anothers accounts can only access to other accounts'clusters.

  Background:
    Given a Space app instance
    And a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user

  Scenario: From the API, as Admin, list accounts clusters of an account, read only
    Given an account for "My First Company" with the account namespace "my-first-company"
    And a user, called "Albert" "Jean" with the "albert@teknoo.space" with the password "Test2@Test"
    And "5" accounts clusters "cluster X" and a slug "a-cluster"
    And an account for "My Other Company" with the account namespace "my-other-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And "5" accounts clusters "cluster B X" and a slug "a-b-cluster"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to list of accounts clusters of last account as admin
    Then get a JSON response
    And is a serialized collection of "5" items on "1" pages
    And the list of serialized accounts clusters

  Scenario: From the API, as Admin, the list of accounts clusters of an account gives the identifier of each cluster
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And "5" accounts clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to list of accounts clusters of last account as admin
    Then get a JSON response
    And is a serialized collection of "5" items on "1" pages
    And each serialized account cluster of the list carries its identifier
