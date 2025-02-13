Feature: API endpoints to create custom clusters on accounts available to account's environment where deploy projects
  In order to manage account's clusters
  As an user of an account
  I want to manage and create custom cluster for my account

  On space, users on a same account can define clusters to use with their environments and for all projects.
  These clusters will complete the official `Cluster Catalog` defined by the Space instance's administrators.
  These account's clusters must be used like defined clusters, Space must be able to initialize environment's namespace
  on these cluster like defined clusted, and environments hosted on these clusters must be used like environments on
  defined clusters. But users of anothers accounts can only access to other accounts'clusters.

  Scenario: From the API, list accounts clusters of user's account
    Given A Space app instance
    And A memory document database
    And an account for "My First Company" with the account namespace "my-first-company"
    And an user, called "Albert" "Jean" with the "albert@teknoo.space" with the password "Test2@Test"
    And "5" accounts clusters "cluster A X" and a slug "a-cluster"
    And an account for "My Other Company" with the account namespace "my-other-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And "5" accounts clusters "cluster B X" and a slug "a-cluster"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to list of accounts clusters
    Then get a JSON reponse
    And is a serialized collection of "5" items on "1" pages
    And the a list of serialized owned accounts clusters

  Scenario: From the API, create an account cluster, via a request with a form url encoded body
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
    Then get a JSON reponse
    And the serialized created account cluster "Behats Test"
    And there is an account cluster in the memory for this account

  Scenario: From the API, create an account cluster, via a request with a json body
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
    Then get a JSON reponse
    And the serialized created account cluster "Behats Test"
    And there is an account cluster in the memory for this account

  Scenario: From the api, get an owned account cluster
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account clusters "my cluster" and a slug "a-cluster"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get the last account cluster
    Then get a JSON reponse
    And the serialized account cluster "my cluster"

  Scenario: From the api, get an non-owned account cluster and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And an account clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get the last account cluster
    Then get a JSON reponse
    But an 403 error

  Scenario: From the api, edit an owned account cluster, via a request with a form url encoded body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
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
    Then get a JSON reponse
    And the serialized updated account cluster "Behats Test"

  Scenario: From the api, edit an non-owned account cluster, via a request with a form url encoded body and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And an account clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
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
    Then get a JSON reponse
    But an 403 error

  Scenario: From the API, edit an owned account cluster, via a request with a json body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
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
    Then get a JSON reponse
    And the serialized updated account cluster "Behats Test"

  Scenario: From the API, edit an non-owned account cluster, via a request with a json body and get and error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And an account clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
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
    Then get a JSON reponse
    But an 403 error

  Scenario: From the API, delete an owned account cluster
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to delete the last account cluster
    Then get a JSON reponse
    And the serialized deleted account cluster
    And the account cluster is deleted

  Scenario: From the api, delete an non-owned account cluster and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And an account clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to delete the last account cluster
    Then get a JSON reponse
    But an 403 error
    And the account cluster is not deleted

  Scenario: From the API, delete an owned account cluster, via a request with DELETE method
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to delete the last account cluster with DELETE method
    Then get a JSON reponse
    And the serialized deleted account cluster
    And the account cluster is deleted

  Scenario: From the API, delete an non-owned account cluster via a request with DELETE method and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And an account clusters "cluster X" and a slug "a-cluster"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to delete the last account cluster with DELETE method
    Then get a JSON reponse
    But an 403 error
    And the account cluster is not deleted
