@api
Feature: API endpoints to create custom clusters on accounts available to account's environment where deploy projects
  In order to manage account's clusters
  As a user of an account
  I want to manage and create custom cluster for my account

  On space, users on a same account can define clusters to use with their environments and for all projects.
  These clusters will complete the official `Cluster Catalog` defined by the Space instance's administrators.
  These account's clusters must be used like defined clusters, Space must be able to initialize environment's namespace
  on these cluster like defined clusted, and environments hosted on these clusters must be used like environments on
  defined clusters. But users of anothers accounts can only access to other accounts'clusters.

  Background:
    Given a Space app instance
    And a memory document database

  Scenario: From the API, list accounts clusters of user's account
    Given an account for "My First Company" with the account namespace "my-first-company"
    And a user, called "Albert" "Jean" with the "albert@teknoo.space" with the password "Test2@Test"
    And "5" accounts clusters "cluster A X" and a slug "a-cluster"
    And an account for "My Other Company" with the account namespace "my-other-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And "5" accounts clusters "cluster B X" and a slug "a-cluster"
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to list of accounts clusters
    Then get a JSON response
    And is a serialized collection of "5" items on "1" pages
    And the list of serialized owned accounts clusters

  Scenario: From the API, create an account cluster, via a request with a form url encoded body
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to create an account cluster:
      | field                              | value                   |
      | account_cluster.name               | Behats Test             |
      | account_cluster.slug               | behat-test              |
      | account_cluster.type               | kubernetes              |
      | account_cluster.masterAddress      | https://127.0.0.1:12345 |
      | account_cluster.storageProvisioner | nfs                     |
      | account_cluster.dashboardAddress   | https://dashboard.local |
      | account_cluster.caCertificate      | Foo                     |
      | account_cluster.token              | Bar                     |
      | account_cluster.supportRegistry    | 1                       |
      | account_cluster.registryUrl        | https://registry.local  |
      | account_cluster.useHnc             | 0                       |
    Then get a JSON response
    And the serialized created account cluster "Behats Test"
    And there is an account cluster in the memory for this account

  Scenario: From the API, create an account cluster, via a request with a json body
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to create an account cluster with a json body:
      | field              | value                   |
      | name               | Behats Test             |
      | slug               | behat-test              |
      | type               | kubernetes              |
      | masterAddress      | https://127.0.0.1:12345 |
      | storageProvisioner | nfs                     |
      | dashboardAddress   | https://dashboard.local |
      | caCertificate      | Foo                     |
      | token              | Bar                     |
      | supportRegistry    | 1                       |
      | registryUrl        | https://registry.local  |
      | useHnc             | 0                       |
    Then get a JSON response
    And the serialized created account cluster "Behats Test"
    And there is an account cluster in the memory for this account

  Scenario: From the API, get an owned account cluster
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account clusters "my cluster" and a slug "a-cluster"
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to get the last account cluster
    Then get a JSON response
    And the serialized account cluster "my cluster"

  Scenario: From the API, get a non-owned account cluster and get an error
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "An Other Company" with the account namespace "my-company"
    And a user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And an account clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to get the last account cluster
    Then get a JSON response
    But an 403 error

  Scenario: From the API, edit an owned account cluster, via a request with a form url encoded body
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to edit an account cluster:
      | field                              | value                   |
      | account_cluster.name               | Behats Test             |
      | account_cluster.slug               | behat-test              |
      | account_cluster.type               | kubernetes              |
      | account_cluster.masterAddress      | https://127.0.0.1:12345 |
      | account_cluster.storageProvisioner | nfs                     |
      | account_cluster.dashboardAddress   | https://dashboard.local |
      | account_cluster.caCertificate      | Foo                     |
      | account_cluster.token              | Bar                     |
      | account_cluster.supportRegistry    | 1                       |
      | account_cluster.registryUrl        | https://registry.local  |
      | account_cluster.useHnc             | 0                       |
    Then get a JSON response
    And the serialized updated account cluster "Behats Test"

  Scenario: From the API, edit a non-owned account cluster, via a request with a form url encoded body and get an error
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "An Other Company" with the account namespace "my-company"
    And a user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And an account clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to edit an account cluster:
      | field                              | value                   |
      | account_cluster.name               | Behats Test             |
      | account_cluster.slug               | behat-test              |
      | account_cluster.type               | kubernetes              |
      | account_cluster.masterAddress      | https://127.0.0.1:12345 |
      | account_cluster.storageProvisioner | nfs                     |
      | account_cluster.dashboardAddress   | https://dashboard.local |
      | account_cluster.caCertificate      | Foo                     |
      | account_cluster.token              | Bar                     |
      | account_cluster.supportRegistry    | 1                       |
      | account_cluster.registryUrl        | https://registry.local  |
      | account_cluster.useHnc             | 0                       |
    Then get a JSON response
    But an 403 error

  Scenario: From the API, edit an owned account cluster, via a request with a json body
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to edit an account cluster with a json body:
      | field              | value                   |
      | name               | Behats Test             |
      | slug               | behat-test              |
      | type               | kubernetes              |
      | masterAddress      | https://127.0.0.1:12345 |
      | storageProvisioner | nfs                     |
      | dashboardAddress   | https://dashboard.local |
      | caCertificate      | Foo                     |
      | token              | Bar                     |
      | supportRegistry    | 1                       |
      | registryUrl        | https://registry.local  |
      | useHnc             | 0                       |
    Then get a JSON response
    And the serialized updated account cluster "Behats Test"

  Scenario: From the API, edit a non-owned account cluster, via a request with a json body and get an error
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "An Other Company" with the account namespace "my-company"
    And a user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And an account clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to edit an account cluster with a json body:
      | field                              | value                   |
      | account_cluster.name               | Behats Test             |
      | account_cluster.slug               | behat-test              |
      | account_cluster.type               | kubernetes              |
      | account_cluster.masterAddress      | https://127.0.0.1:12345 |
      | account_cluster.storageProvisioner | nfs                     |
      | account_cluster.dashboardAddress   | https://dashboard.local |
      | account_cluster.caCertificate      | Foo                     |
      | account_cluster.token              | Bar                     |
      | account_cluster.supportRegistry    | 1                       |
      | account_cluster.registryUrl        | https://registry.local  |
      | account_cluster.useHnc             | 0                       |
    Then get a JSON response
    But an 403 error

  Scenario: From the API, delete an owned account cluster
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to delete the last account cluster
    Then get a JSON response
    And the serialized deleted account cluster
    And the account cluster is deleted

  Scenario: From the API, delete a non-owned account cluster and get an error
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "An Other Company" with the account namespace "my-company"
    And a user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And an account clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to delete the last account cluster
    Then get a JSON response
    But an 403 error
    And the account cluster is not deleted

  Scenario: From the API, delete an owned account cluster, via a request with DELETE method
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to delete the last account cluster with DELETE method
    Then get a JSON response
    And the serialized deleted account cluster
    And the account cluster is deleted

  Scenario: From the API, delete a non-owned account cluster via a request with DELETE method and get an error
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "An Other Company" with the account namespace "my-company"
    And a user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And an account clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to delete the last account cluster with DELETE method
    Then get a JSON response
    But an 403 error
    And the account cluster is not deleted
