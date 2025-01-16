Feature: On space, users on a same account can define some clusters to use with their environments and
  for all projects. These clusters will complete the `Cluster Catalog` defined by the Space instance's administrators.
  These account's clusters must be used like defined clusters, Space must be initialize environment's namespace on
  these cluster like defined clusted, and environments hosted on these clusters must be used like defined clusters.
  But users of anothers accounts can only access to account'clusters owned by theirs accounts.
  An API is available to manage these clusters.

  Scenario: List owned accounts clusters from the API
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

  Scenario: Create an account cluster from the API
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

  Scenario: Create an account cluster from the API with a json body
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
      | field                               | value                   |
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

  Scenario: Get an owned account cluster from the API
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

  Scenario: Get an non-owned account cluster from the API
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
    And an 403 error

  Scenario: Edit an owned account cluster from the API
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
      | field                               | value                   |
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

  Scenario: Edit an non-owned account cluster from the API
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
      | field                               | value                   |
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
    And an 403 error

  Scenario: Edit an owned account cluster from the API with a json body
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
      | field                               | value                   |
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

  Scenario: Edit an non-owned account cluster from the API with a json body
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
      | field                               | value                   |
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
    And an 403 error

  Scenario: Delete an owned account cluster from the API
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

  Scenario: Delete an non-owned account cluster from the API
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
    And an 403 error
    And the account cluster is not deleted

  Scenario: Delete an owned account cluster from the API with DELETE method
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

  Scenario: Delete an non-owned account cluster from the API with DELETE method
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
    And an 403 error
    And the account cluster is not deleted
