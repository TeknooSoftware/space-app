Feature: On a space instance, an API is available to manage accounts as admin and integrating it with any platform.
  An admin must has same rights of than the web access

  Scenario: List of accounts from the API
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

  Scenario: Create a account from the API
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

  Scenario: Create a account from the API with subscription plan
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

  Scenario: Create a account from the API with a json body
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

  Scenario: Create a account from the API with a json body with subscription plan
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

  Scenario: Get an account from the API
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

  Scenario: Edit an account from the API
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

  Scenario: Edit an account from the API with a json body
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

  Scenario: Edit an account and environments from the API
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
    When the API is called to edit the last account:
      | field                                                         | value             |
      | admin_space_account.account.name                              | Test Behat        |
      | admin_space_account.account.prefix_namespace                  | space-client-     |
      | admin_space_account.account.namespace                         | my-company        |
      | admin_space_account.accountData.legalName                     | sasu demo         |
      | admin_space_account.accountData.streetAddress                 | Auge              |
      | admin_space_account.accountData.zipCode                       | 14000             |
      | admin_space_account.accountData.cityName                      | Caen              |
      | admin_space_account.accountData.countryName                   | France            |
      | admin_space_account.accountData.vatNumber                     | FR0102030405      |
      | admin_space_account.accountData.subscriptionPlan              | test-1            |
      | admin_space_account.environmentResumes.1.accountEnvironmentId | <auto:prod>       |
      | admin_space_account.environmentResumes.1.clusterName          | Demo Kube Cluster |
      | admin_space_account.environmentResumes.1.envName              | prod              |
      | admin_space_account.environmentResumes.2.accountEnvironmentId |                   |
      | admin_space_account.environmentResumes.2.clusterName          | Demo Kube Cluster |
      | admin_space_account.environmentResumes.2.envName              | testing           |
    Then get a JSON reponse
    And the serialized account "Test Behat" for admin
    And a Kubernetes namespace for "my-company-testing" dedicated to "Demo Kube Cluster" is applied and populated
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: Edit an account and environments from the API with a json body
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
    When the API is called to edit the last account with a json body:
      | field                                     | value             |
      | account.name                              | Test Behat        |
      | account.prefix_namespace                  | space-client-     |
      | account.namespace                         | my-company        |
      | accountData.legalName                     | sasu demo         |
      | accountData.streetAddress                 | Auge              |
      | accountData.zipCode                       | 14000             |
      | accountData.cityName                      | Caen              |
      | accountData.countryName                   | France            |
      | accountData.vatNumber                     | FR0102030405      |
      | accountData.subscriptionPlan              | test-1            |
      | environmentResumes.1.accountEnvironmentId | <auto:prod>       |
      | environmentResumes.1.clusterName          | Demo Kube Cluster |
      | environmentResumes.1.envName              | prod              |
      | environmentResumes.2.accountEnvironmentId |                   |
      | environmentResumes.2.clusterName          | Demo Kube Cluster |
      | environmentResumes.2.envName              | Testing           |
    Then get a JSON reponse
    And the serialized account "Test Behat" for admin
    And a Kubernetes namespace for "my-company-testing" dedicated to "Demo Kube Cluster" is applied and populated
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: Edit an account and edit read only environments from the API
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
      | field                                                         | value             |
      | admin_space_account.account.name                              | Test Behat        |
      | admin_space_account.account.prefix_namespace                  | space-client-     |
      | admin_space_account.account.namespace                         | my-company        |
      | admin_space_account.accountData.legalName                     | sasu demo         |
      | admin_space_account.accountData.streetAddress                 | Auge              |
      | admin_space_account.accountData.zipCode                       | 14000             |
      | admin_space_account.accountData.cityName                      | Caen              |
      | admin_space_account.accountData.countryName                   | France            |
      | admin_space_account.accountData.vatNumber                     | FR0102030405      |
      | admin_space_account.accountData.subscriptionPlan              | test-1            |
      | admin_space_account.environmentResumes.0.accountEnvironmentId | <auto:dev>        |
      | admin_space_account.environmentResumes.0.clusterName          | Demo Kube Cluster |
      | admin_space_account.environmentResumes.0.envName              | dev               |
      | admin_space_account.environmentResumes.1.accountEnvironmentId | <auto:prod>       |
      | admin_space_account.environmentResumes.1.clusterName          | Demo Kube Cluster |
      | admin_space_account.environmentResumes.1.envName              | Testing           |
    Then get a JSON reponse
    And the user must have a 400 error
    And no Kubernetes manifests must not be created
    And no Kubernetes manifests must not be deleted

  Scenario: Edit an account and edit read only environments from the API with a json body
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
      | field                                     | value             |
      | account.name                              | Test Behat        |
      | account.prefix_namespace                  | space-client-     |
      | account.namespace                         | my-company        |
      | accountData.legalName                     | sasu demo         |
      | accountData.streetAddress                 | Auge              |
      | accountData.zipCode                       | 14000             |
      | accountData.cityName                      | Caen              |
      | accountData.countryName                   | France            |
      | accountData.vatNumber                     | FR0102030405      |
      | accountData.subscriptionPlan              | test-1            |
      | environmentResumes.0.accountEnvironmentId | <auto:dev>        |
      | environmentResumes.0.clusterName          | Demo Kube Cluster |
      | environmentResumes.0.envName              | dev               |
      | environmentResumes.1.accountEnvironmentId | <auto:prod>       |
      | environmentResumes.1.clusterName          | Demo Kube Cluster |
      | environmentResumes.1.envName              | Testing           |
    Then get a JSON reponse
    And the user must have a 400 error
    And no Kubernetes manifests must not be created
    And no Kubernetes manifests must not be deleted

  Scenario: Edit an account and exceed environments allowed from the API
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
      | field                                                         | value             |
      | admin_space_account.account.name                              | Test Behat        |
      | admin_space_account.account.prefix_namespace                  | space-client-     |
      | admin_space_account.account.namespace                         | my-company        |
      | admin_space_account.accountData.legalName                     | sasu demo         |
      | admin_space_account.accountData.streetAddress                 | Auge              |
      | admin_space_account.accountData.zipCode                       | 14000             |
      | admin_space_account.accountData.cityName                      | Caen              |
      | admin_space_account.accountData.countryName                   | France            |
      | admin_space_account.accountData.vatNumber                     | FR0102030405      |
      | admin_space_account.accountData.subscriptionPlan              | test-1            |
      | admin_space_account.environmentResumes.0.accountEnvironmentId | <auto:dev>        |
      | admin_space_account.environmentResumes.0.clusterName          | Demo Kube Cluster |
      | admin_space_account.environmentResumes.0.envName              | dev               |
      | admin_space_account.environmentResumes.1.accountEnvironmentId | <auto:prod>       |
      | admin_space_account.environmentResumes.1.clusterName          | Demo Kube Cluster |
      | admin_space_account.environmentResumes.1.envName              | prod              |
      | admin_space_account.environmentResumes.2.accountEnvironmentId |                   |
      | admin_space_account.environmentResumes.2.clusterName          | Demo Kube Cluster |
      | admin_space_account.environmentResumes.2.envName              | Testing           |
    Then get a JSON reponse
    And the user must have a 400 error
    And no Kubernetes manifests must not be created
    And no Kubernetes manifests must not be deleted

  Scenario: Edit an account and exceed environments allowed from the API with a json body
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
      | field                                     | value             |
      | account.name                              | Test Behat        |
      | account.prefix_namespace                  | space-client-     |
      | account.namespace                         | my-company        |
      | accountData.legalName                     | sasu demo         |
      | accountData.streetAddress                 | Auge              |
      | accountData.zipCode                       | 14000             |
      | accountData.cityName                      | Caen              |
      | accountData.countryName                   | France            |
      | accountData.vatNumber                     | FR0102030405      |
      | accountData.subscriptionPlan              | test-1            |
      | environmentResumes.0.accountEnvironmentId | <auto:dev>        |
      | environmentResumes.0.clusterName          | Demo Kube Cluster |
      | environmentResumes.0.envName              | dev               |
      | environmentResumes.1.accountEnvironmentId | <auto:prod>       |
      | environmentResumes.1.clusterName          | Demo Kube Cluster |
      | environmentResumes.1.envName              | prod              |
      | environmentResumes.2.accountEnvironmentId |                   |
      | environmentResumes.2.clusterName          | Demo Kube Cluster |
      | environmentResumes.2.envName              | testing           |
    Then get a JSON reponse
    And the user must have a 400 error
    And no Kubernetes manifests must not be created
    And no Kubernetes manifests must not be deleted

  Scenario: Delete an account from the API
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

  Scenario: Delete an account from the API with DELETE method
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

  Scenario: Reinstall account's registry namespace from the API
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

  Scenario: Refresh account's quota on its environments from the API
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
    When the API is called to refresh quota of account's environment
    Then get a JSON reponse
    And the serialized success result
    And a Kubernetes manifests dedicated to quota for the last account has been applied
    And no Kubernetes manifests must not be deleted
    And no object has been deleted

  Scenario: Reinstall and account's environment from the API
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
    When the API is called to reinstall the account's environment "prod" on "Demo Kube Cluster"
    Then get a JSON reponse
    And the serialized success result
    And a Kubernetes namespace for "my-company-prod" dedicated to "Demo Kube Cluster" is applied and populated
    And no Kubernetes manifests must not be deleted
    And the old account environment "space-client-my-company-prod" object has been deleted and remplaced

  Scenario: Create new account variables via the API as Admin
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update variables of last account with as admin:
      | field                                     | value  |
      | account_vars.sets.0.envName               | prod1  |
      | account_vars.sets.0.variables.0.id        |        |
      | account_vars.sets.0.variables.0.name      | var1   |
      | account_vars.sets.0.variables.0.wasSecret |        |
      | account_vars.sets.0.variables.0.value     | value1 |
      | account_vars.sets.0.variables.1.id        |        |
      | account_vars.sets.0.variables.1.name      | var2   |
      | account_vars.sets.0.variables.1.secret    | 1      |
      | account_vars.sets.0.variables.1.wasSecret |        |
      | account_vars.sets.0.variables.1.value     | value2 |
      | account_vars.sets.1.envName               | prod2  |
      | account_vars.sets.1.variables.0.id        |        |
      | account_vars.sets.1.variables.0.name      | var3   |
      | account_vars.sets.1.variables.0.wasSecret |        |
      | account_vars.sets.1.variables.0.value     | value3 |
    Then the account must have these persisted variables
      | id | name | secret | value  | environment |
      | x  | var1 | 0      | value1 | prod1       |
      | x  | var2 | 1      | value2 | prod1       |
      | x  | var3 | 0      | value3 | prod2       |
    And no Kubernetes manifests must not be deleted

  Scenario: Create new account variables via the API with a json body as Admin
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update variables of last account with a json body as admin:
      | field                        | value  |
      | sets.0.envName               | prod1  |
      | sets.0.variables.0.id        |        |
      | sets.0.variables.0.name      | var1   |
      | sets.0.variables.0.wasSecret |        |
      | sets.0.variables.0.value     | value1 |
      | sets.0.variables.1.id        |        |
      | sets.0.variables.1.name      | var2   |
      | sets.0.variables.1.secret    | 1      |
      | sets.0.variables.1.wasSecret |        |
      | sets.0.variables.1.value     | value2 |
      | sets.1.envName               | prod2  |
      | sets.1.variables.0.id        |        |
      | sets.1.variables.0.name      | var3   |
      | sets.1.variables.0.wasSecret |        |
      | sets.1.variables.0.value     | value3 |
    Then the account must have these persisted variables
      | id | name | secret | value  | environment |
      | x  | var1 | 0      | value1 | prod1       |
      | x  | var2 | 1      | value2 | prod1       |
      | x  | var3 | 0      | value3 | prod2       |
    And no Kubernetes manifests must not be deleted

  Scenario: Update or delete account variables via the API as Admin
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | eee | var5 | 0      | value5 | prod1       |
      | fff | var6 | 1      | value6 | prod1       |
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod1       |
      | bbb | var2 | 1      | value2 | prod1       |
      | ccc | var3 | 0      | value3 | prod1       |
      | ddd | var4 | 0      | value4 | prod2       |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update variables of last account with as admin:
      | field                                         | value    |
      | account_vars.sets.prod1.envName               | prod1    |
      | account_vars.sets.prod1.variables.1.id        | bbb      |
      | account_vars.sets.prod1.variables.1.name      | var2     |
      | account_vars.sets.prod1.variables.1.secret    | 0        |
      | account_vars.sets.prod1.variables.1.wasSecret | 1        |
      | account_vars.sets.prod1.variables.1.value     |          |
      | account_vars.sets.prod1.variables.3.id        |          |
      | account_vars.sets.prod1.variables.3.name      | var5     |
      | account_vars.sets.prod1.variables.3.secret    | 0        |
      | account_vars.sets.prod1.variables.3.wasSecret | 0        |
      | account_vars.sets.prod1.variables.3.value     | value5   |
      | account_vars.sets.prod2.envName               | prod2    |
      | account_vars.sets.prod2.variables.0.name      | var3     |
      | account_vars.sets.prod2.variables.0.id        | ddd      |
      | account_vars.sets.prod2.variables.0.secret    | 0        |
      | account_vars.sets.prod2.variables.0.wasSecret |          |
      | account_vars.sets.prod2.variables.0.value     | value3.1 |
    Then the account must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 0      | value2   | prod1       |
      | ddd | var3 | 0      | value3.1 | prod2       |
      | x   | var5 | 0      | value5   | prod1       |
    And no Kubernetes manifests must not be deleted

  Scenario: Update or delete account variables via the API with a json body as Admin
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | eee | var5 | 0      | value5 | prod1       |
      | fff | var6 | 1      | value6 | prod1       |
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod1       |
      | bbb | var2 | 1      | value2 | prod1       |
      | ccc | var3 | 0      | value3 | prod1       |
      | ddd | var4 | 0      | value4 | prod2       |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update variables of last account with a json body as admin:
      | field                            | value    |
      | sets.prod1.envName               | prod1    |
      | sets.prod1.variables.1.id        | bbb      |
      | sets.prod1.variables.1.name      | var2     |
      | sets.prod1.variables.1.secret    | 0        |
      | sets.prod1.variables.1.wasSecret | 1        |
      | sets.prod1.variables.1.value     |          |
      | sets.prod1.variables.3.id        |          |
      | sets.prod1.variables.3.name      | var5     |
      | sets.prod1.variables.3.secret    | 0        |
      | sets.prod1.variables.3.wasSecret |          |
      | sets.prod1.variables.3.value     | value5   |
      | sets.prod2.envName               | prod2    |
      | sets.prod2.variables.0.name      | var3     |
      | sets.prod2.variables.0.id        | ddd      |
      | sets.prod2.variables.0.secret    | 0        |
      | sets.prod2.variables.0.wasSecret |          |
      | sets.prod2.variables.0.value     | value3.1 |
    Then the account must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 0      | value2   | prod1       |
      | ddd | var3 | 0      | value3.1 | prod2       |
      | x   | var5 | 0      | value5   | prod1       |
    And no Kubernetes manifests must not be deleted

  Scenario: Create new account variables via the API as Admin with secrets encryptions
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update variables of last account with as admin:
      | field                                     | value  |
      | account_vars.sets.0.envName               | prod1  |
      | account_vars.sets.0.variables.0.id        |        |
      | account_vars.sets.0.variables.0.name      | var1   |
      | account_vars.sets.0.variables.0.wasSecret |        |
      | account_vars.sets.0.variables.0.value     | value1 |
      | account_vars.sets.0.variables.1.id        |        |
      | account_vars.sets.0.variables.1.name      | var2   |
      | account_vars.sets.0.variables.1.secret    | 1      |
      | account_vars.sets.0.variables.1.wasSecret |        |
      | account_vars.sets.0.variables.1.value     | value2 |
      | account_vars.sets.1.envName               | prod2  |
      | account_vars.sets.1.variables.0.id        |        |
      | account_vars.sets.1.variables.0.name      | var3   |
      | account_vars.sets.1.variables.0.wasSecret |        |
      | account_vars.sets.1.variables.0.value     | value3 |
    Then the account must have these persisted variables
      | id | name | secret | value  | environment |
      | x  | var1 | 0      | value1 | prod1       |
      | x  | var2 | 1      | value2 | prod1       |
      | x  | var3 | 0      | value3 | prod2       |
    And no Kubernetes manifests must not be deleted

  Scenario: Create new account variables via the API with a json body as Admin with secrets encryptions
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update variables of last account with a json body as admin:
      | field                        | value  |
      | sets.0.envName               | prod1  |
      | sets.0.variables.0.id        |        |
      | sets.0.variables.0.name      | var1   |
      | sets.0.variables.0.wasSecret |        |
      | sets.0.variables.0.value     | value1 |
      | sets.0.variables.1.id        |        |
      | sets.0.variables.1.name      | var2   |
      | sets.0.variables.1.secret    | 1      |
      | sets.0.variables.1.wasSecret |        |
      | sets.0.variables.1.value     | value2 |
      | sets.1.envName               | prod2  |
      | sets.1.variables.0.id        |        |
      | sets.1.variables.0.name      | var3   |
      | sets.1.variables.0.wasSecret |        |
      | sets.1.variables.0.value     | value3 |
    Then the account must have these persisted variables
      | id | name | secret | value  | environment |
      | x  | var1 | 0      | value1 | prod1       |
      | x  | var2 | 1      | value2 | prod1       |
      | x  | var3 | 0      | value3 | prod2       |
    And no Kubernetes manifests must not be deleted

  Scenario: Update or delete account variables via the API as Admin with secrets encryptions
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | eee | var5 | 0      | value5 | prod1       |
      | fff | var6 | 1      | value6 | prod1       |
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod1       |
      | bbb | var2 | 1      | value2 | prod1       |
      | ccc | var3 | 0      | value3 | prod1       |
      | ddd | var4 | 0      | value4 | prod2       |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update variables of last account with as admin:
      | field                                         | value    |
      | account_vars.sets.prod1.envName               | prod1    |
      | account_vars.sets.prod1.variables.1.id        | bbb      |
      | account_vars.sets.prod1.variables.1.name      | var2     |
      | account_vars.sets.prod1.variables.1.secret    | 0        |
      | account_vars.sets.prod1.variables.1.wasSecret | 1        |
      | account_vars.sets.prod1.variables.1.value     |          |
      | account_vars.sets.prod1.variables.3.id        |          |
      | account_vars.sets.prod1.variables.3.name      | var5     |
      | account_vars.sets.prod1.variables.3.secret    | 0        |
      | account_vars.sets.prod1.variables.3.wasSecret | 0        |
      | account_vars.sets.prod1.variables.3.value     | value5   |
      | account_vars.sets.prod2.envName               | prod2    |
      | account_vars.sets.prod2.variables.0.name      | var3     |
      | account_vars.sets.prod2.variables.0.id        | ddd      |
      | account_vars.sets.prod2.variables.0.secret    | 0        |
      | account_vars.sets.prod2.variables.0.wasSecret |          |
      | account_vars.sets.prod2.variables.0.value     | value3.1 |
    Then the account must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 1      | value2   | prod1       |
      | ddd | var3 | 0      | value3.1 | prod2       |
      | x   | var5 | 0      | value5   | prod1       |
    And no Kubernetes manifests must not be deleted

  Scenario: Update or delete account variables via the API with a json body as Admin with secrets encryptions
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | eee | var5 | 0      | value5 | prod1       |
      | fff | var6 | 1      | value6 | prod1       |
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod1       |
      | bbb | var2 | 1      | value2 | prod1       |
      | ccc | var3 | 0      | value3 | prod1       |
      | ddd | var4 | 0      | value4 | prod2       |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update variables of last account with a json body as admin:
      | field                            | value    |
      | sets.prod1.envName               | prod1    |
      | sets.prod1.variables.1.id        | bbb      |
      | sets.prod1.variables.1.name      | var2     |
      | sets.prod1.variables.1.secret    | 1        |
      | sets.prod1.variables.1.wasSecret | 1        |
      | sets.prod1.variables.1.value     |          |
      | sets.prod1.variables.3.id        |          |
      | sets.prod1.variables.3.name      | var5     |
      | sets.prod1.variables.3.secret    | 0        |
      | sets.prod1.variables.3.wasSecret |          |
      | sets.prod1.variables.3.value     | value5   |
      | sets.prod2.envName               | prod2    |
      | sets.prod2.variables.0.name      | var3     |
      | sets.prod2.variables.0.id        | ddd      |
      | sets.prod2.variables.0.secret    | 0        |
      | sets.prod2.variables.0.wasSecret |          |
      | sets.prod2.variables.0.value     | value3.1 |
    Then the account must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 1      | value2   | prod1       |
      | ddd | var3 | 0      | value3.1 | prod2       |
      | x   | var5 | 0      | value5   | prod1       |
    And no Kubernetes manifests must not be deleted

  Scenario: Get account variables via the API as Admin
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | eee | var5 | 0      | value5 | prod1       |
      | fff | var6 | 1      | value6 | prod1       |
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod1       |
      | bbb | var2 | 1      | value2 | prod1       |
      | ccc | var3 | 0      | value3 | prod1       |
      | ddd | var4 | 0      | value4 | prod2       |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get variables of last account as admin
    Then get a JSON reponse
    And the serialized accounts variables with 4 variables
    And no Kubernetes manifests must not be deleted
