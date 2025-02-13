Feature: API admin endpoints to administrate custom clusters of accounts.
  In order to manage account's clusters
  As an administrator of Space
  I want to manage accounts clusters of each registered account

  On space, users on a same account can define clusters to use with their environments and for all projects.
  These clusters will complete the official `Cluster Catalog` defined by the Space instance's administrators.
  These account's clusters must be used like defined clusters, Space must be able to initialize environment's namespace
  on these cluster like defined clusted, and environments hosted on these clusters must be used like environments on
  defined clusters. But users of anothers accounts can only access to other accounts'clusters.

  Scenario: From the API, as Admin, list accounts clusters of an account
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And an user, called "Albert" "Jean" with the "albert@teknoo.space" with the password "Test2@Test"
    And "5" accounts clusters "cluster X" and a slug "a-cluster"
    And an account for "My Other Company" with the account namespace "my-other-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And "5" accounts clusters "cluster B X" and a slug "a-b-cluster"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to list of accounts clusters of last account as admin
    Then get a JSON reponse
    And is a serialized collection of "5" items on "1" pages
    And the a list of serialized accounts clusters

  Scenario: From the API, as Admin, create an account cluster, via a request with a form url encoded body
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create an account cluster as admin:
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

  Scenario: From the API, as Admin, create an account cluster, via a request with a json body
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create an account cluster as admin with a json body:
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

  Scenario: From the API, as Admin, get an account cluster
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And an account clusters "my cluster" and a slug "a-cluster"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get the last account cluster as admin
    Then get a JSON reponse
    And the serialized account cluster "my cluster"

  Scenario: From the API, as Admin, edit an account's cluster, via a request with a form url encoded body
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And an account clusters "my cluster" and a slug "a-cluster"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to edit an account cluster as admin:
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

  Scenario: From the API, as Admin, edit an account's cluster, via a request with a json body
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And an account clusters "my cluster" and a slug "a-cluster"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to edit an account cluster as admin with a json body:
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

  Scenario: From the API, as Admin, delete an account cluster
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And an account clusters "my cluster" and a slug "a-cluster"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to delete the last account cluster as admin
    Then get a JSON reponse
    And the serialized deleted account cluster
    And the account cluster is deleted

  Scenario: From the API, as Admin, delete an account cluster, via a request with DELETE method
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And an account clusters "my cluster" and a slug "a-cluster"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to delete the last account cluster with DELETE method as admin
    Then get a JSON reponse
    And the serialized deleted account cluster
    And the account cluster is deleted
