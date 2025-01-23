Feature: On a space instance, an API is available to manage accounts as admin and integrating it with any platform.
  An admin must has same rights of than the web access

  Scenario: Edit an account and environments from the API as Admin and create a new environment on a catalog cluster
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
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted on "Demo Kube Cluster"
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: Edit an account and environments from the API with a json body as Admin and create a new environment on a
    catalog cluster
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
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted on "Demo Kube Cluster"
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: Edit an account and edit read only environments from the API as Admin and get an error
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
    And no Kubernetes manifests must not be deleted

  Scenario: Edit an account and edit read only environments from the API with a json body as Admin and get an error
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
    And no Kubernetes manifests must not be deleted

  Scenario: Edit an account and exceed environments allowed from the API as Admin and get an error
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

  Scenario: Edit an account and exceed environments allowed from the API with a json body as Admin and get an error
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

  Scenario: Refresh account's quota on its environments from the API as Admin
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

  Scenario: Reinstall and account's environment from the API as Admin
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


  Scenario: Edit an account and environments from the API as Admin and create a new environment on a account cluster
    Given A Space app instance
    And A memory document database
    And a kubernetes client
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an account clusters "Cluster Company" and a slug "my-company-cluster"
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
      | admin_space_account.environmentResumes.2.clusterName          | Cluster Company   |
      | admin_space_account.environmentResumes.2.envName              | testing           |
    Then get a JSON reponse
    And the serialized account "Test Behat" for admin
    And a Kubernetes namespace for "my-company-testing" dedicated to "Cluster Company" is applied and populated
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted on "Demo Kube Cluster"
    And no Kubernetes manifests must not be created on "Demo Kube Cluster"
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: Edit an account and environments from the API with a json body as Admin and create a new environment on
  a account cluster
    Given A Space app instance
    And A memory document database
    And a kubernetes client
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an account clusters "Cluster Company" and a slug "my-company-cluster"
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
      | environmentResumes.2.clusterName          | Cluster Company   |
      | environmentResumes.2.envName              | Testing           |
    Then get a JSON reponse
    And the serialized account "Test Behat" for admin
    And a Kubernetes namespace for "my-company-testing" dedicated to "Cluster Company" is applied and populated
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted on "Demo Kube Cluster"
    And no Kubernetes manifests must not be created on "Demo Kube Cluster"
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: Edit an account and exceed environments allowed from the API with a json body as Admin and get an error
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an account clusters "Cluster Company" and a slug "my-company-cluster"
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
      | environmentResumes.2.clusterName          | Cluster Company   |
      | environmentResumes.2.envName              | testing           |
    Then get a JSON reponse
    And the user must have a 400 error
    And no Kubernetes manifests must not be created