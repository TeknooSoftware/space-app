Feature: On a space instance, users are grouped in shared accounts : there are at least one user per account, but
  account can have several users. The account and its projects is shared by all users of the account.
  An account owns also a specific Kubernetes namespace, legal informations and a name of the company or the service
  behind the account.

  Scenario: Update my account settings
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

  Scenario: Update my account's environment
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
      | field                                          | value             |
      | space_account.account.name                     | My Company        |
      | space_account.accountData.legalName            | My Company SAS    |
      | space_account.accountData.streetAddress        | 123 street        |
      | space_account.accountData.zipCode              | 14000             |
      | space_account.accountData.cityName             | Caen              |
      | space_account.accountData.countryName          | France            |
      | space_account.accountData.vatNumber            | FR0102030405      |
      | space_account.environmentResumes.0.clusterName | Demo Kube Cluster |
      | space_account.environmentResumes.0.envName     | dev               |
      | space_account.environmentResumes.1.clusterName | Demo Kube Cluster |
      | space_account.environmentResumes.1.envName     | prod              |
    When it submits the form:
      | field                                                   | value             |
      | space_account._token                                    | <auto>            |
      | space_account.account.name                              | My Company        |
      | space_account.accountData.legalName                     | Gr Company SAS    |
      | space_account.accountData.streetAddress                 | 123 street        |
      | space_account.accountData.zipCode                       | 14000             |
      | space_account.accountData.cityName                      | Caen              |
      | space_account.accountData.countryName                   | France            |
      | space_account.accountData.vatNumber                     | FR0102030405      |
      | space_account.environmentResumes.1.accountEnvironmentId | <auto>            |
      | space_account.environmentResumes.1.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.1.envName              | prod              |
      | space_account.environmentResumes.2.accountEnvironmentId |                   |
      | space_account.environmentResumes.2.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.2.envName              | testing           |
    Then the user obtains the form:
      | field                                          | value             |
      | space_account.account.name                     | My Company        |
      | space_account.accountData.legalName            | Gr Company SAS    |
      | space_account.accountData.streetAddress        | 123 street        |
      | space_account.accountData.zipCode              | 14000             |
      | space_account.accountData.cityName             | Caen              |
      | space_account.accountData.countryName          | France            |
      | space_account.accountData.vatNumber            | FR0102030405      |
      | space_account.environmentResumes.1.clusterName | Demo Kube Cluster |
      | space_account.environmentResumes.1.envName     | prod              |
      | space_account.environmentResumes.2.clusterName | Demo Kube Cluster |
      | space_account.environmentResumes.2.envName     | testing           |
    And a Kubernetes namespace for "my-company-testing" dedicated to "Demo Kube Cluster" is applied and populated
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: Update my account's read only environment
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
      | field                                          | value             |
      | space_account.account.name                     | My Company        |
      | space_account.accountData.legalName            | My Company SAS    |
      | space_account.accountData.streetAddress        | 123 street        |
      | space_account.accountData.zipCode              | 14000             |
      | space_account.accountData.cityName             | Caen              |
      | space_account.accountData.countryName          | France            |
      | space_account.accountData.vatNumber            | FR0102030405      |
      | space_account.environmentResumes.0.clusterName | Demo Kube Cluster |
      | space_account.environmentResumes.0.envName     | dev               |
      | space_account.environmentResumes.1.clusterName | Demo Kube Cluster |
      | space_account.environmentResumes.1.envName     | prod              |
    When it submits the form:
      | field                                                   | value             |
      | space_account._token                                    | <auto>            |
      | space_account.account.name                              | My Company        |
      | space_account.accountData.legalName                     | Gr Company SAS    |
      | space_account.accountData.streetAddress                 | 123 street        |
      | space_account.accountData.zipCode                       | 14000             |
      | space_account.accountData.cityName                      | Caen              |
      | space_account.accountData.countryName                   | France            |
      | space_account.accountData.vatNumber                     | FR0102030405      |
      | space_account.environmentResumes.0.accountEnvironmentId | <auto>            |
      | space_account.environmentResumes.0.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.0.envName              | prod              |
      | space_account.environmentResumes.1.accountEnvironmentId | <auto>            |
      | space_account.environmentResumes.1.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.1.envName              | testing           |
    Then the user obtains the form:
      | field                                          | value             |
      | space_account.account.name                     | My Company        |
      | space_account.accountData.legalName            | Gr Company SAS    |
      | space_account.accountData.streetAddress        | 123 street        |
      | space_account.accountData.zipCode              | 14000             |
      | space_account.accountData.cityName             | Caen              |
      | space_account.accountData.countryName          | France            |
      | space_account.accountData.vatNumber            | FR0102030405      |
      | space_account.environmentResumes.0.clusterName | Demo Kube Cluster |
      | space_account.environmentResumes.0.envName     | prod              |
      | space_account.environmentResumes.1.clusterName | Demo Kube Cluster |
      | space_account.environmentResumes.1.envName     | testing           |
    And the user obtains an error
    And no Kubernetes manifests must not be created
    And no Kubernetes manifests must not be deleted

  Scenario: Update my account and exceed environments allowed
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
      | field                                          | value             |
      | space_account.account.name                     | My Company        |
      | space_account.accountData.legalName            | My Company SAS    |
      | space_account.accountData.streetAddress        | 123 street        |
      | space_account.accountData.zipCode              | 14000             |
      | space_account.accountData.cityName             | Caen              |
      | space_account.accountData.countryName          | France            |
      | space_account.accountData.vatNumber            | FR0102030405      |
      | space_account.environmentResumes.0.clusterName | Demo Kube Cluster |
      | space_account.environmentResumes.0.envName     | dev               |
      | space_account.environmentResumes.1.clusterName | Demo Kube Cluster |
      | space_account.environmentResumes.1.envName     | prod              |
    When it submits the form:
      | field                                                   | value             |
      | space_account._token                                    | <auto>            |
      | space_account.account.name                              | My Company        |
      | space_account.accountData.legalName                     | Gr Company SAS    |
      | space_account.accountData.streetAddress                 | 123 street        |
      | space_account.accountData.zipCode                       | 14000             |
      | space_account.accountData.cityName                      | Caen              |
      | space_account.accountData.countryName                   | France            |
      | space_account.accountData.vatNumber                     | FR0102030405      |
      | space_account.environmentResumes.0.accountEnvironmentId | <auto>            |
      | space_account.environmentResumes.0.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.0.envName              | dev               |
      | space_account.environmentResumes.1.accountEnvironmentId | <auto>            |
      | space_account.environmentResumes.1.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.1.envName              | prod              |
      | space_account.environmentResumes.2.accountEnvironmentId |                   |
      | space_account.environmentResumes.2.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.2.envName              | testing           |
    Then the user obtains the form:
      | field                                          | value             |
      | space_account.account.name                     | My Company        |
      | space_account.accountData.legalName            | Gr Company SAS    |
      | space_account.accountData.streetAddress        | 123 street        |
      | space_account.accountData.zipCode              | 14000             |
      | space_account.accountData.cityName             | Caen              |
      | space_account.accountData.countryName          | France            |
      | space_account.accountData.vatNumber            | FR0102030405      |
      | space_account.environmentResumes.0.clusterName | Demo Kube Cluster |
      | space_account.environmentResumes.0.envName     | dev               |
      | space_account.environmentResumes.1.clusterName | Demo Kube Cluster |
      | space_account.environmentResumes.1.envName     | prod              |
      | space_account.environmentResumes.2.clusterName | Demo Kube Cluster |
      | space_account.environmentResumes.2.envName     | testing           |
    And the user obtains an error
    And no Kubernetes manifests must not be created
    And no Kubernetes manifests must not be deleted
