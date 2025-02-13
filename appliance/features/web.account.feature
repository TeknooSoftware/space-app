Feature: Web interface to manage account's settings
  In order to manage account's setting
  As an user of an account
  I want to manage my account's name and account's legal informations

  On space, Non admin users are mandatory attached to an account. The account is central, projects, users, environments
  and clusters are attached to account. An account can represent a company, a company's unit, a project teams,
  any thing.

  Scenario: From the UI, update my account settings
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to account settings
    Then the user obtains the form:
      | field                                   | value          |
      | space_account.account.name              | My Company     |
      | space_account.accountData.legalName     | My Company SAS |
      | space_account.accountData.streetAddress | 123 street     |
      | space_account.accountData.zipCode       | 14000          |
      | space_account.accountData.cityName      | Caen           |
      | space_account.accountData.countryName   | France         |
      | space_account.accountData.vatNumber     | FR0102030405   |
    When it submits the form:
      | field                                   | value          |
      | space_account._token                    | <auto>         |
      | space_account.account.name              | Great Company  |
      | space_account.accountData.legalName     | Gr Company SAS |
      | space_account.accountData.streetAddress | 123 street     |
      | space_account.accountData.zipCode       | 14000          |
      | space_account.accountData.cityName      | Caen           |
      | space_account.accountData.countryName   | France         |
      | space_account.accountData.vatNumber     | FR0102030405   |
    Then the user obtains the form:
      | field                                   | value          |
      | space_account.account.name              | Great Company  |
      | space_account.accountData.legalName     | Gr Company SAS |
      | space_account.accountData.streetAddress | 123 street     |
      | space_account.accountData.zipCode       | 14000          |
      | space_account.accountData.cityName      | Caen           |
      | space_account.accountData.countryName   | France         |
      | space_account.accountData.vatNumber     | FR0102030405   |
    And the account name is now "Great Company"
    And no Kubernetes manifests must not be deleted

  Scenario: From the UI, update my account's environment
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
    And It goes to account settings
    Then the user obtains the form:
      | field                                    | value             |
      | space_account.account.name               | My Company        |
      | space_account.accountData.legalName      | My Company SAS    |
      | space_account.accountData.streetAddress  | 123 street        |
      | space_account.accountData.zipCode        | 14000             |
      | space_account.accountData.cityName       | Caen              |
      | space_account.accountData.countryName    | France            |
      | space_account.accountData.vatNumber      | FR0102030405      |
      | space_account.environments.0.clusterName | Demo Kube Cluster |
      | space_account.environments.0.envName     | dev               |
      | space_account.environments.1.clusterName | Demo Kube Cluster |
      | space_account.environments.1.envName     | prod              |
    When it submits the form:
      | field                                             | value             |
      | space_account._token                              | <auto>            |
      | space_account.account.name                        | My Company        |
      | space_account.accountData.legalName               | Gr Company SAS    |
      | space_account.accountData.streetAddress           | 123 street        |
      | space_account.accountData.zipCode                 | 14000             |
      | space_account.accountData.cityName                | Caen              |
      | space_account.accountData.countryName             | France            |
      | space_account.accountData.vatNumber               | FR0102030405      |
      | space_account.environments.1.accountEnvironmentId | <auto>            |
      | space_account.environments.1.clusterName          | Demo Kube Cluster |
      | space_account.environments.1.envName              | prod              |
      | space_account.environments.2.accountEnvironmentId |                   |
      | space_account.environments.2.clusterName          | Demo Kube Cluster |
      | space_account.environments.2.envName              | testing           |
    Then the user obtains the form:
      | field                                    | value             |
      | space_account.account.name               | My Company        |
      | space_account.accountData.legalName      | Gr Company SAS    |
      | space_account.accountData.streetAddress  | 123 street        |
      | space_account.accountData.zipCode        | 14000             |
      | space_account.accountData.cityName       | Caen              |
      | space_account.accountData.countryName    | France            |
      | space_account.accountData.vatNumber      | FR0102030405      |
      | space_account.environments.1.clusterName | Demo Kube Cluster |
      | space_account.environments.1.envName     | prod              |
      | space_account.environments.2.clusterName | Demo Kube Cluster |
      | space_account.environments.2.envName     | testing           |
    And a Kubernetes namespace for "my-company-testing" dedicated to "Demo Kube Cluster" is applied and populated
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted on "Demo Kube Cluster"
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: From the UI, update an read only environment and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to account settings
    Then the user obtains the form:
      | field                                    | value             |
      | space_account.account.name               | My Company        |
      | space_account.accountData.legalName      | My Company SAS    |
      | space_account.accountData.streetAddress  | 123 street        |
      | space_account.accountData.zipCode        | 14000             |
      | space_account.accountData.cityName       | Caen              |
      | space_account.accountData.countryName    | France            |
      | space_account.accountData.vatNumber      | FR0102030405      |
      | space_account.environments.0.clusterName | Demo Kube Cluster |
      | space_account.environments.0.envName     | dev               |
      | space_account.environments.1.clusterName | Demo Kube Cluster |
      | space_account.environments.1.envName     | prod              |
    When it submits the form:
      | field                                             | value             |
      | space_account._token                              | <auto>            |
      | space_account.account.name                        | My Company        |
      | space_account.accountData.legalName               | Gr Company SAS    |
      | space_account.accountData.streetAddress           | 123 street        |
      | space_account.accountData.zipCode                 | 14000             |
      | space_account.accountData.cityName                | Caen              |
      | space_account.accountData.countryName             | France            |
      | space_account.accountData.vatNumber               | FR0102030405      |
      | space_account.environments.0.accountEnvironmentId | <auto>            |
      | space_account.environments.0.clusterName          | Demo Kube Cluster |
      | space_account.environments.0.envName              | prod              |
      | space_account.environments.1.accountEnvironmentId | <auto>            |
      | space_account.environments.1.clusterName          | Demo Kube Cluster |
      | space_account.environments.1.envName              | testing           |
    Then the user obtains the form:
      | field                                    | value             |
      | space_account.account.name               | My Company        |
      | space_account.accountData.legalName      | Gr Company SAS    |
      | space_account.accountData.streetAddress  | 123 street        |
      | space_account.accountData.zipCode        | 14000             |
      | space_account.accountData.cityName       | Caen              |
      | space_account.accountData.countryName    | France            |
      | space_account.accountData.vatNumber      | FR0102030405      |
      | space_account.environments.0.clusterName | Demo Kube Cluster |
      | space_account.environments.0.envName     | prod              |
      | space_account.environments.1.clusterName | Demo Kube Cluster |
      | space_account.environments.1.envName     | testing           |
    And the user obtains an error
    And no Kubernetes manifests must not be deleted

  Scenario: From the UI, create an environment on a managed cluster and exceeding quota
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to account settings
    Then the user obtains the form:
      | field                                    | value             |
      | space_account.account.name               | My Company        |
      | space_account.accountData.legalName      | My Company SAS    |
      | space_account.accountData.streetAddress  | 123 street        |
      | space_account.accountData.zipCode        | 14000             |
      | space_account.accountData.cityName       | Caen              |
      | space_account.accountData.countryName    | France            |
      | space_account.accountData.vatNumber      | FR0102030405      |
      | space_account.environments.0.clusterName | Demo Kube Cluster |
      | space_account.environments.0.envName     | dev               |
      | space_account.environments.1.clusterName | Demo Kube Cluster |
      | space_account.environments.1.envName     | prod              |
    When it submits the form:
      | field                                             | value             |
      | space_account._token                              | <auto>            |
      | space_account.account.name                        | My Company        |
      | space_account.accountData.legalName               | Gr Company SAS    |
      | space_account.accountData.streetAddress           | 123 street        |
      | space_account.accountData.zipCode                 | 14000             |
      | space_account.accountData.cityName                | Caen              |
      | space_account.accountData.countryName             | France            |
      | space_account.accountData.vatNumber               | FR0102030405      |
      | space_account.environments.0.accountEnvironmentId | <auto>            |
      | space_account.environments.0.clusterName          | Demo Kube Cluster |
      | space_account.environments.0.envName              | dev               |
      | space_account.environments.1.accountEnvironmentId | <auto>            |
      | space_account.environments.1.clusterName          | Demo Kube Cluster |
      | space_account.environments.1.envName              | prod              |
      | space_account.environments.2.accountEnvironmentId |                   |
      | space_account.environments.2.clusterName          | Demo Kube Cluster |
      | space_account.environments.2.envName              | testing           |
    Then the user obtains the form:
      | field                                    | value             |
      | space_account.account.name               | My Company        |
      | space_account.accountData.legalName      | Gr Company SAS    |
      | space_account.accountData.streetAddress  | 123 street        |
      | space_account.accountData.zipCode        | 14000             |
      | space_account.accountData.cityName       | Caen              |
      | space_account.accountData.countryName    | France            |
      | space_account.accountData.vatNumber      | FR0102030405      |
      | space_account.environments.0.clusterName | Demo Kube Cluster |
      | space_account.environments.0.envName     | dev               |
      | space_account.environments.1.clusterName | Demo Kube Cluster |
      | space_account.environments.1.envName     | prod              |
      | space_account.environments.2.clusterName | Demo Kube Cluster |
      | space_account.environments.2.envName     | testing           |
    And the user obtains an error
    And no Kubernetes manifests must not be deleted
