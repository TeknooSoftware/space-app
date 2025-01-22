Feature: On a space instance, an API is available to manage its account's environments

  Scenario: Update my account's environments via API
    Given A Space app instance
    And A memory document database
    And a kubernetes client
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's settings
    Then get a JSON reponse
    And the serialized account "My Company"
    When the API is called to update account's settings:
      | field                                                   | value             |
      | space_account.account.name                              | My Company        |
      | space_account.accountData.streetAddress                 | 123 street        |
      | space_account.accountData.zipCode                       | 14000             |
      | space_account.accountData.cityName                      | Caen              |
      | space_account.accountData.countryName                   | France            |
      | space_account.accountData.vatNumber                     | FR0102030405      |
      | space_account.environmentResumes.1.accountEnvironmentId | <auto:prod>       |
      | space_account.environmentResumes.1.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.1.envName              | prod              |
      | space_account.environmentResumes.2.accountEnvironmentId |                   |
      | space_account.environmentResumes.2.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.2.envName              | testing           |
    Then get a JSON reponse
    And the serialized account "My Company"
    And a Kubernetes namespace for "my-company-testing" dedicated to "Demo Kube Cluster" is applied and populated
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted on "Demo Kube Cluster"
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: Update my account's environments via API with a json body
    Given A Space app instance
    And A memory document database
    And a kubernetes client
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's settings
    Then get a JSON reponse
    And the serialized account "My Company"
    When the API is called to update account's settings with a json body:
      | field                                     | value             |
      | account.name                              | My Company        |
      | accountData.legalName                     | Gr Company SAS    |
      | accountData.streetAddress                 | 123 street        |
      | accountData.zipCode                       | 14000             |
      | accountData.cityName                      | Caen              |
      | accountData.countryName                   | France            |
      | accountData.vatNumber                     | FR0102030405      |
      | environmentResumes.1.accountEnvironmentId | <auto:prod>       |
      | environmentResumes.1.clusterName          | Demo Kube Cluster |
      | environmentResumes.1.envName              | prod              |
      | environmentResumes.2.accountEnvironmentId |                   |
      | environmentResumes.2.clusterName          | Demo Kube Cluster |
      | environmentResumes.2.envName              | testing           |
    Then get a JSON reponse
    And the serialized account "My Company"
    And a Kubernetes namespace for "my-company-testing" dedicated to "Demo Kube Cluster" is applied and populated
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted on "Demo Kube Cluster"
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: Update my account's read only environments via API
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
    When the API is called to get account's settings
    Then get a JSON reponse
    And the serialized account "My Company"
    When the API is called to update account's settings:
      | field                                                   | value             |
      | space_account.account.name                              | My Company        |
      | space_account.accountData.streetAddress                 | 123 street        |
      | space_account.accountData.zipCode                       | 14000             |
      | space_account.accountData.cityName                      | Caen              |
      | space_account.accountData.countryName                   | France            |
      | space_account.accountData.vatNumber                     | FR0102030405      |
      | space_account.environmentResumes.0.accountEnvironmentId | <auto:dev>        |
      | space_account.environmentResumes.0.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.0.envName              | dev               |
      | space_account.environmentResumes.1.accountEnvironmentId | <auto:prod>       |
      | space_account.environmentResumes.1.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.1.envName              | testing           |
    Then get a JSON reponse
    And the user must have a 400 error
    And no Kubernetes manifests must not be created
    And no Kubernetes manifests must not be deleted

  Scenario: Update my account's read only environments via API with a json body
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
    When the API is called to get account's settings
    Then get a JSON reponse
    And the serialized account "My Company"
    When the API is called to update account's settings with a json body:
      | field                                     | value             |
      | account.name                              | My Company        |
      | accountData.legalName                     | Gr Company SAS    |
      | accountData.streetAddress                 | 123 street        |
      | accountData.zipCode                       | 14000             |
      | accountData.cityName                      | Caen              |
      | accountData.countryName                   | France            |
      | accountData.vatNumber                     | FR0102030405      |
      | environmentResumes.0.accountEnvironmentId | <auto:dev>        |
      | environmentResumes.0.clusterName          | Demo Kube Cluster |
      | environmentResumes.0.envName              | dev               |
      | environmentResumes.1.accountEnvironmentId | <auto:prod>       |
      | environmentResumes.1.clusterName          | Demo Kube Cluster |
      | environmentResumes.1.envName              | testing           |
    Then get a JSON reponse
    And the user must have a 400 error
    And no Kubernetes manifests must not be created

  Scenario: Update my account and exceed environments allowed via API
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
    When the API is called to get account's settings
    Then get a JSON reponse
    And the serialized account "My Company"
    When the API is called to update account's settings:
      | field                                                   | value             |
      | space_account.account.name                              | My Company        |
      | space_account.accountData.streetAddress                 | 123 street        |
      | space_account.accountData.zipCode                       | 14000             |
      | space_account.accountData.cityName                      | Caen              |
      | space_account.accountData.countryName                   | France            |
      | space_account.accountData.vatNumber                     | FR0102030405      |
      | space_account.environmentResumes.0.accountEnvironmentId | <auto:dev>        |
      | space_account.environmentResumes.0.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.0.envName              | dev               |
      | space_account.environmentResumes.1.accountEnvironmentId | <auto:prod>       |
      | space_account.environmentResumes.1.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.1.envName              | prod              |
      | space_account.environmentResumes.2.accountEnvironmentId |                   |
      | space_account.environmentResumes.2.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.2.envName              | testing           |
    Then get a JSON reponse
    And the user must have a 400 error
    And no Kubernetes manifests must not be created

  Scenario: Update my account and exceed environments allowed via API with a json body
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
    When the API is called to get account's settings
    Then get a JSON reponse
    And the serialized account "My Company"
    When the API is called to update account's settings with a json body:
      | field                                     | value             |
      | account.name                              | My Company        |
      | accountData.legalName                     | Gr Company SAS    |
      | accountData.streetAddress                 | 123 street        |
      | accountData.zipCode                       | 14000             |
      | accountData.cityName                      | Caen              |
      | accountData.countryName                   | France            |
      | accountData.vatNumber                     | FR0102030405      |
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

  Scenario: #todo create from account cluster