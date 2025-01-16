Feature: On a space instance, an API is available to manage accounts as admin and integrating it with any platform.
  An admin must has same rights of than the web access

  Scenario: List of accounts from the API as Admin
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And 10 accounts with some users
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to list of accounts as admin
    Then get a JSON reponse
    And is a serialized collection of "10" items on "1" pages
    And the a list of serialized accounts

  Scenario: Create a account from the API as Admin
    Given A Space app instance
    And a kubernetes client
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create an account as admin:
      | field                                         | value         |
      | admin_space_account.account.name              | Test Behat    |
      | admin_space_account.account.prefix_namespace  | space-client- |
      | admin_space_account.account.namespace         | behat         |
      | admin_space_account.accountData.legalName     | sasu demo     |
      | admin_space_account.accountData.streetAddress | Auge          |
      | admin_space_account.accountData.zipCode       | 14000         |
      | admin_space_account.accountData.cityName      | Caen          |
      | admin_space_account.accountData.countryName   | France        |
      | admin_space_account.accountData.vatNumber     | FR0102030405  |
    Then get a JSON reponse
    And the serialized account "Test Behat" for admin
    And there is an account in the memory
    And a Kubernetes namespace dedicated to registry for "behat" is applied and populated
    And no Kubernetes manifests must not be deleted

  Scenario: Create a account from the API with subscription plan as Admin
    Given A Space app instance
    And a kubernetes client
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create an account as admin:
      | field                                            | value         |
      | admin_space_account.account.name                 | Test Behat    |
      | admin_space_account.account.prefix_namespace     | space-client- |
      | admin_space_account.account.namespace            | behat         |
      | admin_space_account.accountData.legalName        | sasu demo     |
      | admin_space_account.accountData.streetAddress    | Auge          |
      | admin_space_account.accountData.zipCode          | 14000         |
      | admin_space_account.accountData.cityName         | Caen          |
      | admin_space_account.accountData.countryName      | France        |
      | admin_space_account.accountData.vatNumber        | FR0102030405  |
      | admin_space_account.accountData.subscriptionPlan | test-1        |
    Then get a JSON reponse
    And the serialized account "Test Behat" for admin
    And there is an account in the memory
    And with the subscription plan "test-1"
    And a Kubernetes namespace dedicated to registry for "behat" is applied and populated
    And no Kubernetes manifests must not be deleted

  Scenario: Create a account from the API with a json body as Admin
    Given A Space app instance
    And a kubernetes client
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create an account as admin with a json body:
      | field                     | value         |
      | account.name              | Test Behat    |
      | account.prefix_namespace  | space-client- |
      | account.namespace         | behat         |
      | accountData.legalName     | sasu demo     |
      | accountData.streetAddress | Auge          |
      | accountData.zipCode       | 14000         |
      | accountData.cityName      | Caen          |
      | accountData.countryName   | France        |
      | accountData.vatNumber     | FR0102030405  |
    Then get a JSON reponse
    And the serialized account "Test Behat" for admin
    And there is an account in the memory
    And a Kubernetes namespace dedicated to registry for "behat" is applied and populated
    And no Kubernetes manifests must not be deleted

  Scenario: Create a account from the API with a json body with subscription plan as Admin
    Given A Space app instance
    And a kubernetes client
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create an account as admin with a json body:
      | field                        | value         |
      | account.name                 | Test Behat    |
      | account.prefix_namespace     | space-client- |
      | account.namespace            | behat         |
      | accountData.legalName        | sasu demo     |
      | accountData.streetAddress    | Auge          |
      | accountData.zipCode          | 14000         |
      | accountData.cityName         | Caen          |
      | accountData.countryName      | France        |
      | accountData.vatNumber        | FR0102030405  |
      | accountData.subscriptionPlan | test-1        |
    Then get a JSON reponse
    And the serialized account "Test Behat" for admin
    And there is an account in the memory
    And with the subscription plan "test-1"
    And a Kubernetes namespace dedicated to registry for "behat" is applied and populated
    And no Kubernetes manifests must not be deleted

  Scenario: Get an account from the API as Admin
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
    When the API is called to get the last account
    Then get a JSON reponse
    And the serialized account "My Company" for admin
    And no Kubernetes manifests must not be deleted

  Scenario: Edit an account from the API as Admin
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
    When the API is called to edit the last account:
      | field                                            | value         |
      | admin_space_account.account.name                 | Test Behat    |
      | admin_space_account.account.prefix_namespace     | space-client- |
      | admin_space_account.account.namespace            | behat         |
      | admin_space_account.accountData.legalName        | sasu demo     |
      | admin_space_account.accountData.streetAddress    | Auge          |
      | admin_space_account.accountData.zipCode          | 14000         |
      | admin_space_account.accountData.cityName         | Caen          |
      | admin_space_account.accountData.countryName      | France        |
      | admin_space_account.accountData.vatNumber        | FR0102030405  |
      | admin_space_account.accountData.subscriptionPlan | test-1        |
    Then get a JSON reponse
    And the serialized account "Test Behat" for admin
    And no Kubernetes manifests must not be created
    And no Kubernetes manifests must not be deleted

  Scenario: Edit an account from the API with a json body as Admin
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
    When the API is called to edit the last account with a json body:
      | field                        | value         |
      | account.name                 | Test Behat    |
      | account.prefix_namespace     | space-client- |
      | account.namespace            | behat         |
      | accountData.legalName        | sasu demo     |
      | accountData.streetAddress    | Auge          |
      | accountData.zipCode          | 14000         |
      | accountData.cityName         | Caen          |
      | accountData.countryName      | France        |
      | accountData.vatNumber        | FR0102030405  |
      | accountData.subscriptionPlan | test-1        |
    Then get a JSON reponse
    And the serialized account "Test Behat" for admin
    And no Kubernetes manifests must not be created
    And no Kubernetes manifests must not be deleted

  Scenario: Delete an account from the API as Admin
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
    When the API is called to delete the last account
    Then get a JSON reponse
    And the serialized deleted account
    And the account is deleted

  Scenario: Delete an account from the API with DELETE method as Admin
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
    When the API is called to delete the last account with DELETE method
    Then get a JSON reponse
    And the serialized deleted account
    And the account is deleted

  Scenario: Reinstall account's registry namespace from the API as Admin
    Given A Space app instance
    And A memory document database
    And a kubernetes client
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to reinstall the account registry
    Then get a JSON reponse
    And the serialized success result
    And a Kubernetes namespace dedicated to registry for "my-company" is applied and populated
    And no Kubernetes manifests must not be deleted
    And the old account registry object has been deleted and remplaced
