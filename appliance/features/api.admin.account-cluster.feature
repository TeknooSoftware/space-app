Feature: On a space instance, an API is available to manage accounts'clusters as admin.
  An admin must has same rights of than the web access #todo

  Scenario: List of accounts clusters from the API as Admin
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

  Scenario: Create an account cluster from the API as Admin
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

  Scenario: Create an account cluster from the API with a json body as Admin
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
      | field                              | value                   |
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

  Scenario: Get an account cluster from the API as Admin
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

  Scenario: Edit an account cluster from the API as Admin
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

  Scenario: Edit an account cluster from the API with a json body as Admin
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
      | field                              | value                   |
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

  Scenario: Delete an account cluster from the API as Admin
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

  Scenario: Delete an account cluster from the API with DELETE method as Admin
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
